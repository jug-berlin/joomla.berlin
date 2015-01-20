<?php
/**
 * @author    JoomlaShine.com http://www.joomlashine.com
 * @copyright Copyright (C) 2008 - 2011 JoomlaShine.com. All rights reserved.
 * @license   GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.archive');
include_once dirname(__FILE__). DIRECTORY_SEPARATOR .'jsn_downloadpackage.php';
class JSNDownloadTemplatePackage extends JSNDownloadPackage
{
	var $_objArchive = null;
	var $_msgError 	 = '';

	function JSNDownloadTemplatePackage($downloadURL, $packageName = '')
	{
		parent::JSNDownloadPackage($downloadURL, $packageName);
		$this->_objArchive = JArchive::getAdapter('zip');
	}

	function download()
	{
		/**
	 	 * Get the default timeout value first because after using set_time_limit()
		 * this value will be overwritten by what specified by the function.
		 * NOTE: this only affect Windows host!
		 */
		$defaultTimeout = ini_get('max_execution_time');
		@set_time_limit(360);

		if ($this->_fsocketopenCheck())
		{
			$result = $this->fsocketdownload();
		}
		elseif ($this->_cURLCheckFunctions())
		{
			$result = $this->cURLdownload();
		}
		elseif ($this->_fOPENCheck())
		{
			$result = $this->fOPENdownload();
		}
		else
		{
			return false;
		}

		@set_time_limit($defaultTimeout);
		return $result;
	}

	function cURLdownload()
	{
		$path = $this->_tmpFolder.$this->_tmpPackageName;
		$cp = curl_init($this->_downloadURL);
		$fp = fopen($path, "w");

		curl_setopt($cp, CURLOPT_FILE, $fp);
		curl_setopt($cp, CURLOPT_HEADER, false);
		curl_setopt($cp, CURLOPT_FOLLOWLOCATION, true);

		$result = curl_exec($cp);
		curl_close($cp);
		fclose($fp);

		if ($result)
		{
			$binary_data = $this->readBinaryFile($path);
			if (!$this->_objArchive->checkZipData($binary_data))
			{
				if (JFile::exists($path))
				{
					JFile::delete($path);
				}
				//$this->_msgError = $binary_data;
				return $binary_data;
			}

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
		$old 		= @ini_set('default_socket_timeout', $this->_timeout);
		$handle 	= @fopen($this->_downloadURL, 'r');

		$filename	= '';
		if (!$handle)
		{
			return false;
		}
		@ini_set('default_socket_timeout', $old);
		stream_set_timeout($handle, $this->_timeout);
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
		if (!$this->_objArchive->checkZipData($contents))
		{
			if (JFile::exists($target))
			{
				JFile::delete($target);
			}
			return $contents;
		}
		JFile::write($target, $contents);
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
		if (!$this->_objArchive->checkZipData($contents))
		{
			return $contents;
		}
		JFile::write($target, $contents);

		return basename($target);
	}
}
