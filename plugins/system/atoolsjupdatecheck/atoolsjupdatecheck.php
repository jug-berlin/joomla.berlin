<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

// Uncomment the following line to enable debug mode
// define('ATJUPDATEDEBUG',1);

// PHP version check
if (defined('PHP_VERSION'))
{
	$version = PHP_VERSION;
}
elseif (function_exists('phpversion'))
{
	$version = phpversion();
}
else
{
	$version = '5.0.0'; // all bets are off!
}
if (!version_compare($version, '5.2.7', 'ge'))
{
	return;
}

// You can't fix stupid… but you can try working around it
if ((!function_exists('json_encode')) || (!function_exists('json_decode')))
{
	require_once JPATH_ADMINISTRATOR . '/components/com_admintools/helpers/jsonlib.php';
}

JLoader::import('joomla.application.plugin');

class plgSystemAtoolsjupdatecheck extends JPlugin
{
	public function onAfterRender()
	{
		// Get the singleclick preference
		$singleClick = $this->params->get('singleclick', 0);

		// Is the One Click Action plugin enabled?
		$app = JFactory::getApplication();
		$jResponse = $app->triggerEvent('onOneClickActionEnabled');

		if (empty($jResponse) && $singleClick)
		{
			return;
		}

		$status = false;

		foreach ($jResponse as $response)
		{
			$status = $status || $response;
		}

		if (!$status && $singleClick)
		{
			return;
		}

		// Get the timeout for Joomla! updates
		jimport('joomla.application.component.helper');
		$component = JComponentHelper::getComponent('com_installer');
		$params = $component->params;
		$cache_timeout = $params->get('cachetimeout', 6, 'int');
		$cache_timeout = 3600 * $cache_timeout;

		// Do we need to run?
		// Store the last run timestamp inside out table
		$db = JFactory::getDBO();

		$query = $db->getQuery(true)
			->select($db->qn('value'))
			->from($db->qn('#__admintools_storage'))
			->where($db->qn('key') . ' = ' . $db->q('atoolsjupdatecheck_lastrun'));

		$last = (int)$db->setQuery($query)->loadResult();
		$now = time();

		if (!defined('ATJUPDATEDEBUG') && (abs($now - $last) < $cache_timeout))
		{
			return;
		}

		// Update last run status
		// If I have the time of the last run, I can update, otherwise insert
		if ($last)
		{
			$query = $db->getQuery(true)
				->update($db->qn('#__admintools_storage'))
				->set($db->qn('value') . ' = ' . $db->q($now))
				->where($db->qn('key') . ' = ' . $db->q('atoolsjupdatecheck_lastrun'));
		}
		else
		{
			$query = $db->getQuery(true)
				->insert($db->qn('#__admintools_storage'))
				->columns(array($db->qn('key'), $db->qn('value')))
				->values($db->q('atoolsjupdatecheck_lastrun') . ', ' . $db->q($now));
		}

		try
		{
			$result = $db->setQuery($query)->execute();
		}
		catch (Exception $exc)
		{
			$result = false;
		}

		if (!$result)
		{
			return;
		}

		// This is the extension ID for Joomla! itself
		$eid = 700;

		// Get any available updates
		$updater = JUpdater::getInstance();
		$results = $updater->findUpdates(array($eid), $cache_timeout);

		if (!$results)
		{
			return;
		}

		require_once JPATH_ADMINISTRATOR . '/components/com_installer/models/update.php';
		$model = JModelLegacy::getInstance('Update', 'InstallerModel');

		$model->setState('filter.extension_id', $eid);
		$updates = $model->getItems();

		if (empty($updates))
		{
			return;
		}

		$update = array_pop($updates);

		// Check the version. It must be different than the current version.
		if (version_compare($update->version, JVERSION, 'eq'))
		{
			return;
		}

		// If we're here, we have updates. Let's create an OTP.
		$uri = JURI::base();
		$uri = rtrim($uri, '/');
		$uri .= (substr($uri, -13) != 'administrator') ? '/administrator/' : '/';
		$link = 'index.php?option=com_joomlaupdate';

		$superAdmins = array();
		$superAdminEmail = $this->params->get('email', '');

		if (!empty($superAdminEmail))
		{
			$superAdmins = $this->_getSuperAdministrators($superAdminEmail);
		}

		if (empty($superAdmins))
		{
			$superAdmins = $this->_getSuperAdministrators();
		}

		if (empty($superAdmins))
		{
			return;
		}

		$this->loadLanguage();
		$email_subject = <<<ENDSUBJECT
THIS EMAIL IS SENT FROM YOUR SITE "[SITENAME]" - Update available
ENDSUBJECT;

		if ($singleClick)
		{
			$autoLoginReminder = <<< ALREND
This one-time use link, valid for 24 hours, will automatically log you in to
your site, [SITENAME], and perform the update automatically.

IMPORTANT NOTE: While this kind of one-time use links are convenient, they can
pose a security risk to your site. If this or any other update notification
email with a one-time use automatic log in update URL is intercepted by a
malicious third party they can use it to gain access to your site. We strongly
advise you to avoid using this feature.

ALREND;
		}
		else
		{
			$autoLoginReminder = <<< ALREND
Visiting this link will require you to enter your login credentials (typically
your username and password) into your site's administrator login page in order
to initiate the update process. If you are not sure about the legitimacy of
this email message we strongly recommend you to visit your site's
administrator page manually, log in, and check for the availability of updates
yourself.

ALREND;
		}

		$email_body = <<<ENDBODY
This email IS NOT sent by Joomla.org or Akeeba Ltd. It is sent automatically
by your own site, [SITENAME]

================================================================================
UPDATE INFORMATION
================================================================================

Your site has determined that there is an updated version of Joomla!
available for download.

nJoomla! version currently installed:       [CURVERSION]
Joomla! version available for installation: [NEWVERSION]

This email is sent to you by your site to remind you of this fact. The authors
of Joomla! (Open Source Matters) or Admin Tools (Akeeba Ltd) will not contact
you about available updates of Joomla!.

================================================================================
UPDATE INSTRUCTIONS
================================================================================

To install the update on [SITENAME] please click the following link. (If the URL
is not a link, simply copy & paste it to your browser).

Update link: [LINK]

$autoLoginReminder

================================================================================
WHY AM I RECEIVING THIS EMAIL?
================================================================================

This email has been automatically sent by a plugin you, or the person who built
or manages your site, has installed and explicitly activated. This plugin looks
for updated versions of Joomla! and sends an email notification to all Super
Users. You will receive several similar emails from your site, up to 6 times
per day, until you either update the software or disable these emails.

To disable these emails, please unpublish the 'System - Joomla! Update Email'
plugin in the Plugin Manager on your site.

If you do not understand what this means, please do not contact the authors of
Joomla! or Admin Tools. They are NOT sending you this email and they cannot
help you. Instead, please contact the person who built or manages your site.

If you are the person who built or manages your website, please note that you
activated the update email notification feature during Admin Tools' first run,
by clicking on a check box with a clear explanation of how this feature works
printed under it.

================================================================================
WHO SENT ME THIS EMAIL?
================================================================================

This email is sent to you by your own site, [SITENAME]

ENDBODY;

		$newVersion = $update->version;

		$jVersion = new JVersion;
		$currentVersion = $jVersion->getShortVersion();

		$jconfig = JFactory::getConfig();
		$sitename = $jconfig->get('sitename');

		$substitutions = array(
			'[NEWVERSION]' => $newVersion,
			'[CURVERSION]' => $currentVersion,
			'[SITENAME]'   => $sitename
		);

		// If Admin Tools Professional is installed, fetch the administrator secret key as well
		$adminpw = '';
		$modelFile = JPATH_ROOT . '/administrator/components/com_admintools/models/storage.php';
		if (@file_exists($modelFile))
		{
			include_once $modelFile;

			if (class_exists('AdmintoolsModelStorage'))
			{
				$model = JModelLegacy::getInstance('Storage', 'AdmintoolsModel');
				$adminpw = $model->getValue('adminpw', '');
			}
		}

		foreach ($superAdmins as $sa)
		{
			if ($singleClick)
			{
				$otp = plgSystemOneclickaction::addAction($sa->id, $link);
				if (is_null($otp))
				{
					// If the OTP is null, a database error occurred
					return;
				}
				elseif (empty($otp))
				{
					// If the OTP is empty, an OTP for the same action was already
					// created and it hasn't expired.
					continue;
				}
				$emaillink = $uri . 'index.php?oneclickaction=' . $otp;
			}
			else
			{
				$emaillink = $uri . $link;
			}

			if (!empty($adminpw))
			{
				$emaillink .= '&' . urlencode($adminpw);
			}

			$substitutions['[LINK]'] = $emaillink;

			foreach ($substitutions as $k => $v)
			{
				$email_subject = str_replace($k, $v, $email_subject);
				$email_body = str_replace($k, $v, $email_body);
			}

			$mailer = JFactory::getMailer();
			$mailfrom = $jconfig->get('mailfrom');
			$fromname = $jconfig->get('fromname');
			$mailer->setSender(array($mailfrom, $fromname));
			$mailer->addRecipient($sa->email);
			$mailer->setSubject($email_subject);
			$mailer->setBody($email_body);
			$mailer->Send();
		}
	}

	private function _getSuperAdministrators($email = null)
	{
		$db = JFactory::getDBO();

		$sql = $db->getQuery(true)
			->select(array(
				$db->qn('u') . '.' . $db->qn('id'),
				$db->qn('u') . '.' . $db->qn('email')
			))->from($db->qn('#__user_usergroup_map') . ' AS ' . $db->qn('g'))
			->join(
				'INNER',
				$db->qn('#__users') . ' AS ' . $db->qn('u') . ' ON (' .
				$db->qn('g') . '.' . $db->qn('user_id') . ' = ' . $db->qn('u') . '.' . $db->qn('id') . ')'
			)->where($db->qn('g') . '.' . $db->qn('group_id') . ' = ' . $db->q('8'))
			->where($db->qn('u') . '.' . $db->qn('sendEmail') . ' = ' . $db->q('1'));

		if (!empty($email))
		{
			$sql->where($db->qn('u') . '.' . $db->qn('email') . ' = ' . $db->q($email));
		}

		$db->setQuery($sql);

		return $db->loadObjectList();
	}
}