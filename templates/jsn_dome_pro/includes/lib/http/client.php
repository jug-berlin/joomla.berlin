<?php
abstract class JSNTPLHttpClient
{
	const FOLLOW_LOCATION		= 52;
	const BUFFER_SIZE			= 98;
	const MAX_REDIRECTS			= 68;
	const USER_AGENT			= 10018;
	const CONNECTION_TIMEOUT 	= 78;
	const READ_TIMEOUT		 	= 13;

	/**
	 * Create HTTP Request object
	 * 
	 * @param   array   $options  Object options
	 * @param   string  $adapter  Adapter to be used, null to auto detection
	 * 
	 * @return  JSNHttpAdapter
	 */
	public static function createRequest (array $options = array(), $adapter = null)
	{
		return new JSNTPLHttpAdapterSocket($options);
	}
}
