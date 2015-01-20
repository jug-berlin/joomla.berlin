<?php
abstract class JSNTPLHttpAdapter
{
	/**
	 * Request data
	 * @var  array
	 */
	protected $_data = array();

	/**
	 * Request headers
	 * @var  array
	 */
	protected $_requestHeaders = array();

	/**
	 * Response object
	 * @var  stdClass
	 */
	protected $_response = null;

	/**
	 * Redirected times
	 * @var  int
	 */
	protected $_redirectedTimes = 0;

	/**
	 * Request options
	 * @var  array
	 */
	protected $_options = array(
		JSNTPLHttpClient::FOLLOW_LOCATION 		=> true,
		JSNTPLHttpClient::MAX_REDIRECTS 		=> 10,
		JSNTPLHttpClient::BUFFER_SIZE			=> 4096,
		JSNTPLHttpClient::USER_AGENT			=> 'JSNTPLHttpClient Agent',
		JSNTPLHttpClient::CONNECTION_TIMEOUT 	=> 30,
		JSNTPLHttpClient::READ_TIMEOUT			=> 30
	);

	/**
	 * Registered callback functions
	 * @var  array
	 */
	protected $_callbacks = array();

	/**
	 * Class constructor
	 * 
	 * @param   array  $options  Options
	 */
	public function __construct (array $options = array())
	{
		if (!empty($options))
			$this->_options = array_merge($options, $this->_options);
	}

	/**
	 * Add a header value to request headers
	 * 
	 * @param   string  $name   Name of header to be added
	 * @param   string  $value  Header value
	 * 
	 * @return  JSNTPLHttpAdapter
	 */
	public function addHeader ($name, $value)
	{
		$this->_requestHeaders[$name] = $value;

		// Return $this to allow method chaining
		return $this;
	}

	/**
	 * Add a list of headers to request headers
	 * 
	 * @param   array  $headers  List of headers to be added
	 * 
	 * @return  JSNTPLHttpAdapter
	 */
	public function addHeaders (array $headers)
	{
		foreach ($headers as $name => $value)
		{
			$this->_requestHeaders[$name] = $value;
		}

		return $this;
	}

	/**
	 * Add a cookie to an HTTP request headers
	 * 
	 * @param   JSNTPLHttpCookie  $cookie  Cookie value to be added
	 * 
	 * @return  JSNTPLHttpAdapter
	 */
	public function addCookie (JSNTPLHttpCookie $cookie)
	{

	}

	/**
	 * Register callback function or method to receive notification
	 * when processing HTTP request
	 * 
	 * @param   string  $action    Action to be registered
	 * @param   string  $function  Name of function to be called
	 * @param   object  $object    Object instance that will call the method
	 * 
	 * @return  JSNTPLHttpAdapter
	 */
	public function registerCallback ($action, $function, $object = null)
	{
		if (!isset($this->_callbacks[$action]))
			$this->_callbacks[$action] = array();

		$this->_callbacks[$action][] = array('function' => $function, 'object' => $object);
		return $this;
	}

	/**
	 * Trigger all callback function when an action is called
	 * 
	 * @param   string  $action  Action to notify
	 * @param   array   $params  Action parameters
	 * 
	 * @return  JSNTPLHttpAdapter
	 */
	protected function _notify ($action, array $params = array())
	{
		if (isset($this->_callbacks[$action]) && is_array($this->_callbacks[$action]))
		{
			foreach ($this->_callbacks[$action] as $callback)
			{
				$function  = $callback['function'];
				$object    = $callback['object'];

				if (is_object($object) && method_exists($object, $function))
				{
					call_user_func_array(array($object, $function), $params);
					continue;
				}

				if (function_exists($function))
				{
					call_user_func_array($function, $params);
					continue;
				}
			}
		}

		return $this;
	}

	/**
	 * Build headers string for HTTP request
	 * 
	 * @param   string  $method   HTTP Request method
	 * @param   string  $path     URL Path
	 * @param   array   $headers  Additional headers
	 * 
	 * @return  string
	 */
	protected function _buildHeaders ($method, $path, array $headers = array())
	{
		$headerString = array("{$method} {$path} HTTP/1.1");

		foreach ($headers as $name => $value)
		{
			if (is_array($value))
				$value = implode(';', $value);
			else if (!is_numeric($name) && is_string($value))
				$headerString[] = "{$name}: {$value}";
			else
				$headerString[] = $value;
		}

		return implode("\r\n", $headerString) . "\r\n\r\n";
	}

	/**
	 * Parse headers that responsed from HTTP request
	 * 
	 * @param   string  $content  Responsed content to
	 * 
	 * @return  array
	 */
	protected function _parseResponse ($content)
	{
		$result = new stdClass;
		$result->headers = array();
		$result->status  = null;
		$result->code    = null;
		$result->content = substr($content, strpos($content, "\r\n\r\n"));
		$result->raw     = $content;
		$headerString = substr($content, 0, strpos($content, "\r\n\r\n"));

		// Parse status
		if (preg_match('/^HTTP\/(1\.0|1\.1)\s+([0-9]+)\s+([^\r\n]+)/i', $headerString, $matched))
		{
			$result->code   = $matched[2];
			$result->status = $matched[3];
		}

		// Parse response headers
		foreach (explode("\r\n", $headerString) as $line)
		{
			if (preg_match('/([^:]+):(.*?)$/i', $line, $matched))
			{
				$key = strtolower($matched[1]);
				$value = trim($matched[2]);

				if ($key == 'set-cookie')
				{
					$segments = explode(';', $value);
					$cookieParams = array();

					list($cookieName, $cookieValue) = explode('=', $segments[0]);
					unset($segments[0]);

					foreach ($segments as $param)
					{
						if (strpos($param, '=') === false)
						{
							$cookieParams[] = trim($param);
							continue;
						}

						list($paramName, $paramValue) = explode('=', $param);
						$cookieParams[trim(strtolower($paramName))] = trim($paramValue);
					}

					$result->cookies[] = array('name' => trim($cookieName), 'value' => trim($cookieValue), 'extra' => $cookieParams);
				}

				$result->headers[$key] = $value;
			}
		}

		return $result;
	}

	/**
	 * Retrieve detailed information of an URL
	 * 
	 * @param   string  $url  URL to be parsed
	 * 
	 * @return  array
	 */
	protected function _parseURL ($url)
	{
		$info = parse_url($url);
		$predefinedPorts = array(
			'https' => 443,
			'http'  => 80,
			'ftp'   => 21,
			'smtp'  => 25
		);

		if (!isset($info['protocol']))
			$info['protocol'] = $info['scheme'];

		if ($info['scheme'] == 'https')
			$info['protocol'] = 'ssl';

		if (!isset($info['path']))
			$info['path'] = '/';

		if (!isset($info['port']) && isset($predefinedPorts[$info['scheme']]))
			$info['port'] = $predefinedPorts[$info['scheme']];

		return $info;
	}

	/**
	 * Retrieve HTTP response header from an URL
	 * 
	 * @param   string  $url      URL to request
	 * @param   array   $headers  Custom headers for this request
	 * 
	 * @return  boolean
	 */
	public abstract function head ($url, array $headers = array());

	/**
	 * Make a request that use GET as request method
	 * 
	 * @param   string  $url      URL to request
	 * @param   array   $headers  Custom headers for this request
	 * 
	 * @return  boolean
	 */
	public abstract function get ($url, array $headers = array());

	/**
	 * Make a POST request to an URL
	 * 
	 * @param   string  $url      URL to request
	 * @param   array   $data     Data that will be posted to the URL
	 * @param   array   $headers  Custom headers for this request
	 * 
	 * @return  boolean
	 */
	public abstract function post ($url, array $data = array(), array $headers = array());

	/**
	 * Create a HTTP Request to download a file from another server
	 * 
	 * @param   string  $url      URL to the file
	 * @param   array   $path     Path to save file
	 * @param   array   $headers  Custom headers for this request
	 * 
	 * @return  boolean
	 */
	public abstract function download ($url, $path, array $headers = array());
}
