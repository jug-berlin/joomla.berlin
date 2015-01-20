<?php
/**
 * @package AkeebaBackup
 * @subpackage SRP
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 *
 * @since 3.3
 */

defined('_JEXEC') or die();

// PHP version check
if(defined('PHP_VERSION')) {
	$version = PHP_VERSION;
} elseif(function_exists('phpversion')) {
	$version = phpversion();
} else {
	$version = '5.0.0'; // all bets are off!
}
if(!version_compare($version, '5.3.0', '>=')) return;

// Make sure Akeeba Backup is installed
if(!file_exists(JPATH_ADMINISTRATOR.'/components/com_akeeba')) {
	return;
}

// Load F0F
if(!defined('F0F_INCLUDED')) {
	include_once JPATH_SITE.'/libraries/f0f/include.php';
}
if(!defined('F0F_INCLUDED') || !class_exists('F0FLess', true))
{
	return;
}

JLoader::import('joomla.filesystem.file');
$db = JFactory::getDBO();

// Is Akeeba Backup enabled?
$query = $db->getQuery(true)
	->select($db->qn('enabled'))
	->from($db->qn('#__extensions'))
	->where($db->qn('element').' = '.$db->q('com_akeeba'))
	->where($db->qn('type').' = '.$db->q('component'));
$db->setQuery($query);
$enabled = $db->loadResult();

if(!$enabled)
{
	return;
}

// Is it the Pro release?
include_once JPATH_ADMINISTRATOR.'/components/com_akeeba/version.php';

if(!defined('AKEEBA_PRO'))
{
	return;
}

if(!AKEEBA_PRO)
{
	return;
}

JLoader::import('joomla.application.plugin');

class plgSystemSRP extends JPlugin
{
	private static $extraMessage = null;

	public function onSRPEnabled()
	{
		return true;
	}

	public function onAfterInitialise()
	{
		// Make sure this is the back-end
		$app = JFactory::getApplication();

		if (!in_array($app->getName(),array('administrator','admin')))
		{
			return;
		}

		// If the user tried to access Joomla!'s com_installer, hijack his
		// request and forward them to our private, improved implementation!
		$input			= JFactory::getApplication()->input;
		$component		= $input->getCmd('option', '');
		$task 			= $input->getCmd('task', '');
		$skipsrp 		= $input->getInt('skipsrp', -1);
		$view 			= $input->getCmd('view', '');
		$installtype 	= $input->getCmd('installtype', 'upload');
		$session		= JFactory::getSession();

		// If skipsrp was not defined, read it from the session
		if ($skipsrp < 0)
		{
			$skipsrp = $session->get('skipsrp', 0, 'akeeba.srp');
		}

		// Save skipsrp to the session
		$session->set('skipsrp', $skipsrp, 'akeeba.srp');

		// If we are told to disable SRP show a message in the installer main page
		if ($skipsrp && (empty($view) || (($view = 'update') && empty($task))))
		{
			$lang = JFactory::getLanguage();
			$lang->load('com_akeeba', JPATH_ADMINISTRATOR, 'en-GB', true, false);
			$lang->load('com_akeeba', JPATH_ADMINISTRATOR, null, true, true);
			$lang->load('lib_joomla', JPATH_ADMINISTRATOR, 'en-GB', true, false);
			$lang->load('lib_joomla', JPATH_ADMINISTRATOR, null, true, true);
			$lang->load('com_installer', JPATH_ADMINISTRATOR, 'en-GB', true, false);
			$lang->load('com_installer', JPATH_ADMINISTRATOR, null, true, true);

			// Append an Akeeba Backup SRP notification message to the installer's cached message
			$message = $app->getUserState('com_installer.message', '');

			if (!empty($message))
			{
				$message .= "<hr />";
			}

			$extraMessageFile = F0FTemplateUtils::parsePath('site://plugins/system/srp/tmpl/skipsrp.php', true);
			@ob_start();
			include_once $extraMessageFile;
			$extraMessage = @ob_get_clean();

			$message .= $extraMessage;

			$app->setUserState('com_installer.message', $message);

			return;
		}

		// All other cases when we are told not to enable SRP: do nothing at all.
		if ($skipsrp)
		{
			return;
		}

		// Only catch requests to the extensions installer
		if ($component != 'com_installer')
		{
			return;
		}

		// Only catch requests to com_installer's install and update views
		if (!in_array($view, array('install', 'update', 'akeeba', '')))
		{
			return;
		}

		$lang = JFactory::getLanguage();
		$lang->load('com_akeeba', JPATH_ADMINISTRATOR, 'en-GB', true, false);
		$lang->load('com_akeeba', JPATH_ADMINISTRATOR, null, true, true);
		$lang->load('com_installer', JPATH_ADMINISTRATOR, 'en-GB', true, false);
		$lang->load('com_installer', JPATH_ADMINISTRATOR, null, true, true);

		require_once F0FTemplateUtils::parsePath('admin://components/com_akeeba/models/installer.php', true);
		$model = new AkeebaModelInstaller();

		if (($view == '') || (($view == 'install') && empty($task)))
		{
			// Append an Akeeba Backup SRP notification message to the installer's cached message
			$message = $app->getUserState('com_installer.message', '');

			if (!empty($message))
			{
				$message .= "<hr />";
			}

			// Load and add our custom message
			$extraMessageFile = F0FTemplateUtils::parsePath('site://plugins/system/srp/tmpl/message.php', true);
			@ob_start();
			include_once $extraMessageFile;
			$extraMessage = @ob_get_clean();

			$message .= $extraMessage;

			$app->setUserState('com_installer.message', $message);
		}
		elseif (($view == 'update') && empty($task))
		{
			// Show an Akeeba Backup SRP notification message
			$lang = JFactory::getLanguage();
			$lang->load('com_akeeba', JPATH_ADMINISTRATOR, 'en-GB', true, false);
			$lang->load('com_akeeba', JPATH_ADMINISTRATOR, null, true, true);

			$extraMessageFile = F0FTemplateUtils::parsePath('site://plugins/system/srp/tmpl/update_message.php', true);
			@ob_start();
			include_once $extraMessageFile;

			self::$extraMessage = @ob_get_clean();
		}
		elseif ($task == 'install.install')
		{
			$context = 'com_installer.install';

			// Set FTP credentials, if given, saving them to the session for later use
			JClientHelper::setCredentialsFromRequest('ftp');
			$ftpCredentials = JClientHelper::getCredentials('ftp');
			$session = JFactory::getSession();
			$session->set('ftp', $ftpCredentials, 'akeeba');

			switch ($installtype)
			{
				case 'upload':
					if ($model->upload())
					{
						// Go to extraction step
						$url = JRoute::_('index.php?option=com_installer&view=install&task=install.akextract', false);
						JFactory::getApplication()->redirect($url);
					}
					else
					{
						// We failed :(
						$this->_cleanUpSession();
						$url = JRoute::_('index.php?option=com_installer&view=install', false);
						JFactory::getApplication()->redirect($url);
					}
					break;

				case 'url':
					if ($model->download())
					{
						// Go to extraction step
						$url = JRoute::_('index.php?option=com_installer&view=install&task=install.akextract', false);
						JFactory::getApplication()->redirect($url);
					}
					else
					{
						// We failed :(
						$this->_cleanUpSession();
						$url = JRoute::_('index.php?option=com_installer&view=install', false);
						JFactory::getApplication()->redirect($url);
					}
					break;

				default:
				case 'folder':
					// Save the package information to the session and go to the install.akpreinstall step
					$model->fromDirectory();
					$url = JRoute::_('index.php?option=com_installer&view=install&task=install.akpreinstall', false);
					JFactory::getApplication()->redirect($url);
					break;
			}
		}
		elseif ($task == 'install.akextract')
		{
			// Apply any saved FTP credentials
			$this->_applyFTPCredentials();

			// Extract the package and go to the install.akpreinstall step
			if ($model->extract())
			{
				$url = JRoute::_('index.php?option=com_installer&view=install&task=install.akpreinstall', false);
				JFactory::getApplication()->redirect($url);
			}
			else
			{
				// We failed :(
				$this->_cleanUpSession();
				$url = JRoute::_('index.php?option=com_installer&view=install', false);
				JFactory::getApplication()->redirect($url);
			}
		}
		elseif ($task == 'install.akpreinstall')
		{
			// Apply any saved FTP credentials
			$this->_applyFTPCredentials();

			// Perform the pre-installation inspection. This is where we decide if we will take an SRP backup.
			// If an SRP backup is to be taken we redirect to Akeeba Backup, else to the install.akrealinstall
			// task of the controller.
			$srpURL = $model->getSrpUrl();

			if ($srpURL !== false)
			{
				JFactory::getApplication()->redirect($srpURL);
			}
			else
			{
				$url = JRoute::_('index.php?option=com_installer&view=install&task=install.akrealinstall', false);
				JFactory::getApplication()->redirect($url);
			}
		}
		elseif ($task == 'install.akrealinstall')
		{
			// Apply any saved FTP credentials
			$this->_applyFTPCredentials();

			// Manipulate the input object, faking an install from directory
			$session = JFactory::getSession();
			$package = $session->get('package', null, 'akeeba');
			$input = JFactory::getApplication()->input;
			$input->set('task', 'install.install');
			$input->set('installtype', 'folder');
			$input->set('install_directory', $package['dir']);

			// Run the real installation
			$model->install();

			// Clean up
			$model->cleanUp();

			// If we have any more UIDs we are updating. In this case go to update.akupdatedl
			$uid = $session->get('uid', array(), 'akeeba');
			if (!empty($uid))
			{
				$session->set('package', null, 'akeeba');
				$session->set('compressed_package', null, 'akeeba');

				$redirect_url = JRoute::_('index.php?option=com_installer&view=update&task=update.akupdatedl', false);
				JFactory::getApplication()->redirect($redirect_url);

				return;
			}

			$this->_cleanUpSession();

			// Post installation redirection
			$app = JFactory::getApplication();
			$redirect_url = $app->getUserState('com_installer.redirect_url');

			if (empty($redirect_url))
			{
				$redirect_url = JRoute::_('index.php?option=com_installer&view=install', false);
			}
			else
			{
				// Wipe out the user state when we're going to redirect
				$app->setUserState('com_installer.redirect_url', '');
				$app->setUserState('com_installer.message', '');
				$app->setUserState('com_installer.extension_message', '');
			}

			JFactory::getApplication()->redirect($redirect_url);
		}
		elseif ($task == 'update.update')
		{
			// Set FTP credentials, if given, saving them to the session for later use
			JClientHelper::setCredentialsFromRequest('ftp');
			$ftpCredentials = JClientHelper::getCredentials('ftp');
			$session = JFactory::getSession();
			$session->set('ftp', $ftpCredentials, 'akeeba');

			// Get the list of update record IDs (uids)
			$uid   = $input->get('cid', array(), 'array');
			JArrayHelper::toInteger($uid, array());

			// Save uids in the session
			$session->set('uid', $uid, 'akeeba');

			// Go to update.akupdatedl step
			$url = JRoute::_('index.php?option=com_installer&view=update&task=update.akupdatedl', false);
			JFactory::getApplication()->redirect($url);
		}
		elseif ($task == 'update.akupdatedl')
		{
			// Apply any saved FTP credentials
			$this->_applyFTPCredentials();

			// Pop the next update ID and save the stack back to the session
			$session = JFactory::getSession();
			$uid = $session->get('uid', array(), 'akeeba');
			$currentUID = array_shift($uid);
			$session->set('uid', $uid, 'akeeba');

			// Download the update package
			if ($model->downloadUpdate($currentUID))
			{
				$url = JRoute::_('index.php?option=com_installer&view=install&task=install.akextract', false);
				JFactory::getApplication()->redirect($url);
			}
			else
			{
				// We failed :(
				$this->_cleanUpSession();
				$url = JRoute::_('index.php?option=com_installer&view=install', false);
				JFactory::getApplication()->redirect($url);
			}
		}
		else
		{
			return;
		}
	}

	public function onAfterRender()
	{
		if (empty(self::$extraMessage))
		{
			return;
		}

		if (version_compare(JVERSION, '3.0', 'gt'))
		{
			$buffer = JFactory::getApplication()->getBody();
			$buffer = str_replace('</html>', self::$extraMessage . '</html>', $buffer);
			JFactory::getApplication()->setBody($buffer);
		}
		else
		{
			$buffer = JResponse::getBody();
			$buffer = str_replace('</html>', self::$extraMessage . '</html>', $buffer);
			JResponse::setBody($buffer);
		}
	}

	/**
	 * Cleans up the session variables used in SRP
	 */
	private function _cleanUpSession()
	{
		$session = JFactory::getSession();
		$session->set('ftp', null, 'akeeba');
		$session->set('package', null, 'akeeba');
		$session->set('compressed_package', null, 'akeeba');
		$session->set('uid', null, 'akeeba');
	}

	private function _applyFTPCredentials()
	{
		$session = JFactory::getSession();
		$credentials = $session->get('ftp', array('user' => '', 'pass' => ''), 'akeeba');
		JClientHelper::setCredentials('ftp', $credentials['user'], $credentials['pass']);
	}
}
