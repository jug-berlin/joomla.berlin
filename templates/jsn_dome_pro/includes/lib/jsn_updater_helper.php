<?php
/**
 * @author    JoomlaShine.com http://www.joomlashine.com
 * @copyright Copyright (C) 2008 - 2011 JoomlaShine.com. All rights reserved.
 * @license   GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');
jimport('joomla.installer.helper');
include_once(dirname(__FILE__). DIRECTORY_SEPARATOR .'jsn_archive.php');
require_once(dirname(__FILE__). DIRECTORY_SEPARATOR .'jsn_readxmlfile.php');
include_once dirname(__FILE__). DIRECTORY_SEPARATOR .'jsn_installer.php';
require_once(dirname(__FILE__). DIRECTORY_SEPARATOR .'jsn_checksum_integrity_comparison.php');
require_once(dirname(__FILE__). DIRECTORY_SEPARATOR .'jsn_checksum_file_comparison.php');
class JSNUpdaterHelper
{
	var $_template_folder_path  = '';
	var $_template_folder_name 	= '';
	var $_template_name 		= '';
	var $_obj_utils				= null;
	function JSNUpdaterHelper()
	{
		$this->_setPhysicalTmplInfo();
		require_once($this->_template_folder_path. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_utils.php');
		$this->_setUtilsInstance();
		$this->_setTmplInfo();
	}

	function _setUtilsInstance()
	{
		$this->_obj_utils = JSNUtils::getInstance();
	}

	function _setPhysicalTmplInfo()
	{
		$template_name 					= explode(DIRECTORY_SEPARATOR, str_replace(array('\includes\lib', '/includes/lib'), '', dirname(__FILE__)));
		$template_name 					= $template_name [count( $template_name ) - 1];
		$path_base 						= str_replace(DIRECTORY_SEPARATOR."templates". DIRECTORY_SEPARATOR .$template_name. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib', "", dirname(__FILE__));
		$this->_template_folder_name    = $template_name;
		$this->_template_folder_path 	= $path_base . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $template_name;

	}

	function _setTmplInfo()
	{
		$result 				 	= $this->_obj_utils->getTemplateDetails();
		$this->_template_name 		= $result->name;
	}

	function login($username, $password)
	{
		jimport('joomla.user.helper');
		$app 					 = JFactory::getApplication();
		$credentials 			 = array();
		$credentials['username'] = $username;
		$credentials['password'] = $password;

		// Get the log in options.
		$options = array();

		// Perform the login action
		$error = $app->login($credentials, $options);

		// Check if the log in succeeded.
		if (!JError::isError($error) && $error)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function getUserActions()
	{
		$user	 = JFactory::getUser();
		$result	 = new JObject;
		$actions = array('core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.delete');
		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, 'com_templates'));
		}
		return $result;
	}

	function backupModifiedFile()
	{
		$session 					= JFactory::getSession();
		$modifiedFileIdentifier 	= md5('state_modified_file_'.strtolower($this->_template_name));
		$checksum 					= new JSNChecksumIntegrityComparison();
		$result 					= $checksum->compareIntegrity();
		$files						= array();
		$paths						= array();
		$tmpPath					= JPATH_ROOT. DIRECTORY_SEPARATOR .'tmp';
		$tmpTemplatePath 			= $tmpPath. DIRECTORY_SEPARATOR .$this->_template_folder_name;
		$tmpFiles					= array();
		if (count($result))
		{
			$count = count($result['modified']);

			if ($count)
			{
				foreach ($result['modified'] as $value)
				{
					$slash 		= strrpos($value, '/');
					$files []  	= $value;
					if ($slash)
					{
						$paths  []  = substr($value, 0, $slash + 1);
					}
				}

				if (count($paths))
				{
					foreach ($paths as $path)
					{
						$path = str_replace('/', DIRECTORY_SEPARATOR, $path);
						$path = $tmpTemplatePath. DIRECTORY_SEPARATOR .$path;
						JFolder::create($path);
					}
				}
				else
				{
					JFolder::create($tmpTemplatePath);
				}

				if (count($files))
				{
					foreach($files as $file)
					{
						$slash 		= strrpos($file, '/');
						$fileName 	= JFile::getName(str_replace('/', DIRECTORY_SEPARATOR, $file));
						if ($slash)
						{
							$path = substr($file, 0, $slash + 1);
						}
						else
						{
							$path = '';
						}
						if ($path != '')
						{
							$path 		= str_replace('/', DIRECTORY_SEPARATOR, $path);
						}
						$tmpFiles[] = $file;
						$file 	 	= str_replace('/', DIRECTORY_SEPARATOR, $file);
						$dest 	 	= $tmpTemplatePath. DIRECTORY_SEPARATOR .$path.$fileName;
						$src		= $this->_template_folder_path. DIRECTORY_SEPARATOR .$file;
						JFile::copy($src, $dest);
					}
				}
				$session->set($modifiedFileIdentifier, $tmpFiles, 'jsntemplatesession');
				$zipResult = $this->createZip($tmpTemplatePath);
				if ($zipResult)
				{
					$tmpName = JFile::getName($zipResult);
					$backupPath = $this->_template_folder_path. DIRECTORY_SEPARATOR .'backups';
					if (!JFolder::exists($backupPath))
					{
						JFolder::create($backupPath);
					}
					$dest 	= $this->_template_folder_path. DIRECTORY_SEPARATOR .'backups'. DIRECTORY_SEPARATOR .$tmpName;
					$src 	= $zipResult;
					JFile::copy($src, $dest);
					JFolder::delete($tmpTemplatePath);
					return $tmpName;
				}
				else
				{
					return false;
				}
			}
		}
		return true;
	}

	function createZip($file)
	{
		//$objReadXMLFile 	= new JSNReadXMLFile();
		//$templateManifest	= $objReadXMLFile->getTemplateManifestFileInformation();
		$manifestCache		= $this->_obj_utils->getTemplateManifestCache();
		$manifestCache		= json_decode($manifestCache);
		$version 			= $manifestCache->version;
		$fileName 			= JPATH_ROOT. DIRECTORY_SEPARATOR .'tmp'. DIRECTORY_SEPARATOR .date('Ymd_H\hi\ms\s').'_'.$this->_template_folder_name.'_'.$version.'.bak.zip';
		$zip 				= new JSNZIPFile($fileName);
		$zip->setOptions(array('basedir' => $file, 'inmemory' => 0, 'recurse' => 1, 'storepaths' => 1));
		$zip->addFiles(array('*.*'));
		if ($zip->createArchive() === false)
		{
			return false;
		}
		return $fileName;
	}

	function compareChecksumFile($comparedFilePath)
	{
		$checksum 	= new JSNChecksumFileComparison($comparedFilePath);
		return $checksum->compareFileContent();
	}

	function findManifest($path)
	{
		$xmlfiles = JFolder::files($path, '.xml$', 1, true);
		if (!empty($xmlfiles))
		{

			foreach ($xmlfiles as $file)
			{
				$manifest = $this->isManifest($file);

				if (!is_null($manifest))
				{
					return $manifest;
				}
			}
			return false;
		}
		else
		{
			return false;
		}
	}

	function isManifest($file)
	{
		$xml = JFactory::getXML($file);
		if (!$xml)
		{
			return null;
		}
		if ($xml->getName() != 'install' && $xml->getName() != 'extension')
		{
			return null;
		}

		return $xml;
	}

	function downloadFile($type, $file_name, $path = '', $delete = false)
	{
		jimport('joomla.filesystem.file');
		if (empty($path))
		{
			$file_path 		= $this->_template_folder_path. DIRECTORY_SEPARATOR .'backups'. DIRECTORY_SEPARATOR .$file_name;
		}
		else
		{
			$file_path 		= $path. DIRECTORY_SEPARATOR .$file_name;
		}
		if(!JFile::exists($file_path))
		{
			return false;
		}
		$file_size 		= filesize($file_path);
		switch ($type)
		{
			case "zip":
				header("Content-Type: application/zip");
				break;
			case "bzip":
				header("Content-Type: application/x-bzip2");
				break;
			case "gzip":
				header("Content-Type: application/x-gzip");
				break;
			case "tar":
				header("Content-Type: application/x-tar");
		}
		$header = "Content-Disposition: attachment; filename=\"";
		$header .= $file_name;
		$header .= "\"";
		header($header);
		header('Content-Description: File Transfer');
		header("Content-Length: " . $file_size);
		header("Content-Transfer-Encoding: binary");
		header("Cache-Control: no-cache, must-revalidate, max-age=60");
		header("Expires: Sat, 01 Jan 2000 12:00:00 GMT");
		ob_clean();
   	 	flush();
		@readfile($file_path);
		if ($delete)
		{
			JFile::delete($file_path);
		}
	}

	function destroySession()
	{
		$session 					= JFactory::getSession();
		$login_identifier 			= md5('state_update_login_'.strtolower($this->_template_name));
		$modified_file_identifier 	= md5('state_modified_file_'.strtolower($this->_template_name));
		$customer_info_identifier 	= md5('state_update_customer_info_'.strtolower($this->_template_name));
		$session->set($login_identifier, false, 'jsntemplatesession');
		$session->set($modified_file_identifier, array(), 'jsntemplatesession');
		$session->set($customer_info_identifier, array(), 'jsntemplatesession');
		return true;
	}

	function deleteInstallationPackage($file, $extract_folder)
	{
		if (is_file($file))
		{
			JFile::delete($file);
		}

		if (is_dir($extract_folder))
		{
			JFolder::delete($extract_folder);
		}

		return true;
	}
}