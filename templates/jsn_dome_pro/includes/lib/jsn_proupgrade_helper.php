<?php
/**
 * @author    JoomlaShine.com http://www.joomlashine.com
 * @copyright Copyright (C) 2008 - 2012 JoomlaShine.com. All rights reserved.
 * @license   GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') or die('Restricted access');

include_once(dirname(__FILE__). DIRECTORY_SEPARATOR .'jsn_updater_helper.php');
include_once(dirname(__FILE__). DIRECTORY_SEPARATOR .'jsn_httpsocket.php');
include_once(dirname(__FILE__). DIRECTORY_SEPARATOR .'jsn_downloadtemplatepackage.php');

class JSNProUpgradeHelper extends JSNUpdaterHelper
{
	var $_template_version      = '';
	var $_edition               = '';
	var $_identified_name       = '';
	var $_joomla_version        = '';
	var $_full_name             = '';
	var $_name                  = '';

	function JSNProUpgradeHelper($templateInfo, $joomlaVersion)
	{
		parent::JSNUpdaterHelper();

		$this->_template_version      = $templateInfo['version'];
		$this->_identified_name       = str_replace('jsn', 'tpl', strtolower($templateInfo['name']));
		$this->_joomla_version        = $joomlaVersion;
		$this->_full_name             = strtolower($templateInfo['full_name']);
		$this->_name                  = strtolower($templateInfo['name']);
	}

	/**
	 * This function will authenticate the user as JoomlaShine customer and
	 * return the array of versions of template that the user purchased
	 * @return [type] [description]
	 */
	function authenticateCustomerInfo($ajax = true)
	{
		$post						= JRequest::get('post');
		$post['customer_password'] 	= JRequest::getString('customer_password', '', 'post', JREQUEST_ALLOWRAW);
		$link						= JSN_TEMPLATE_AUTOUPDATE_URL.'&identified_name='.urlencode($this->_identified_name).'&joomla_version='.urlencode($this->_joomla_version).'&username='.urlencode($post['customer_username']).'&password='.urlencode($post['customer_password']).'&upgrade=no';
		$objHTTPSocket 				= new JSNHTTPSocket($link, null, null, 'get');
		$result    					= $objHTTPSocket->socketDownload();
		
		$errorCode = strtolower((string) $result);
		$hasError = true;
		
		$rel = new stdClass();
		$rel->error = false;
		if ($result)
		{
			switch ($errorCode)
			{
				case 'err00':
					$message = 'Invalid Parameters! Cannot verify your product information.';
					break;
				case 'err01':
					$message = 'Invalid username or password. Please enter JoomlaShine customer account you created when you purchased the product.';
					break;
				case 'err02':
					$message = 'Installation is not authorized. We could not find the product in your order list. Seems like you did not purchase it yet.';
					break;
				case 'err03':
					$message = 'Requested file could not be found on server.';
					break;
				default:
					$hasError = false;
					break;
			}
			
			if ($hasError === true)
			{
				if ($ajax !== true) JError::raiseWarning('SOME_ERROR_CODE', $message);
				$rel->error = true;
				$rel->message = $message;
			}
			else
			{
				/* Standardize the returned array */
				$result = json_decode($result, true);
				if (!in_array('PRO UNLIMITED', $result['editions']))
				{
					$rel->error = true;
					$rel->message = 'Your account doesn\'t provided with UNLIMITED edition!';
					if ($ajax !== true) JError::raiseWarning('SOME_ERROR_CODE', $rel->message);
				}
			}
		}
		else
		{		
			$rel->error = true;
			$rel->message = 'Can not authorize your Customer account! Your server does not allow connection to Joomlashine server.';
			if ($ajax !== true) JError::raiseWarning('SOME_ERROR_CODE', $rel->message);
		}

		return $rel;
	}

	function changeToUnlimited()
	{
		$templateXmlPath = JPATH_ROOT. DIRECTORY_SEPARATOR .'templates'. DIRECTORY_SEPARATOR .$this->_template_folder_name. DIRECTORY_SEPARATOR .'templateDetails.xml';
		$fileContent = file_get_contents($templateXmlPath);
		$fileContent = str_replace("<edition>STANDARD</edition>", "<edition>UNLIMITED</edition>", $fileContent);
		
		jimport('joomla.filesystem.file');
		if (!JFile::write($templateXmlPath, $fileContent))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	function destroyUpgradeSession($sessionNameArray, $noJoomlaLogin = false)
	{
		if (is_array($sessionNameArray))
		{
			$session = JFactory::getSession();
			if ($noJoomlaLogin === true)
			{
				foreach ($sessionNameArray as $sessionKey => $sessionName) {
					if ($sessionKey != 'joomla_login')
					{
						$session->clear($sessionName, 'jsntemplatesession');
					}
				}
			}
			else
			{
				foreach ($sessionNameArray as $sessionName) {
					$session->clear($sessionName, 'jsntemplatesession');
				}
			}
		}
	}
}