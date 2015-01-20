<?php
/**
 * @author    JoomlaShine.com http://www.joomlashine.com
 * @copyright Copyright (C) 2008 - 2011 JoomlaShine.com. All rights reserved.
 * @license   GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');
jimport('joomla.installer.helper');
include_once(dirname(__FILE__). DIRECTORY_SEPARATOR .'jsn_utils.php');
include_once(dirname(__FILE__). DIRECTORY_SEPARATOR .'jsn_updater_helper.php');
include_once(dirname(__FILE__). DIRECTORY_SEPARATOR .'jsn_httpsocket.php');
include_once(dirname(__FILE__). DIRECTORY_SEPARATOR .'jsn_downloadtemplatepackage.php');
class JSNAutoUpdaterHelper extends JSNUpdaterHelper
{
	var $_edition               = '';
	var $_based_identified_name = '';
	var $_identified_name       = '';
	var $_joomla_version        = '';
	var $_full_name             = '';
	var $_name                  = '';
	var $_template_version      = '';

	function JSNAutoUpdaterHelper()
	{
		parent::JSNUpdaterHelper();
		$this->setTemplateInfo();
	}

	function setTemplateInfo()
	{
		$objUtils			= JSNUtils::getInstance();
		$objReadXMLFile 	= new JSNReadXMLFile();
		$templateManifest	= $objReadXMLFile->getTemplateManifestFileInformation();
		if ($templateManifest['edition'] != '' && $templateManifest['edition'] != 'free')
		{
			$edition = 'pro '.$templateManifest['edition'];
		}
		else
		{
			$edition = 'free';
		}
		$this->_edition               = $edition;
		$this->_based_identified_name = '';
		$this->_identified_name       = str_replace('jsn', 'tpl', strtolower($templateManifest['name']));
		$this->_joomla_version        = $objUtils->getJoomlaVersion(true);
		$this->_full_name             = strtolower($templateManifest['full_name']);
		$this->_name                  = strtolower($templateManifest['name']);
		$this->_template_version      = $templateManifest['version'];
	}

	function authenticateCustomerInfo()
	{
		$post						= JRequest::get('post');
		$post['customer_password'] 	= JRequest::getString('customer_password', '', 'post', JREQUEST_ALLOWRAW);
		$link						= JSN_TEMPLATE_AUTOUPDATE_URL.'&identified_name='.urlencode($this->_identified_name).'&based_identified_name=&edition='.urlencode($this->_edition).'&joomla_version='.urlencode($this->_joomla_version).'&username='.urlencode($post['customer_username']).'&password='.urlencode($post['customer_password']).'&upgrade=no';
		$objHTTPSocket 				= new JSNHTTPSocket($link, null, null, 'get');
		$result    					= $objHTTPSocket->socketDownload();
		if ($result)
		{
			$decodeToJSON = json_decode($result);
			if (is_null($decodeToJSON))
			{
				$errorCode = strtolower((string) $result);

				switch ($errorCode)
				{
					case 'err01':
						$message = 'Invalid username or password. Please input JoomlaShine customer account you created when were purchasing the product';
						break;
					case 'err02':
						$message = 'Installation is not authorized. We could not find the product in your order list. Seems like you did not purchase it yet..';
						break;
					case 'err03':
						$message = 'Requested file is not found on server';
						break;
					default:
						$message = '';
						break;
				}
				JError::raiseWarning('SOME_ERROR_CODE', $message);
				return false;
			}
			else
			{
				return true;
			}
		}
		JError::raiseWarning('SOME_ERROR_CODE', 'Can not authorize your Customer account! Your server does not allow connection to Joomlashine server.');
		return false;
	}

	function downloadTemplatePackage($post)
	{
		$session 					= JFactory::getSession();
		$login_identifier 			= md5('state_update_login_'.strtolower($this->_template_name));
		$state_login				= $session->get($login_identifier, false, 'jsntemplatesession');
		if(!$state_login) jexit('Invalid Token');
		$link					= JSN_TEMPLATE_AUTOUPDATE_URL
			.'&identified_name='.urlencode($this->_identified_name)
			.'&based_identified_name='.urlencode($this->_based_identified_name)
			.'&edition='.urlencode($this->_edition)
			.'&joomla_version='.urlencode($this->_joomla_version)
			.'&username='.urlencode($post['customer_username'])
			.'&password='.urlencode($post['customer_password'])
			.'&product_version='.urlencode($this->_template_version)
			.'&upgrade=yes';
		$tmpName				= strtolower($this->_name.'_'.str_replace(' ', '_', $this->_edition).'.zip');
		$objJSNDownloadPackage  = new JSNDownloadTemplatePackage($link, $tmpName);
		return $objJSNDownloadPackage->download();
	}

	function unpack($path)
	{
		jimport('joomla.installer.helper');
		$package = JInstallerHelper::unpack($path);
		return $package;
	}
}
