<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 *
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license   GNU GPL version 3 or, at your option, any later version
 * @package   akeebaengine
 *
 */

namespace Akeeba\Engine\Postproc;

// Protection against direct access
defined('AKEEBAENGINE') or die();

use Psr\Log\LogLevel;
use Akeeba\Engine\Factory;
use \Akeeba\Engine\Postproc\Connector\Amazon\Aws\S3\S3Client;
use \Akeeba\Engine\Postproc\Connector\Amazon\Aws\Common\Credentials\Credentials;
use \Akeeba\Engine\Postproc\Connector\Amazon\Aws\S3\Model\MultipartUpload\UploadBuilder;
use \Akeeba\Engine\Postproc\Connector\Amazon\Symfony\Component\EventDispatcher\EventSubscriberInterface;
use \Akeeba\Engine\Postproc\Connector\Amazon\Aws\Common\Exception\MultipartUploadException;

/**
 * Upload to Amazon S3 (new version) post-processing engine for Akeeba Engine
 *
 * @package Akeeba\Engine\Postproc
 */
class Amazons3 extends Base implements EventSubscriberInterface
{
	/** @var null|string The upload ID of the multipart upload in progress */
	protected $uploadId = null;

	/** @var null|int The position in the source file for the multipart upload in progress */
	protected $sourceSeekPosition = null;

	/**
	 * Initialise the class, setting its capabilities
	 */
	public function __construct()
	{
		$this->can_delete              = true;
		$this->can_download_to_browser = true;
		$this->can_download_to_file    = true;
	}

	/**
	 * This function takes care of post-processing a backup archive's part, or the
	 * whole backup archive if it's not a split archive type. If the process fails
	 * it should return false. If it succeeds and the entirety of the file has been
	 * processed, it should return true. If only a part of the file has been uploaded,
	 * it must return 1.
	 *
	 * @param   string $absolute_filename Absolute path to the part we'll have to process
	 * @param   string $upload_as         Base name of the uploaded file, skip to use $absolute_filename's
	 *
	 * @return  boolean|integer  False on failure, true on success, 1 if more work is required
	 */
	public function processPart($absolute_filename, $upload_as = null)
	{
		// Retrieve engine configuration data
		$akeebaConfig = Factory::getConfiguration();

		// Load multipart information from temporary storage
		$this->uploadId           = $akeebaConfig->get('volatile.postproc.amazons3.uploadId', null);
		$this->sourceSeekPosition = $akeebaConfig->get('volatile.postproc.amazons3.sourceSeekPosition', null);

		// Get the configuration parameters
		$bucket           = $akeebaConfig->get('engine.postproc.amazons3.bucket', null);
		$disableMultipart = $akeebaConfig->get('engine.postproc.amazons3.legacy', 0);
		$useRRS           = $akeebaConfig->get('engine.postproc.amazons3.rrs', 0);

		// The directory is a special case. First try getting a cached directory
		$directory        = $akeebaConfig->get('volatile.postproc.directory', null);
		$processDirectory = false;

		// If there is no cached directory, fetch it from the engine configuration
		if (is_null($directory))
		{
			$directory        = $akeebaConfig->get('engine.postproc.amazons3.directory', '');
			$processDirectory = true;
		}

		// The very first time we deal with the directory we need to process it.
		if ($processDirectory)
		{
			if ( !empty($directory))
			{
				$directory = str_replace('\\', '/', $directory);
				$directory = rtrim($directory, '/');
				$directory = trim($directory);
				$directory = ltrim(Factory::getFilesystemTools()->TranslateWinPath($directory), '/');
				$directory = Factory::getFilesystemTools()->replace_archive_name_variables($directory);
			}
			else
			{
				$directory = '';
			}

			// Store the parsed directory in temporary storage
			$akeebaConfig->set('volatile.postproc.directory', $directory);
		}

		// Remove any slashes from the bucket
		$bucket = str_replace('/', '', $bucket);

		// Get the file size and disable multipart uploads for files shorter than 5Mb
		$fileSize = @filesize($absolute_filename);

		if ($fileSize <= 5242880)
		{
			$disableMultipart = true;
		}

		// Calculate relative remote filename
		$remoteKey = empty($upload_as) ? basename($absolute_filename) : $upload_as;

		if ( !empty($directory) && ($directory != '/'))
		{
			$remoteKey = $directory . '/' . $remoteKey;
		}

		// Store the absolute remote path in the class property
		$this->remote_path = $remoteKey;

		// Create the S3 client instance
		$s3Client = $this->getS3Client();

		if ( !is_object($s3Client))
		{
			return false;
		}

		// Are we already processing a multipart upload or asked to perform a multipart upload?
		if ( !empty($this->uploadId) || !$disableMultipart)
		{
			return $this->multipartUpload($bucket, $remoteKey, $absolute_filename, $s3Client, 'bucket-owner-full-control', $useRRS);
		}

		return $this->simpleUpload($bucket, $remoteKey, $absolute_filename, $s3Client, 'bucket-owner-full-control', $useRRS);
	}

	/**
	 * Deletes a remote file
	 *
	 * @param $path string Absolute path to the file we're deleting
	 *
	 * @return bool|int False on failure, true on success, 1 if more work is required
	 */
	public function delete($path)
	{
		// Retrieve engine configuration data
		$akeebaConfig = Factory::getConfiguration();

		// Get the configuration parameters
		$bucket = $akeebaConfig->get('engine.postproc.amazons3.bucket', null);
		$bucket = str_replace('/', '', $bucket);

		// Create the S3 client instance
		$s3Client = $this->getS3Client();

		if ( !is_object($s3Client))
		{
			return false;
		}

		try
		{
			$s3Client->deleteObject(array(
				'Bucket' => $bucket,
				'Key'    => $path
			));
		}
		catch (\Exception $e)
		{
			$this->setError($e->getCode() . ' :: ' . $e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Downloads a remote file to a local file, optionally doing a range download. If the
	 * download fails we return false. If the download succeeds we return true. If range
	 * downloads are not supported, -1 is returned and nothing is written to disk.
	 *
	 * @param $remotePath string The path to the remote file
	 * @param $localFile  string The absolute path to the local file we're writing to
	 * @param $fromOffset int|null The offset (in bytes) to start downloading from
	 * @param $length     int|null The amount of data (in bytes) to download
	 *
	 * @return bool|int True on success, false on failure, -1 if ranges are not supported
	 */
	public function downloadToFile($remotePath, $localFile, $fromOffset = null, $length = null)
	{
		// Retrieve engine configuration data
		$akeebaConfig = Factory::getConfiguration();

		// Get the configuration parameters
		$bucket = $akeebaConfig->get('engine.postproc.amazons3.bucket', null);
		$bucket = str_replace('/', '', $bucket);

		// Create the S3 client instance
		$s3Client = $this->getS3Client();

		if ( !is_object($s3Client))
		{
			return false;
		}

		$serviceArguments = array(
			'Bucket' => $bucket,
			'Key'    => $remotePath,
			'SaveAs' => $localFile,
		);

		if ($fromOffset && $length)
		{
			$toOffset                  = $fromOffset + $length - 1;
			$serviceArguments['Range'] = $fromOffset . '-' . $toOffset;
		}

		try
		{
			$s3Client->getObject($serviceArguments);
		}
		catch (\Exception $e)
		{
			$this->setError($e->getCode() . ' :: ' . $e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Returns a public download URL or starts a browser-side download of a remote file.
	 * In the case of a public download URL, a string is returned. If a browser-side
	 * download is initiated, it returns true. In any other case (e.g. unsupported, not
	 * found, etc) it returns false.
	 *
	 * @param $remotePath string The file to download
	 *
	 * @return string|bool
	 */
	public function downloadToBrowser($remotePath)
	{
		// Retrieve engine configuration data
		$akeebaConfig = Factory::getConfiguration();

		// Get the configuration parameters
		$bucket = $akeebaConfig->get('engine.postproc.amazons3.bucket', null);
		$bucket = str_replace('/', '', $bucket);

		// Create the S3 client instance
		$signatureMethod = $akeebaConfig->get('engine.postproc.amazons3.signature', 's3');
		$akeebaConfig->set('engine.postproc.amazons3.signature', 's3');
		$s3Client = $this->getS3Client();
		$akeebaConfig->set('engine.postproc.amazons3.signature', $signatureMethod);

		if ( !is_object($s3Client))
		{
			return false;
		}

		return $s3Client->getObjectUrl($bucket, $remotePath, '+10 seconds');
	}

	/**
	 * Start a multipart upload
	 *
	 * @param string   $bucket     The bucket to upload to
	 * @param string   $remoteKey  The remote filename
	 * @param string   $sourceFile The full path to the local source file
	 * @param S3Client $s3Client   The S3 client object instance
	 * @param string   $acl        Canned ACL privileges to use
	 * @param bool     $useRRS     Should I use Reduced Redundancy Storage?
	 *
	 * @return  bool|int  True when we're done uploading, false if an error occurs, 1 if we have more parts
	 */
	protected function multipartUpload($bucket, $remoteKey, $sourceFile, $s3Client, $acl = 'bucket-owner-full-control', $useRRS = false)
	{
		/** @var UploadBuilder $uploadBuilder */
		$uploadBuilder = UploadBuilder::newInstance()
			->setClient($s3Client)
			->setSource($sourceFile)
			->setBucket($bucket)
			->setKey($remoteKey)
			->setOption('ACL', $acl)
			->setOption('StorageClass', $useRRS ? 'REDUCED_REDUNDANCY' : 'STANDARD');

		if (is_null($this->uploadId))
		{
			Factory::getLog()->log(LogLevel::DEBUG, "AmazonS3 -- Beginning multipart upload of $sourceFile");
		}
		else
		{
			Factory::getLog()
				->log(LogLevel::DEBUG, "AmazonS3 -- Continuing multipart upload of $sourceFile (UploadId: {$this->uploadId})");

			$uploadBuilder->resumeFrom($this->uploadId);
		}

		/** @var \Akeeba\Engine\Postproc\Connector\Amazon\Aws\S3\Model\MultipartUpload\SerialTransfer $uploader */
		try
		{
			$uploader = $uploadBuilder->build();
			$uploader->addSubscriber($this);
		}
		catch (\Exception $e)
		{
			Factory::getLog()->log(LogLevel::DEBUG, "AmazonS3 -- Could not prepare the multipart upload. Please check your S3 configuration (access and secret key, bucket name, S3 region and whether the user for this access and secret key has full privileges on this bucket in Amazon IAM).");
			$this->setWarning('Amazon S3 returned an error message: ' . $e->getCode() . ' :: ' . $e->getMessage());

			// Reset the multipart markers in temporary storage
			$akeebaConfig = Factory::getConfiguration();
			$akeebaConfig->set('volatile.postproc.amazons3.uploadId', null);
			$akeebaConfig->set('volatile.postproc.amazons3.sourceSeekPosition', null);

			return false;
		}

		// When resuming the upload, we need to seek the source to the place where it should resume uploading
		if (!is_null($this->uploadId))
		{
			$resource = $uploader->getSource()->getStream();
			fseek($resource, $this->sourceSeekPosition, SEEK_SET);
		}

		// Perform the upload. Abort the upload if something goes wrong
		try
		{
			$uploader->upload();
			$akeebaConfig = Factory::getConfiguration();

			// Are we done?
			if (is_null($this->uploadId))
			{
				Factory::getLog()->log(LogLevel::DEBUG, "AmazonS3 -- Multipart upload has finished successfully");

				$akeebaConfig->set('volatile.postproc.amazons3.uploadId', null);
				$akeebaConfig->set('volatile.postproc.amazons3.sourceSeekPosition', null);

				return true;
			}

			// Not done, mark ourselves as having more work to do
			$akeebaConfig->set('volatile.postproc.amazons3.uploadId', $this->uploadId);
			$akeebaConfig->set('volatile.postproc.amazons3.sourceSeekPosition', $this->sourceSeekPosition);

			return 1;
		}
		// There was a catchable upload exception
		catch (MultipartUploadException $e)
		{
			Factory::getLog()
				   ->log(LogLevel::DEBUG, "AmazonS3 -- Multipart upload has failed with a catchable error. Aborting upload.");
			$this->setWarning('Amazon S3 returned an error message: ' . $e->getCode() . ' :: ' . $e->getMessage());

			// Reset the multipart markers in temporary storage
			$akeebaConfig = Factory::getConfiguration();
			$akeebaConfig->set('volatile.postproc.amazons3.uploadId', null);
			$akeebaConfig->set('volatile.postproc.amazons3.sourceSeekPosition', null);

			// Abort the upload
			$uploader->abort();

			return false;
		}
			// There was an error we don't know how to recover from
		catch (\Exception $e)
		{
			Factory::getLog()->log(LogLevel::DEBUG, "AmazonS3 -- Multipart upload has failed with an unexpected error.");
			$this->setWarning('Amazon S3 returned an error message: ' . $e->getCode() . ' :: ' . $e->getMessage());

			// Reset the multipart markers in temporary storage
			$akeebaConfig = Factory::getConfiguration();
			$akeebaConfig->set('volatile.postproc.amazons3.uploadId', null);
			$akeebaConfig->set('volatile.postproc.amazons3.sourceSeekPosition', null);

			return false;
		}
	}

	/**
	 * Perform a single-step upload of a file
	 *
	 * @param string   $bucket     The bucket to upload to
	 * @param string   $remoteKey  The remote filename
	 * @param string   $sourceFile The full path to the local source file
	 * @param S3Client $s3Client   The S3 client object instance
	 * @param string   $acl        Canned ACL privileges to use
	 * @param bool     $useRRS     Should I use Reduced Redundancy Storage?
	 *
	 * @return  bool|int  True when we're done uploading, false if an error occurs, 1 if we have more parts
	 */
	protected function simpleUpload($bucket, $remoteKey, $sourceFile, $s3Client, $acl = 'bucket-owner-full-control', $useRRS = false)
	{
		Factory::getLog()
			   ->log(LogLevel::DEBUG, "AmazonS3 -- Legacy (single part) upload of " . basename($sourceFile));

		$uploadOperation = array(
			'Bucket'       => $bucket,
			'Key'          => $remoteKey,
			'SourceFile'   => $sourceFile,
			'ACL'          => $acl,
			'StorageClass' => $useRRS ? 'REDUCED_REDUNDANCY' : 'STANDARD'
		);

		try
		{
			$s3Client->putObject($uploadOperation);
		}
		catch (\Exception $e)
		{
			$this->setWarning($e->getCode() . ' :: ' . $e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Returns an array of event names this subscriber wants to listen to. This implements the EventSubscriberInterface
	 * interface which allows us to register an object of this class as an event listener to the S3 client object. This
	 * is required for multipart uploads to take place on different page loads.
	 *
	 * The array keys are event names and the value can be:
	 *
	 *  * The method name to call (priority defaults to 0)
	 *  * An array composed of the method name to call and the priority
	 *  * An array of arrays composed of the method names to call and respective
	 *    priorities, or 0 if unset
	 *
	 * For instance:
	 *
	 *  * array('eventName' => 'methodName')
	 *  * array('eventName' => array('methodName', $priority))
	 *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
	 *
	 * @return array The event names to listen to
	 */
	public static function getSubscribedEvents()
	{
		return array(
			'multipart_upload.after_part_upload' => 'onAfterPartUpload',
			'multipart_upload.after_complete'    => 'onAfterCompleteUpload',
		);
	}

	/**
	 * Event handler called after uploading a single part of the backup archive
	 *
	 * @param   array $message The message sent by AbstractTransfer
	 */
	public function onAfterPartUpload($message)
	{
		/** @var \Akeeba\Engine\Postproc\Connector\Amazon\Aws\S3\Model\MultipartUpload\SerialTransfer $transferEngine */
		$transferEngine = $message['transfer'];

		/** @var \Akeeba\Engine\Postproc\Connector\Amazon\Aws\S3\Model\MultipartUpload\TransferState $stateObject */
		$stateObject    = $message['state'];
		$params         = $stateObject->getUploadId()->toParams();
		$uploadId       = $params['UploadId'];
		$this->uploadId = $uploadId;

		$this->sourceSeekPosition = $transferEngine->getSource()->ftell();

		$transferEngine->stop();
	}

	/**
	 * Event handler called after the multipart upload is complete. This is called after AbstractTransfer calls the
	 * complete() method which finalises the transferred file.
	 *
	 * @param   array $message The message sent by AbstractTransfer
	 */
	public function onAfterCompleteUpload($message)
	{
		$this->uploadId           = null;
		$this->sourceSeekPosition = null;
	}

	/**
	 * Get a configured S3 client object.
	 *
	 * @return  S3Client
	 */
	protected function &getS3Client()
	{
		// Retrieve engine configuration data
		$akeebaConfig = Factory::getConfiguration();

		// Get the configuration parameters
		$accessKey        = $akeebaConfig->get('engine.postproc.amazons3.accesskey', '');
		$secretKey        = $akeebaConfig->get('engine.postproc.amazons3.secretkey', '');
		$useSSL           = $akeebaConfig->get('engine.postproc.amazons3.usessl', 0);
		$customEndpoint   = $akeebaConfig->get('engine.postproc.amazons3.customendpoint', '');
		$signatureMethod  = $akeebaConfig->get('engine.postproc.amazons3.signature', 's3');
		$region           = $akeebaConfig->get('engine.postproc.amazons3.region', '');
		$disableMultipart = $akeebaConfig->get('engine.postproc.amazons3.legacy', 0);
		$bucket           = $akeebaConfig->get('engine.postproc.amazons3.bucket', null);

		Factory::getLog()
			   ->log(LogLevel::DEBUG, "AmazonS3 -- Using signature method $signatureMethod, " . ($disableMultipart ? 'single-part' : 'multipart') . ' uploads');

		// Makes sure the custom endpoint has a protocol and no trailing slash
		$customEndpoint = trim($customEndpoint);

		if ( !empty($customEndpoint))
		{
			$protoPos = strpos($customEndpoint, ':\\');

			if ($protoPos === false)
			{
				$customEndpoint = 'http://' . $customEndpoint;
			}

			$customEndpoint = rtrim($customEndpoint, '/');

			Factory::getLog()
				   ->log(LogLevel::DEBUG, "AmazonS3 -- Using custom endpoint $customEndpoint");
		}

		// Remove any slashes from the bucket
		$bucket = str_replace('/', '', $bucket);

		// Sanity checks
		if (empty($accessKey))
		{
			$this->setError('You have not set up your Amazon S3 Access Key');

			return null;
		}

		if (empty($secretKey))
		{
			$this->setError('You have not set up your Amazon S3 Secret Key');

			return null;
		}

		if ( !function_exists('curl_init'))
		{
			$this->setWarning('cURL is not enabled, please enable it in order to post-process your archives');

			return null;
		}

		if (empty($bucket))
		{
			$this->setError('You have not set up your Amazon S3 Bucket');

			return null;
		}

		// Prepare the credentials object
		$amazonCredentials = new Credentials(
			$accessKey,
			$secretKey
		);

		// Prepare the client options array. See http://docs.aws.amazon.com/aws-sdk-php/guide/latest/configuration.html#client-configuration-options
		$clientOptions = array(
			'credentials' => $amazonCredentials,
			'scheme'      => $useSSL ? 'https' : 'http',
			'signature'   => $signatureMethod,
			'region'      => $region
		);

		if ($customEndpoint)
		{
			$clientOptions['base_url'] = $customEndpoint;
		}

		// If SSL is not enabled you must not provide the CA root file.
		if (defined('AKEEBA_CACERT_PEM') && $useSSL)
		{
			$clientOptions['ssl.certificate_authority'] = AKEEBA_CACERT_PEM;
		}
		else
		{
			$clientOptions['ssl.certificate_authority'] = false;
		}

		// Create the S3 client instance
		$s3Client = S3Client::factory($clientOptions);

		return $s3Client;
	}
}