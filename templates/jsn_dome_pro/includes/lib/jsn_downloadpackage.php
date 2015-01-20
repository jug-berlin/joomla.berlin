<?php
/**
 * @author    JoomlaShine.com http://www.joomlashine.com
 * @copyright Copyright (C) 2008 - 2011 JoomlaShine.com. All rights reserved.
 * @license   GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');
class JSNDownloadPackage
{
	var $_tmpPackageName 	= '';
	var $_downloadURL		= '';
	var $_tmpFolder			= '';
	var $_timeout			= 180;

	function JSNDownloadPackage($downloadURL, $packageName = '')
	{
		if ($packageName != '')
		{
			$this->_tmpPackageName 	= $packageName;
		}
		else
		{
			$this->_tmpPackageName = $this->_getFilenameFromURL($downloadURL);
		}
		$this->_downloadURL		= $downloadURL;
		$this->_tmpFolder		= JPATH_ROOT. DIRECTORY_SEPARATOR .'tmp'.DIRECTORY_SEPARATOR;
	}

	function download()
	{
		if ($this->_fsocketopenCheck())
		{
			return $this->fsocketdownload();
		}
		elseif ($this->_cURLCheckFunctions())
		{
			return $this->cURLdownload();
		}
		elseif ($this->_fOPENCheck())
		{
			return $this->fOPENdownload();
		}
		else
		{
			return false;
		}
		return false;
	}

	function cURLdownload()
	{
		@set_time_limit(ini_get('max_execution_time'));
		$path = $this->_tmpFolder.$this->_tmpPackageName;
		$cp = curl_init($this->_downloadURL);
		$fp = fopen($path, "w");
		curl_setopt($cp, CURLOPT_FILE, $fp);
		curl_setopt($cp, CURLOPT_HEADER, 0);
		curl_setopt($cp, CURLOPT_TIMEOUT, $this->_timeout);
		$result = curl_exec($cp);
		curl_close($cp);
		fclose($fp);
		if ($result)
		{
			return basename($path);
		}
		else
		{
			if (JFile::exists($path))
			{
				JFile::delete($path);
			}
			return $result;
		}
	}

	function fOPENdownload()
	{
		$target 	= false;
		$timeout 	= $this->_timeout;
		$old 		= @ini_set('default_socket_timeout', $timeout);
		$handle 	= @fopen($this->_downloadURL, 'r');

		$filename	= '';
		if (!$handle)
		{
			return false;
		}
		@ini_set('default_socket_timeout', $old);
		stream_set_timeout($handle, $timeout);
		stream_set_blocking($handle, 0);
		$metaData 	= stream_get_meta_data($handle);

		if($metaData['timed_out'] == true)
		{
			return false;
		}

		foreach ($metaData['wrapper_data'] as $wrapperData)
		{

			if (substr($wrapperData, 0, strlen("Content-Disposition")) == "Content-Disposition")
			{
				$fileName 	= explode ("\"", $wrapperData);
				$target 	= $fileName[1];
			}
		}
		if (!$target)
		{
			$filename = $this->_tmpPackageName;
		}
		else
		{
			$filename = basename($target);
		}
		$target = $this->_tmpFolder.$filename;
		$contents = null;

		while (!feof($handle))
		{
			$contents .= fread($handle, 8192);
			if ($contents === false)
			{
				return false;
			}
		}

		JFile::write($target, $contents);
		@set_time_limit(ini_get('max_execution_time'));
		fclose($handle);

		return basename($target);
	}

	function fsocketdownload()
	{
		$target 				= $this->_tmpFolder.$this->_tmpPackageName;
		$obj_http_request 		= new JSNHTTPSocket($this->_downloadURL, null, null, 'get');
		$contents    		  	= $obj_http_request->socketDownload();
		if ($contents == false)
		{
			return false;
		}
		JFile::write($target, $contents);
		return basename($target);
	}

	function _getFilenameFromURL($url)
	{
		if (is_string($url))
		{
			$parts = explode('/', $url);
			return $parts[count($parts) - 1];
		}
		return false;
	}

	function _cURLCheckFunctions()
	{
	  if(!function_exists("curl_init") && !function_exists("curl_setopt") && !function_exists("curl_exec") && !function_exists("curl_close")) return false;
	  return true;
	}

	function _fOPENCheck()
	{
		return (boolean) ini_get('allow_url_fopen');
	}

	function _fsocketopenCheck()
	{
		if (!function_exists('fsockopen')) return false;
		return true;
	}

	function readBinaryFile($file)
	{
		if (!JFile::exists($file)) return false;
		$file = @fopen($file, 'r');
		$contents = '';
		while (!feof($file))
		{
			$contents .= fread($file, 8192);
			if ($contents === false)
			{
				return false;
			}
		}
		fclose($file);
		return $contents;
	}
}