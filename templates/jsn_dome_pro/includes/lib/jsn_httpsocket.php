<?php
/*------------------------------------------------------------------------
# JSN Template Framework
# ------------------------------------------------------------------------
# author    JoomlaShine.com Team
# copyright Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
# Websites: http://www.joomlashine.com
# Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
# @license - GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
# @version $Id: jsn_httpsocket.php 17002 2012-10-13 09:39:19Z tuyetvt $
-------------------------------------------------------------------------*/

defined( '_JEXEC' ) or die( 'Restricted access' );

include_once dirname(__FILE__). DIRECTORY_SEPARATOR .'jsn_utils.php';
class JSNHTTPSocket
{
	var $_fp 		= null;
    var $_url 		= '';
    var $_host		= '';
    var $_protocol	= '';
    var $_uri		= '';
    var $_port		= '';
 	var $_query 	= '';
	var $_method 	= null;
	var $_data 	    = null;
	var $_referer   = null;
    var $_nofollow  = false;

    function JSNHTTPSocket($url, $data = null, $referer = null, $method = 'post', $nofollow = false)
    {
        $this->_url     = $url;
        $this->_data    = $data;
        $this->_referer = $referer;
        $this->_method  = $method;
        $this->_nofollow = $nofollow;
        $this->_scan_url();
    }

	function _scan_url()
	{
		$req = $this->_url;
		$url = parse_url($req);

		if(!isset($url['scheme']) || ($url['scheme'] != 'http'))
		{
		    return false;
		}

		$this->_protocol = $url['scheme'];
		$this->_host	 = $url['host'];
		$this->_uri 	 = (isset($url['path']) ? $url['path'] : '/');
		$this->_query 	 = (isset($url['query']) ? '?'.$url['query'] : '');

        if (isset($url['port']) && $url['port'])
        {
            $this->_port = (int) $url['port'];
        }
        else
        {
            $this->_port = (($url['scheme'] == 'https') ? 443 : 80);
        }
	}

    function socketDownload()
    {
        /**
         * When it come across this point from a JSNDownloadPackage instance,
         * the fsockopen will always be used, not cURL nor fOPEN
         */
		$objJSNUtil = JSNUtils::getInstance();
        if(!function_exists('fsockopen'))
        {
        	if($objJSNUtil->cURLCheckFunctions())
        	{
				return $this->cURLDownload();
        	}
        	elseif ($objJSNUtil->fOPENCheck())
        	{
				return $this->fOPENDownload();
        	}
        	else
        	{
        		return false;
        	}
        }
    	$crlf     = "\r\n";
        $response = '';
        $data     = '';

        if (is_array($this->_data) && count($this->_data) > 0)
        {
            $data = array();
            while (list ($n, $v) = each ($this->_data))
            {
                $data[] = "$n=$v";
            }
            $data = implode('&', $data);
            $contentType = "Content-type: application/x-www-form-urlencoded".$crlf;
        }
        else
        {
            $data = $this->_data;
            $contentType = "Content-type: text/xml".$crlf;
        }

        if (is_null($this->_referer))
        {
            $referer = JURI::root();
        }

        $this->_fp = @fsockopen(($this->_protocol == 'https' ? 'ssl://' : '') . $this->_host, $this->_port, $errno, $errstr);

        if ($this->_fp === false)
   		{
            return false;
        }

        if ($this->_method == 'post')
        {
        	$req = 'POST '.$this->_uri
		        	.' HTTP/1.1'.$crlf
		        	.'Host: '.$this->_host.$crlf
		        	.'Referer: '.$referer.$crlf.$contentType
		        	.'Content-length: '.strlen($data).$crlf
		        	.'Connection: close'.$crlf.$crlf.$data;
        }
        elseif ($this->_method == 'get')
        {
        	$req = 'GET '.$this->_uri.$this->_query
		        	.' HTTP/1.1'.$crlf
		        	.'Host: '.$this->_host.$crlf
		        	.'Connection: close'.$crlf.$crlf;
        	@fwrite($this->_fp, $req);
        }

        while (is_resource($this->_fp) && $this->_fp && !feof($this->_fp))
        {
           $response .= fread($this->_fp, 1024);
        }

        @fclose($this->_fp);

        $pos = @strpos($response, $crlf.$crlf);
        if ($pos === false)
        {
        	return($response);
        }
        $header 	= substr($response, 0, $pos);
        $body 		= substr($response, $pos + 2 * strlen($crlf));
        $headers 	= array();

        $lines = explode($crlf, $header);

        foreach ($lines as $line)
        {
            if (($pos = strpos($line, ':')) !== false)
            {
                $headers[strtolower(trim(substr($line, 0, $pos)))] = trim(substr($line, $pos+1));
            }
        }

        /* Modify a bit for sample data extension installing */
        if (isset($headers['location']) && $this->_nofollow !== true)
        {
            $http = new JSNHTTPSocket($headers['location'], $this->_data, $this->_referer, $this->_method);
            return $http->socketDownload();
        }
        else
        {
            return $body;
        }
    }

	function cURLDownload()
	{
		@set_time_limit(ini_get('max_execution_time'));
		$ch = curl_init($this->_url);

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($this->_nofollow === true)
        {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        }

		$result = curl_exec($ch);
		curl_close($ch);
		if ($result)
		{
			return $result;
		}
		return false;
	}

	function fOPENDownload()
	{
		$handle = @fopen($this->_url, 'r');
		if (!$handle)
		{
			return false;
		}
		$contents = null;
		while (!feof($handle))
		{
			$contents .= fread($handle, 8192);
			if ($contents === false)
			{
				return false;
			}
		}
		return $contents;
	}
}
?>
