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
use Akeeba\Engine\Postproc\Connector\Amazons3 as ConnectorAmazons3;

class S3 extends Base
{
	public $cache = null;

	public function __construct()
	{
		$this->can_delete = true;
		$this->can_download_to_browser = true;
		$this->can_download_to_file = true;
	}

	public function processPart($absolute_filename, $upload_as = null)
	{
		// Retrieve engine configuration data
		$config = Factory::getConfiguration();

		$accesskey = trim($config->get('engine.postproc.s3.accesskey', ''));
		$secret = trim($config->get('engine.postproc.s3.secretkey', ''));
		$usessl = $config->get('engine.postproc.s3.usessl', 0) == 0 ? false : true;
		$bucket = $config->get('engine.postproc.s3.bucket', '');
		$directory = $config->get('volatile.postproc.directory', null);
		$lowercase = $config->get('engine.postproc.s3.lowercase', 1);
		$rrs = $config->get('engine.postproc.s3.rrs', 1);
		$endpoint = $config->get('engine.postproc.s3.customendpoint', '');

		if (empty($directory))
		{
			$directory = $config->get('engine.postproc.s3.directory', 0);
		}

		if (!empty($directory))
		{
			$directory = str_replace('\\', '/', $directory);
			$directory = rtrim($directory, '/');
		}

		$endpoint = trim($endpoint);

		if (!empty($endpoint))
		{
			$protoPos = strpos($endpoint, ':\\');

			if ($protoPos !== false)
			{
				$endpoint = substr($endpoint, $protoPos + 3);
			}

			$slashPos = strpos($endpoint, '/');

			if ($slashPos !== false)
			{
				$endpoint = substr($endpoint, $slashPos + 1);
			}
		}

		// Sanity checks
		if (empty($accesskey))
		{
			$this->setError('You have not set up your Amazon S3 Access Key');

			return false;
		}

		if (empty($secret))
		{
			$this->setError('You have not set up your Amazon S3 Secret Key');

			return false;
		}

        if(!function_exists('curl_init'))
        {
            $this->setWarning('cURL is not enabled, please enable it in order to post-process your archives');

            return false;
        }

		if (empty($bucket))
		{
			$this->setError('You have not set up your Amazon S3 Bucket');

			return false;
		}
		else
		{
			// Remove any slashes from the bucket
			$bucket = str_replace('/', '', $bucket);
			if ($lowercase)
			{
				$bucket = strtolower($bucket);
			}
		}

		// Create an S3 instance with the required credentials
		$s3 = ConnectorAmazons3::getInstance($accesskey, $secret, $usessl);

		if (!empty($endpoint))
		{
			$s3->defaultHost = $endpoint;
		}

		// Fix the directory name, if required
		if (!empty($directory))
		{
			$directory = trim($directory);
			$directory = ltrim(Factory::getFilesystemTools()->TranslateWinPath($directory), '/');
		}
		else
		{
			$directory = '';
		}

		// Parse tags
		$directory = Factory::getFilesystemTools()->replace_archive_name_variables($directory);
		$config->set('volatile.postproc.directory', $directory);

		// Calculate relative remote filename
		$filename = empty($upload_as) ? basename($absolute_filename) : $upload_as;

		if (!empty($directory) && ($directory != '/'))
		{
			$filename = $directory . '/' . $filename;
		}

		// Store the absolute remote path in the class property
		$this->remote_path = $filename;

		// Do we have to upload in one go or do a multipart upload instead?
		Factory::getLog()->log(LogLevel::DEBUG, "S3 -- Single part upload of " . basename($absolute_filename));

		// Legacy single part uploads
		$result = $s3->putObject(
			ConnectorAmazons3::inputFile($absolute_filename, false), // File to read from
			$bucket, // Bucket name
			$filename, // Remote relative filename, including directory
			ConnectorAmazons3::ACL_BUCKET_OWNER_FULL_CONTROL, // ACL (bucket owner has full control, file owner gets full control)
			array(), // Meta headers
			// Other request headers
			array(
				// Amazon storage class (support for RRS - Reduced Redundancy Storage)
				'x-amz-storage-class' => $rrs ? 'REDUCED_REDUNDANCY' : 'STANDARD'
			)
		);

		// Return the result
		$this->propagateFromObject($s3);

		return $result;
	}

	/**
	 * Implements object deletion
	 */
	public function delete($path)
	{
		// Retrieve engine configuration data
		$config = Factory::getConfiguration();

		$accesskey = trim($config->get('engine.postproc.s3.accesskey', ''));
		$secret = trim($config->get('engine.postproc.s3.secretkey', ''));
		$usessl = $config->get('engine.postproc.s3.usessl', 0) == 0 ? false : true;
		$bucket = $config->get('engine.postproc.s3.bucket', '');
		$lowercase = $config->get('engine.postproc.s3.lowercase', 1);
		$endpoint = $config->get('engine.postproc.s3.customendpoint', '');

		$endpoint = trim($endpoint);

		if (!empty($endpoint))
		{
			$protoPos = strpos($endpoint, ':\\');

			if ($protoPos !== false)
			{
				$endpoint = substr($endpoint, $protoPos + 3);
			}

			$slashPos = strpos($endpoint, '/');

			if ($slashPos !== false)
			{
				$endpoint = substr($endpoint, $slashPos + 1);
			}
		}

		// Sanity checks
		if (empty($accesskey))
		{
			$this->setError('You have not set up your Amazon S3 Access Key');

			return false;
		}

		if (empty($secret))
		{
			$this->setError('You have not set up your Amazon S3 Secret Key');

			return false;
		}

		if (empty($bucket))
		{
			$this->setError('You have not set up your Amazon S3 Bucket');

			return false;
		}
		else
		{
			// Remove any slashes from the bucket
			$bucket = str_replace('/', '', $bucket);
			if ($lowercase)
			{
				$bucket = strtolower($bucket);
			}
		}

		// Create an S3 instance with the required credentials
		$s3 = ConnectorAmazons3::getInstance($accesskey, $secret, $usessl);

		if (!empty($endpoint))
		{
			$s3->defaultHost = $endpoint;
		}

		// Delete the file
		$result = $s3->deleteObject($bucket, $path);

		// Return the result
		$this->propagateFromObject($s3);

		return $result;
	}

	public function downloadToFile($remotePath, $localFile, $fromOffset = null, $length = null)
	{
		// Retrieve engine configuration data
		$config = Factory::getConfiguration();

		$accesskey = trim($config->get('engine.postproc.s3.accesskey', ''));
		$secret = trim($config->get('engine.postproc.s3.secretkey', ''));
		$usessl = $config->get('engine.postproc.s3.usessl', 0) == 0 ? false : true;
		$bucket = $config->get('engine.postproc.s3.bucket', '');
		$lowercase = $config->get('engine.postproc.s3.lowercase', 1);
		$rrs = $config->get('engine.postproc.s3.rrs', 1);
		$endpoint = $config->get('engine.postproc.s3.customendpoint', '');

		$endpoint = trim($endpoint);

		if (!empty($endpoint))
		{
			$protoPos = strpos($endpoint, ':\\');

			if ($protoPos !== false)
			{
				$endpoint = substr($endpoint, $protoPos + 3);
			}

			$slashPos = strpos($endpoint, '/');

			if ($slashPos !== false)
			{
				$endpoint = substr($endpoint, $slashPos + 1);
			}
		}

		// Sanity checks
		if (empty($accesskey))
		{
			$this->setError('You have not set up your Amazon S3 Access Key');

			return false;
		}

		if (empty($secret))
		{
			$this->setError('You have not set up your Amazon S3 Secret Key');

			return false;
		}

		if (empty($bucket))
		{
			$this->setError('You have not set up your Amazon S3 Bucket');

			return false;
		}
		else
		{
			// Remove any slashes from the bucket
			$bucket = str_replace('/', '', $bucket);
			if ($lowercase)
			{
				$bucket = strtolower($bucket);
			}
		}

		// Create an S3 instance with the required credentials
		$s3 = ConnectorAmazons3::getInstance($accesskey, $secret, $usessl);

		if (!empty($endpoint))
		{
			$s3->defaultHost = $endpoint;
		}

		if ($fromOffset && $length)
		{
			$toOffset = $fromOffset + $length - 1;
		}
		else
		{
			$toOffset = null;
		}
		$result = $s3->getObject($bucket, $remotePath, $localFile, $fromOffset, $toOffset);

		// Return the result
		$this->propagateFromObject($s3);

		return $result;
	}

	public function downloadToBrowser($remotePath)
	{
		// Retrieve engine configuration data
		$config = Factory::getConfiguration();

		$accesskey = trim($config->get('engine.postproc.s3.accesskey', ''));
		$secret = trim($config->get('engine.postproc.s3.secretkey', ''));
		$usessl = $config->get('engine.postproc.s3.usessl', 0) == 0 ? false : true;
		$bucket = $config->get('engine.postproc.s3.bucket', '');
		$lowercase = $config->get('engine.postproc.s3.lowercase', 1);
		$rrs = $config->get('engine.postproc.s3.rrs', 1);
		$endpoint = $config->get('engine.postproc.s3.customendpoint', '');

		$endpoint = trim($endpoint);

		if (!empty($endpoint))
		{
			$protoPos = strpos($endpoint, ':\\');

			if ($protoPos !== false)
			{
				$endpoint = substr($endpoint, $protoPos + 3);
			}

			$slashPos = strpos($endpoint, '/');

			if ($slashPos !== false)
			{
				$endpoint = substr($endpoint, $slashPos + 1);
			}
		}

		// Sanity checks
		if (empty($accesskey))
		{
			$this->setError('You have not set up your Amazon S3 Access Key');

			return false;
		}

		if (empty($secret))
		{
			$this->setError('You have not set up your Amazon S3 Secret Key');

			return false;
		}

		if (empty($bucket))
		{
			$this->setError('You have not set up your Amazon S3 Bucket');

			return false;
		}
		else
		{
			// Remove any slashes from the bucket
			$bucket = str_replace('/', '', $bucket);
			if ($lowercase)
			{
				$bucket = strtolower($bucket);
			}
		}

		// Create an S3 instance with the required credentials
		$s3 = ConnectorAmazons3::getInstance($accesskey, $secret, $usessl);

		if (!empty($endpoint))
		{
			$s3->defaultHost = $endpoint;
		}

		$expires = time() + 10; // Should be plenty of time for a simple redirection!
		$stringToSign = "GET\n\n\n$expires\n/$bucket/$remotePath";
		$signature = ConnectorAmazons3::__getHash($stringToSign);

		$url = $usessl ? 'https://' : 'http://';
		$url .= "$bucket.s3.amazonaws.com/$remotePath?AWSAccessKeyId=" . urlencode($accesskey) . "&Expires=$expires&Signature=" . urlencode($signature);

		return $url;
	}
}