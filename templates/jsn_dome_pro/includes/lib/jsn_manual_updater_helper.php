<?php
/**
 * @author    JoomlaShine.com http://www.joomlashine.com
 * @copyright Copyright (C) 2008 - 2011 JoomlaShine.com. All rights reserved.
 * @license   GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');
jimport('joomla.installer.helper');
include_once(dirname(__FILE__). DIRECTORY_SEPARATOR .'jsn_updater_helper.php');
class JSNManualUpdaterHelper extends JSNUpdaterHelper
{
	function JSNManualUpdaterHelper()
	{
		parent::JSNUpdaterHelper();
	}

	function getPackageFromUpload()
	{
		$install_file = JRequest::getVar('package', null, 'files', 'array');
		if (!(bool) ini_get('file_uploads'))
		{
			$msg 	= 'File upload function is disabled, please enable it in file "php.ini"';
			JError::raiseWarning('SOME_ERROR_CODE', JText::_($msg));
			return false;
		}
		if (!extension_loaded('zlib'))
		{
			$msg = 'Zlib library is disabled, please enable it in file "php.ini"';
			JError::raiseWarning('SOME_ERROR_CODE', JText::_($msg));
			return false;
		}
		if ($install_file['name'] == '')
		{
			$msg 	= 'The package is not selected, please download and select it';
			JError::raiseWarning('SOME_ERROR_CODE', JText::_($msg));
			return false;
		}
		if (JFile::getExt($install_file['name']) != 'zip')
		{
			$msg = 'The package has incorrect format, please use exactly the file you downloaded';
			JError::raiseWarning('SOME_ERROR_CODE', JText::_($msg));
			return false;
		}

		$tmp_dest 	= JPATH_ROOT. DIRECTORY_SEPARATOR .'tmp'. DIRECTORY_SEPARATOR .$install_file['name'];
		$tmp_src	= $install_file['tmp_name'];

		if (!JFile::upload($tmp_src, $tmp_dest))
		{
			$msg = 'Folder "tmp" is Unwritable, please set it to Writable (chmod 777). You can set the folder back to Unwritable after sample data installation';
			JError::raiseWarning('SOME_ERROR_CODE', JText::_($msg));
			return false;
		}

		$package = JInstallerHelper::unpack($tmp_dest);
		return $package;
	}
}