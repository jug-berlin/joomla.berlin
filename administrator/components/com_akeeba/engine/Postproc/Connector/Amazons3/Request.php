<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 *
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license   GNU GPL version 3 or, at your option, any later version
 * @package   akeebaengine
 *
 *
 * This file contains the Amazons3 connector class which allows storing and
 * retrieving files from Amazon's Simple Storage Service (Amazon S3).
 * It is a subset of S3.php, written by Donovan Schonknecht and available
 * at http://undesigned.org.za/2007/10/22/amazon-s3-php-class under a
 * BSD-like license. I have merely removed the parts which weren't useful
 * to Akeeba Backup and changed the naming to fit the convention used in
 * our backup engine.
 *
 * Note for version 3.2: I have added multipart uploads, a feature which
 * wasn't included in the original version of the S3.php. As a result, this
 * file no longer reflects the original author's work and should not be
 * confused with it.
 *
 * Amazon S3 is a trademark of Amazon.com, Inc. or its affiliates.
 */

namespace Akeeba\Engine\Postproc\Connector\Amazons3;

// Protection against direct access
defined('AKEEBAENGINE') or die();

use Akeeba\Engine\Postproc\Connector\Amazons3;

/**
 * This is the legacy (AWS2 authentication) method for connecting to Amazon S3 and compatible storage engines. It is
 * currently used by the Google Storage and DreamObjects as well as the legacy Amazon S3 post-processing engines.
 */
class Request
{
	private $verb = null;

	private $bucket = null;

	private $uri = null;

	private $resource = '';

	private $parameters = array();

	private $amzHeaders = array();

	private $headers = array(
		'Host' => '',
		'Date' => '',
		'Content-MD5' => '',
		'Content-Type' => ''
	);

	/** @var bool|resource */
	public $fp = false;

	public $size = 0;

	public $data = false;

	public $response = null;


	/**
	 * Constructor
	 *
	 * @param string $verb   Verb
	 * @param string $bucket Bucket name
	 * @param string $uri    Object URI
	 *
	 * @return mixed
	 */
	function __construct($verb, $bucket = '', $uri = '', $defaultHost = 's3.amazonaws.com')
	{
		$this->verb = $verb;
		$this->bucket = $bucket;
		$this->uri = $uri !== '' ? '/' . str_replace('%2F', '/', rawurlencode($uri)) : '/';

		if ($this->bucket !== '')
		{
			$this->headers['Host'] = $this->bucket . '.' . $defaultHost;
			$this->resource = '/' . $this->bucket . $this->uri;
		}
		else
		{
			$this->headers['Host'] = $defaultHost;
			//$this->resource = strlen($this->uri) > 1 ? '/'.$this->bucket.$this->uri : $this->uri;
			$this->resource = $this->uri;
		}
		$this->headers['Date'] = gmdate('D, d M Y H:i:s T');

		$this->response = new \stdClass();
		$this->response->error = false;
	}


	/**
	 * Set request parameter
	 *
	 * @param string $key   Key
	 * @param string $value Value
	 *
	 * @return void
	 */
	public function setParameter($key, $value)
	{
		$this->parameters[$key] = $value;
	}


	/**
	 * Set request header
	 *
	 * @param string $key   Key
	 * @param string $value Value
	 *
	 * @return void
	 */
	public function setHeader($key, $value)
	{
		$this->headers[$key] = $value;
	}


	/**
	 * Set x-amz-meta-* header
	 *
	 * @param string $key   Key
	 * @param string $value Value
	 *
	 * @return void
	 */
	public function setAmzHeader($key, $value)
	{
		$this->amzHeaders[$key] = $value;
	}


	/**
	 * Get the S3 response
	 *
	 * @return object | false
	 */
	public function getResponse()
	{
		$query = '';
		if (sizeof($this->parameters) > 0)
		{
			$query = substr($this->uri, -1) !== '?' ? '?' : '&';
			foreach ($this->parameters as $var => $value)
			{
				if ($value == null || $value == '')
				{
					$query .= $var . '&';
				}
				// Parameters should be encoded (thanks Sean O'Dea)
				else
				{
					$query .= $var . '=' . rawurlencode($value) . '&';
				}
			}
			$query = substr($query, 0, -1);
			$this->uri .= $query;

			if (array_key_exists('acl', $this->parameters) ||
				array_key_exists('location', $this->parameters) ||
				array_key_exists('torrent', $this->parameters) ||
				array_key_exists('logging', $this->parameters) ||
				array_key_exists('uploads', $this->parameters) ||
				array_key_exists('uploadId', $this->parameters) ||
				array_key_exists('partNumber', $this->parameters)
			)
			{
				$this->resource .= $query;
			}
		}
		$url = ((Amazons3::$useSSL && extension_loaded('openssl')) ?
				'https://' : 'http://') . $this->headers['Host'] . $this->uri;

		// Basic setup
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_USERAGENT, 'AkeebaBackupProfessional/S3PostProcessor');

		if (Amazons3::$useSSL && extension_loaded('openssl'))
		{
			@curl_setopt($curl, CURLOPT_CAINFO, AKEEBA_CACERT_PEM);

			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		}

		curl_setopt($curl, CURLOPT_URL, $url);

		// Headers
		$headers = array();
		$amz = array();
		foreach ($this->amzHeaders as $header => $value)
		{
			if (strlen($value) > 0)
			{
				$headers[] = $header . ': ' . $value;
			}
		}
		foreach ($this->headers as $header => $value)
		{
			if (strlen($value) > 0)
			{
				$headers[] = $header . ': ' . $value;
			}
		}

		// Collect AMZ headers for signature
		foreach ($this->amzHeaders as $header => $value)
		{
			if (strlen($value) > 0)
			{
				$amz[] = strtolower($header) . ':' . $value;
			}
		}

		// AMZ headers must be sorted
		if (sizeof($amz) > 0)
		{
			sort($amz);
			$amz = "\n" . implode("\n", $amz);
		}
		else
		{
			$amz = '';
		}

		// Authorization string (CloudFront stringToSign should only contain a date)
		if ($this->headers['Host'] == 'cloudfront.amazonaws.com')
		{
			$stringToSign = $this->headers['Date'];
		}
		else
		{
			$stringToSign = $this->verb . "\n" . $this->headers['Content-MD5'] . "\n" .
				$this->headers['Content-Type'] . "\n" . $this->headers['Date'] . $amz . "\n" . $this->resource;
		}

		$headers[] = 'Authorization: ' . Amazons3::__getSignature($stringToSign);

		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
		curl_setopt($curl, CURLOPT_WRITEFUNCTION, array(&$this, '__responseWriteCallback'));
		curl_setopt($curl, CURLOPT_HEADERFUNCTION, array(&$this, '__responseHeaderCallback'));
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

		// Request types
		switch ($this->verb)
		{
			case 'GET':
				break;
			case 'PUT':
			case 'POST': // POST only used for CloudFront
				if ($this->fp !== false)
				{
					curl_setopt($curl, CURLOPT_PUT, true);
					curl_setopt($curl, CURLOPT_INFILE, $this->fp);
					if ($this->size >= 0)
					{
						curl_setopt($curl, CURLOPT_INFILESIZE, $this->size);
					}
				}
				elseif ($this->data !== false)
				{
					curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->verb);
					curl_setopt($curl, CURLOPT_POSTFIELDS, $this->data);
					if ($this->size >= 0)
					{
						curl_setopt($curl, CURLOPT_BUFFERSIZE, $this->size);
					}
				}
				else
				{
					curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->verb);
				}
				break;
			case 'HEAD':
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'HEAD');
				curl_setopt($curl, CURLOPT_NOBODY, true);
				break;
			case 'DELETE':
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
				break;
			default:
				break;
		}

		// Execute, grab errors
		if (curl_exec($curl))
		{
			$this->response->code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		}
		else
		{
			$this->response->error = array(
				'code'     => curl_errno($curl),
				'message'  => curl_error($curl),
				'resource' => $this->resource
			);
		}

		@curl_close($curl);

		// Parse body into XML
		if ($this->response->error === false && isset($this->response->headers['type']) &&
			$this->response->headers['type'] == 'application/xml' && isset($this->response->body)
		)
		{
			$this->response->body = simplexml_load_string($this->response->body);

			// Grab S3 errors
			if (!in_array($this->response->code, array(200, 204)) &&
				isset($this->response->body->Code, $this->response->body->Message)
			)
			{
				$this->response->error = array(
					'code'    => (string)$this->response->body->Code,
					'message' => (string)$this->response->body->Message
				);
				if (isset($this->response->body->Resource))
				{
					$this->response->error['resource'] = (string)$this->response->body->Resource;
				}
				unset($this->response->body);
			}
		}

		// Clean up file resources
		if ($this->fp !== false && is_resource($this->fp))
		{
			fclose($this->fp);
		}

		return $this->response;
	}


	/**
	 * CURL write callback
	 *
	 * @param resource &$curl CURL resource
	 * @param string   &$data Data
	 *
	 * @return integer
	 */
	protected function  __responseWriteCallback(&$curl, &$data)
	{
		if (in_array($this->response->code, array(200, 206)) && $this->fp !== false)
		{
			return fwrite($this->fp, $data);
		}
		else
		{
			if (!isset($this->response->body))
			{
				$this->response->body = '';
			}

			$this->response->body .= $data;
		}

		return strlen($data);
	}


	/**
	 * CURL header callback
	 *
	 * @param resource &$curl CURL resource
	 * @param string   &$data Data
	 *
	 * @return integer
	 */
	protected function  __responseHeaderCallback(&$curl, &$data)
	{
		if (($strlen = strlen($data)) <= 2)
		{
			return $strlen;
		}
		if (substr($data, 0, 4) == 'HTTP')
		{
			$this->response->code = (int)substr($data, 9, 3);
		}
		else
		{
			list($header, $value) = explode(': ', trim($data), 2);
			if ($header == 'Last-Modified')
			{
				$this->response->headers['time'] = strtotime($value);
			}
			elseif ($header == 'Content-Length')
			{
				$this->response->headers['size'] = (int)$value;
			}
			elseif ($header == 'Content-Type')
			{
				$this->response->headers['type'] = $value;
			}
			elseif ($header == 'ETag')
			{
				$this->response->headers['hash'] = $value{0} == '"' ? substr($value, 1, -1) : $value;
			}
			elseif (preg_match('/^x-amz-meta-.*$/', $header))
			{
				$this->response->headers[$header] = is_numeric($value) ? (int)$value : $value;
			}
		}

		return $strlen;
	}
}