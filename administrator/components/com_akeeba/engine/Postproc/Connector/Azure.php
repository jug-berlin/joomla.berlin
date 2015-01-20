<?php
/**
 * Copyright (c) 2009, RealDolmen
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of RealDolmen nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY RealDolmen ''AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL RealDolmen BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Microsoft
 * @package    Microsoft
 * @copyright  Copyright (c) 2009, RealDolmen (http://www.realdolmen.com)
 * @license    http://phpazure.codeplex.com/license
 */

namespace Akeeba\Engine\Postproc\Connector;

// Protection against direct access
defined('AKEEBAENGINE') or die();

use Akeeba\Engine\Postproc\Connector\Azure\AzureStorage;
use Akeeba\Engine\Postproc\Connector\Azure\Blob\Container;
use Akeeba\Engine\Postproc\Connector\Azure\Blob\Instance;
use Akeeba\Engine\Postproc\Connector\Azure\Credentials;
use Akeeba\Engine\Postproc\Connector\Azure\Credentials\Sharedkey;
use Akeeba\Engine\Postproc\Connector\Azure\Credentials\Sharedsignature;
use Akeeba\Engine\Postproc\Connector\Azure\Exception\Api;
use Akeeba\Engine\Postproc\Connector\Azure\Http\Response;
use Akeeba\Engine\Postproc\Connector\Azure\Http\Transport;
use Akeeba\Engine\Postproc\Connector\Azure\Retrypolicy;

/**
 * @category   Microsoft
 * @package    Microsoft_WindowsAzure
 * @subpackage Storage
 * @copyright  Copyright (c) 2009, RealDolmen (http://www.realdolmen.com)
 * @license    http://phpazure.codeplex.com/license
 */
class Azure extends AzureStorage
{
	/**
	 * ACL - Private access
	 */
	const ACL_PRIVATE = false;

	/**
	 * ACL - Public access
	 */
	const ACL_PUBLIC = true;

	/**
	 * Maximal blob size (in bytes)
	 */
	const MAX_BLOB_SIZE = 67108864;

	/**
	 * Maximal blob transfer size (in bytes)
	 */
	const MAX_BLOB_TRANSFER_SIZE = 4194304;

	/**
	 * Stream wrapper clients
	 *
	 * @var array
	 */
	protected static $_wrapperClients = array();

	/**
	 * SharedAccessSignature credentials
	 *
	 * @var Sharedsignature
	 */
	private $_sharedAccessSignatureCredentials = null;

	/**
	 * Creates a new Azure connector instance
	 *
	 * @param string                 $host            Storage host name
	 * @param string                 $accountName     Account name for Windows Azure
	 * @param string                 $accountKey      Account key for Windows Azure
	 * @param boolean                $usePathStyleUri Use path-style URI's
	 * @param Retrypolicy $retryPolicy     Retry policy to use when making requests
	 */
	public function __construct($host = AzureStorage::URL_DEV_BLOB, $accountName = Sharedkey::DEVSTORE_ACCOUNT, $accountKey = Sharedkey::DEVSTORE_KEY, $usePathStyleUri = false, Retrypolicy $retryPolicy = null)
	{
		parent::__construct($host, $accountName, $accountKey, $usePathStyleUri, $retryPolicy);

		// API version
		$this->_apiVersion = '2009-07-17';

		// SharedAccessSignature credentials
		$this->_sharedAccessSignatureCredentials = new Sharedsignature($accountName, $accountKey, $usePathStyleUri);
	}

	/**
	 * Get container
	 *
	 * @param string $containerName Container name
	 *
	 * @return Container
	 * @throws Api
	 */
	public function getContainer($containerName = '')
	{
		if ($containerName === '')
		{
			throw new Api('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName))
		{
			throw new Api('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}

		// Perform request
		$response = $this->performRequest($containerName, '?restype=container', Transport::VERB_GET, array(), false, null, AzureStorage::RESOURCE_CONTAINER, Credentials::PERMISSION_READ);
		if ($response->isSuccessful())
		{
			// Parse metadata
			$metadata = array();
			foreach ($response->getHeaders() as $key => $value)
			{
				if (substr(strtolower($key), 0, 10) == "x-ms-meta-")
				{
					$metadata[str_replace("x-ms-meta-", '', strtolower($key))] = $value;
				}
			}

			// Return container
			return new Container(
				$containerName,
				$response->getHeader('Etag'),
				$response->getHeader('Last-modified'),
				$metadata
			);
		}
		else
		{
			throw new Api($this->getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Put blob
	 *
	 * @param string $containerName     Container name
	 * @param string $blobName          Blob name
	 * @param string $localFileName     Local file name to be uploaded
	 * @param array  $metadata          Key/value pairs of meta data
	 * @param array  $additionalHeaders Additional headers. See http://msdn.microsoft.com/en-us/library/dd179371.aspx for more information.
	 *
	 * @return object Partial blob properties
	 * @throws Api
	 */
	public function putBlob($containerName = '', $blobName = '', $localFileName = '', $metadata = array(), $additionalHeaders = array())
	{
		if ($containerName === '')
		{
			throw new Api('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName))
		{
			throw new Api('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}
		if ($blobName === '')
		{
			throw new Api('Blob name is not specified.');
		}
		if ($localFileName === '')
		{
			throw new Api('Local file name is not specified.');
		}
		if (!file_exists($localFileName))
		{
			throw new Api('Local file not found.');
		}
		if ($containerName === '$root' && strpos($blobName, '/') !== false)
		{
			throw new Api('Blobs stored in the root container can not have a name containing a forward slash (/).');
		}

		// Check file size
		if (filesize($localFileName) >= self::MAX_BLOB_SIZE)
		{
			throw new Api('The maximum part size for Windows Azure is 64Mb. Please set the Part Size for Archive Splitting to 64Mb or lower and retry backup.');
		}

		// Create metadata headers
		$headers = array();
		foreach ($metadata as $key => $value)
		{
			$headers["x-ms-meta-" . strtolower($key)] = $value;
		}

		// Additional headers?
		foreach ($additionalHeaders as $key => $value)
		{
			$headers[$key] = $value;
		}

		// File contents
		$fileContents = file_get_contents($localFileName);

		// Resource name
		$resourceName = self::createResourceName($containerName, $blobName);

		// Perform request
		$response = $this->performRequest($resourceName, '', Transport::VERB_PUT, $headers, false, $fileContents, AzureStorage::RESOURCE_BLOB, Credentials::PERMISSION_WRITE);
		if ($response->isSuccessful())
		{
			return new Instance(
				$containerName,
				$blobName,
				$response->getHeader('Etag'),
				$response->getHeader('Last-modified'),
				$this->getBaseUrl() . '/' . $containerName . '/' . $blobName,
				strlen($fileContents),
				'',
				'',
				'',
				false,
				$metadata
			);
		}
		else
		{
			throw new Api($this->getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Set blob metadata
	 *
	 * Calling the Set Blob Metadata operation overwrites all existing metadata that is associated with the blob. It's not possible to modify an individual name/value pair.
	 *
	 * @param string $containerName     Container name
	 * @param string $blobName          Blob name
	 * @param array  $metadata          Key/value pairs of meta data
	 * @param array  $additionalHeaders Additional headers. See http://msdn.microsoft.com/en-us/library/dd179371.aspx for more information.
	 *
	 * @throws Api
	 */
	public function setBlobMetadata($containerName = '', $blobName = '', $metadata = array(), $additionalHeaders = array())
	{
		if ($containerName === '')
		{
			throw new Api('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName))
		{
			throw new Api('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}
		if ($blobName === '')
		{
			throw new Api('Blob name is not specified.');
		}
		if ($containerName === '$root' && strpos($blobName, '/') !== false)
		{
			throw new Api('Blobs stored in the root container can not have a name containing a forward slash (/).');
		}
		if (count($metadata) == 0)
		{
			return;
		}

		// Create metadata headers
		$headers = array();
		foreach ($metadata as $key => $value)
		{
			$headers["x-ms-meta-" . strtolower($key)] = $value;
		}

		// Additional headers?
		foreach ($additionalHeaders as $key => $value)
		{
			$headers[$key] = $value;
		}

		// Perform request
		$response = $this->performRequest($containerName . '/' . $blobName, '?comp=metadata', Transport::VERB_PUT, $headers, false, null, AzureStorage::RESOURCE_BLOB, Credentials::PERMISSION_WRITE);
		if (!$response->isSuccessful())
		{
			throw new Api($this->getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Get blob
	 *
	 * @param string $containerName     Container name
	 * @param string $blobName          Blob name
	 * @param string $localFileName     Local file name to store downloaded blob
	 * @param string $snapshotId        Snapshot identifier
	 * @param string $leaseId           Lease identifier
	 * @param array  $additionalHeaders Additional headers. See http://msdn.microsoft.com/en-us/library/dd179371.aspx for more information.
	 *
	 * @throws Api
	 */
	public function getBlob($containerName = '', $blobName = '', $localFileName = '', $snapshotId = null, $leaseId = null, $additionalHeaders = array())
	{
		if ($containerName === '')
		{
			throw new Api('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName))
		{
			throw new Api('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}
		if ($blobName === '')
		{
			throw new Api('Blob name is not specified.');
		}
		if ($localFileName === '')
		{
			throw new Api('Local file name is not specified.');
		}

		// Fetch data
		file_put_contents($localFileName, $this->getBlobData($containerName, $blobName, $snapshotId, $leaseId, $additionalHeaders));
	}

	/**
	 * Get blob data
	 *
	 * @param string $containerName     Container name
	 * @param string $blobName          Blob name
	 * @param string $snapshotId        Snapshot identifier
	 * @param string $leaseId           Lease identifier
	 * @param array  $additionalHeaders Additional headers. See http://msdn.microsoft.com/en-us/library/dd179371.aspx for more information.
	 *
	 * @return mixed Blob contents
	 * @throws Api
	 */
	public function getBlobData($containerName = '', $blobName = '', $snapshotId = null, $leaseId = null, $additionalHeaders = array())
	{
		if ($containerName === '')
		{
			throw new Api('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName))
		{
			throw new Api('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}
		if ($blobName === '')
		{
			throw new Api('Blob name is not specified.');
		}

		// Build query string
		$queryString = array();
		if (!is_null($snapshotId))
		{
			$queryString[] = 'snapshot=' . $snapshotId;
		}
		$queryString = self::createQueryStringFromArray($queryString);

		// Additional headers?
		$headers = array();
		if (!is_null($leaseId))
		{
			$headers['x-ms-lease-id'] = $leaseId;
		}
		foreach ($additionalHeaders as $key => $value)
		{
			$headers[$key] = $value;
		}

		// Resource name
		$resourceName = self::createResourceName($containerName, $blobName);

		// Perform request
		$response = $this->performRequest($resourceName, $queryString, 'GET', $headers, false, null, self::RESOURCE_BLOB, Credentials::PERMISSION_READ);
		if ($response->isSuccessful())
		{
			return $response->getBody();
		}
		else
		{
			throw new Api($this->getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Delete blob
	 *
	 * @param string $containerName     Container name
	 * @param string $blobName          Blob name
	 * @param string $snapshotId        Snapshot identifier
	 * @param string $leaseId           Lease identifier
	 * @param array  $additionalHeaders Additional headers. See http://msdn.microsoft.com/en-us/library/dd179371.aspx for more information.
	 *
	 * @throws Api
	 */
	public function deleteBlob($containerName = '', $blobName = '', $snapshotId = null, $leaseId = null, $additionalHeaders = array())
	{
		if ($containerName === '')
		{
			throw new Api('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName))
		{
			throw new Api('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}
		if ($blobName === '')
		{
			throw new Api('Blob name is not specified.');
		}
		if ($containerName === '$root' && strpos($blobName, '/') !== false)
		{
			throw new Api('Blobs stored in the root container can not have a name containing a forward slash (/).');
		}

		// Build query string
		$queryString = array();
		if (!is_null($snapshotId))
		{
			$queryString[] = 'snapshot=' . $snapshotId;
		}
		$queryString = self::createQueryStringFromArray($queryString);

		// Additional headers?
		$headers = array();
		if (!is_null($leaseId))
		{
			$headers['x-ms-lease-id'] = $leaseId;
		}
		foreach ($additionalHeaders as $key => $value)
		{
			$headers[$key] = $value;
		}

		// Resource name
		$resourceName = self::createResourceName($containerName, $blobName);

		// Perform request
		$response = $this->performRequest($resourceName, $queryString, 'DELETE', $headers, false, null, self::RESOURCE_BLOB, Credentials::PERMISSION_WRITE);
		if (!$response->isSuccessful())
		{
			throw new Api($this->getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Create resource name
	 *
	 * @param string $containerName Container name
	 * @param string $blobName      Blob name
	 *
	 * @return string
	 */
	public static function createResourceName($containerName = '', $blobName = '')
	{
		// Resource name
		$resourceName = $containerName . '/' . $blobName;
		if ($containerName === '' || $containerName === '$root')
		{
			$resourceName = $blobName;
		}
		if ($blobName === '')
		{
			$resourceName = $containerName;
		}

		return $resourceName;
	}

	/**
	 * Is valid container name?
	 *
	 * @param string $containerName Container name
	 *
	 * @return boolean
	 */
	public static function isValidContainerName($containerName = '')
	{
		if ($containerName == '$root')
		{
			return true;
		}

		if (!ereg("^[a-z0-9][a-z0-9-]*$", $containerName))
		{
			return false;
		}

		if (strpos($containerName, '--') !== false)
		{
			return false;
		}

		if (strtolower($containerName) != $containerName)
		{
			return false;
		}

		if (strlen($containerName) < 3 || strlen($containerName) > 63)
		{
			return false;
		}

		if (substr($containerName, -1) == '-')
		{
			return false;
		}

		return true;
	}

	/**
	 * Get error message from Response
	 *
	 * @param Response $response         Repsonse
	 * @param string                  $alternativeError Alternative error message
	 *
	 * @return string
	 */
	protected function getErrorMessage(Response $response, $alternativeError = 'Unknown error.')
	{
		$response = $this->parseResponse($response);
		if ($response && $response->Message)
		{
			return (string)$response->Message;
		}
		else
		{
			return $alternativeError;
		}
	}

	/**
	 * Generate block id
	 *
	 * @param int $part Block number
	 *
	 * @return string Windows Azure Blob Storage block number
	 */
	protected function generateBlockId($part = 0)
	{
		$returnValue = $part;
		while (strlen($returnValue) < 64)
		{
			$returnValue = '0' . $returnValue;
		}

		return $returnValue;
	}
}
