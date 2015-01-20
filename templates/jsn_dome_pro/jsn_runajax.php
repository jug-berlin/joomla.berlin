<?php
/*------------------------------------------------------------------------
# JSN Template Framework
# ------------------------------------------------------------------------
# author    JoomlaShine.com Team
# copyright Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
# Websites: http://www.joomlashine.com
# Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
# @license - GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
# @version $Id$
-------------------------------------------------------------------------*/

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once(dirname(__FILE__). DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'jsn_defines.php');
require_once(dirname(__FILE__). DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_httpsocket.php');
require_once(dirname(__FILE__). DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_readxmlfile.php');
require_once(dirname(__FILE__). DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_downloadpackage.php');
require_once(dirname(__FILE__). DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_sampledata_helper.php');
require_once(dirname(__FILE__). DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_updater_helper.php');
require_once(dirname(__FILE__). DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_auto_updater_helper.php');
require_once(dirname(__FILE__). DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_backup.php');
require_once(dirname(__FILE__). DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_ajax.php');
$obj_ajax = new JSNAjax();
$task = JRequest::getCmd('task');
switch($task)
{
	case 'checkCacheFolder':
		$obj_ajax->checkCacheFolder();
		break;
	case 'checkFolderPermission':
		$obj_ajax->checkFolderPermission();
		break;
	case 'checkVersion':
		$obj_ajax->checkVersion();
		break;
	case 'checkFilesIntegrity':
		$obj_ajax->checkFilesIntegrity();
		break;
	case 'downloadSampleDataPackage':
		$obj_ajax->downloadSampleDataPackage();
		break;
	case 'initialDownloadSampleData':
		$obj_ajax->initialDownloadSampleData();
		break;
	case 'initialDownloadPackage':
		$obj_ajax->initialDownloadPackage();
		break;
	case 'initialDownloadTemplatePackage':
		$obj_ajax->initialDownloadTemplatePackage();
		break;
	case 'getInstallableExtensions':
		$obj_ajax->getInstallableExtensions();
		break;
	case 'selectExtensions':
		$obj_ajax->selectExtensions();
		break;
	case 'installExtension':
		$obj_ajax->installExtension();
		break;
	case 'requestInstallExtension':
		$obj_ajax->requestInstallExtension();
		break;
	case 'reportFailedExtension':
		$obj_ajax->reportFailedExtension();
		break;
	case 'installSampleData':
		$obj_ajax->installSampleData();
		break;
	case 'backupModifiedFile':
		$obj_ajax->backupModifiedFile();
		break;
	case 'manualUpdateTemplate':
		$obj_ajax->manualUpdateTemplate();
		break;
	case 'prepareTemplatePackage':
		$obj_ajax->downloadTemplatePackage();
		break;
	case 'autoUpdateTemplate':
		$obj_ajax->autoUpdateTemplate();
		break;
	default:
	break;
}
?>
