<?php
/**
 * @author    JoomlaShine.com http://www.joomlashine.com
 * @copyright Copyright (C) 2008 - 2012 JoomlaShine.com. All rights reserved.
 * @license   GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 * @version   $Id$
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once('includes'. DIRECTORY_SEPARATOR .'jsn_defines.php');
require_once('includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_utils.php');
require_once('includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_readxmlfile.php');
require_once('includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_proupgrade_helper.php');

$jsnUtils   = new JSNUtils();
$jsnReadXml = new JSNReadXMLFile();

$joomlaVersion    = $jsnUtils->getJoomlaVersion(true);
$templateManifest = $jsnReadXml->getTemplateManifestFileInformation();
$templateLowName  = strtolower($templateManifest['full_name']);

$jsnUpgradeHelper = new JSNProUpgradeHelper($templateManifest, $joomlaVersion);

$task             = JRequest::getVar('task', '');
$templateStyleId  = JRequest::getInt('template_style_id', 0, 'GET');

$frontIndexPath = JURI::root() . $jsnUtils->determineFrontendIndex();

/* Session variables */
$session = JFactory::getSession();
$sessionTemp = array();

$sessionTemp['upgrader']         = md5('jsn_upgrader_' . $templateLowName);
$sessionTemp['joomla_login']     = md5('joomla_login_' . $templateLowName);
$sessionTemp['jsn_login']        = md5('jsn_login_' . $templateLowName);

$isAjax = false;

switch ($task)
{
	case 'upgrade_proceeded':
		$session->set($sessionTemp['upgrader'], true, 'jsntemplatesession');
		break;

	case 'joomla_login':
		JRequest::checkToken() or jexit('Invalid Token');
		$options = array();
		$credentials['username'] = JRequest::getVar('username', '', 'post', 'username');
		$credentials['password'] = JRequest::getString('password', '', 'post', JREQUEST_ALLOWRAW);

		jimport('joomla.user.helper');
		$app  = JFactory::getApplication();
		/* Perform the login action */
		$error = $app->login($credentials, $options);

		/* Check User permission */
		$canDo = new JObject;
		$user = JFactory::getUser();
		$actions = array('core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.delete');
		foreach ($actions as $action)
		{
			$canDo->set($action, $user->authorise($action, 'com_templates'));
		}

		if (!JError::isError($error) && $error && $canDo->get('core.manage'))
		{
			$session->set($sessionTemp['joomla_login'], true, 'jsntemplatesession');
		}

		break;

	case 'jsn_login':
		JRequest::checkToken() or jexit('Invalid Token');
		$result = $jsnUpgradeHelper->authenticateCustomerInfo($isAjax);

		if (!$result->error){
			$session->set($sessionTemp['jsn_login'], true, 'jsntemplatesession');
		}

		break;

	case 'ajax_install_pro':
		if ($session->get($sessionTemp['jsn_login'], false, 'jsntemplatesession'))
		{
			$isAjax = true;
			if ($jsnUpgradeHelper->changeToUnlimited())
			{
				echo json_encode(array('install' => true));
			}
			else
			{
				echo json_encode(array('install' => false, 'message' => '<span class="jsn-red-message">It was unable to upgrade template files. Please make sure you have permission to modify template files.</span>'));
			}
			$jsnUpgradeHelper->destroyUpgradeSession($sessionTemp);
		}
		break;

	case 'ajax_destroy_sesison':
		$isAjax = true;
		$jsnUpgradeHelper->destroyUpgradeSession($sessionTemp);
		echo json_encode(array('sessionclear' => true));
		break;

	default:
		break;
}

if ($isAjax) {
	jexit();
}

/* Begin to include appropriate HTML content */
include('elements/upgrader/jsn_head.php');

/* Intro page to show benefits of using PRO edition */
if (!$session->get($sessionTemp['upgrader'], false, 'jsntemplatesession'))
{
	$buyLink = JSN_BASE_BUY_LINK . str_replace('_', '-', $jsnUpgradeHelper->_name) . '-buy-now.html';
	include('elements/upgrader/jsn_upgradeinfo.php');
}
else
{
	if (!$session->get($sessionTemp['joomla_login'], false, 'jsntemplatesession'))
	{
		$session->set($sessionTemp['joomla_login'], false, 'jsntemplatesession');
		/* Require login with Joomla Super Administrator account */
		include('elements/upgrader/jsn_joomlaloginform.php');
	}
	else
	{
		if (!$session->get($sessionTemp['jsn_login'], false, 'jsntemplatesession'))
		{
			$session->set($sessionTemp['jsn_login'], false, 'jsntemplatesession');

			/* Joomla Login Successful. Change to JSN Customer Login */
			include('elements/upgrader/jsn_loginform.php');
		}
		else
		{
			/* Change to Upgrade view, start upgrade process */
			include('elements/upgrader/jsn_doupgrade.php');
		}
	}
}

include('elements/upgrader/jsn_foot.php');
