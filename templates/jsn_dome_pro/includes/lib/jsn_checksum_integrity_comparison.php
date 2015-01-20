<?php
/**
 * @author    JoomlaShine.com http://www.joomlashine.com
 * @copyright Copyright (C) 2008 - 2011 JoomlaShine.com. All rights reserved.
 * @license   GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 * @version   $Id: jsn_checksum_integrity_comparison.php 17002 2012-10-13 09:39:19Z tuyetvt $
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
include_once dirname(__FILE__). DIRECTORY_SEPARATOR .'jsn_checksum.php';
class JSNChecksumIntegrityComparison extends JSNChecksum
{
	function JSNChecksumIntegrityComparison()
	{
		parent::JSNChecksum();
	}

	function getFileList()
	{
		$files 		= array ();
		$basePath	= $this->_template_folder_path;
		//Get the list of files from given folder
		$fileList 	= JFolder::files($basePath, '.', true, true, array('.checksum', '.svn', 'CVS', 'language'));
		if ($fileList !== false)
		{
			foreach ($fileList as $file)
			{
				$absolute_path			= str_replace('/', DIRECTORY_SEPARATOR, $file);
				$relative_path 			= str_replace(DIRECTORY_SEPARATOR, '/', str_replace($basePath.DIRECTORY_SEPARATOR, '',  $absolute_path));
				$files[$relative_path] 	= md5_file($absolute_path);
			}
			//unset($files[@$this->_checksum_file_name]);
			unset($files['template.checksum']);
			unset($files['templateDetails.xml']);
		}
		return $files;
	}

	function compareIntegrity()
	{
		$comparedContent 	= $this->getFileList();
		$comparingContent	= $this->_getFileContent($this->_template_folder_path. DIRECTORY_SEPARATOR .$this->_checksum_file_name, array('template.checksum'));
		return $this->compare($comparedContent, $comparingContent);
	}
}