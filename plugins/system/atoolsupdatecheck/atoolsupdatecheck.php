<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

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
if (!version_compare($version, '5.2.7', '>='))
{
	return;
}

// Joomla! version check
if (version_compare(JVERSION, '2.5', 'lt') && version_compare(JVERSION, '1.6.0', 'ge'))
{
	// Joomla! 1.6.x and 1.7.x: sorry fellas, no go.
	return;
}

// You can't fix stupid… but you can try working around it
if ((!function_exists('json_encode')) || (!function_exists('json_decode')))
{
	require_once JPATH_ADMINISTRATOR . '/components/com_admintools/helpers/jsonlib.php';
}

JLoader::import('joomla.application.plugin');

class plgSystemAtoolsupdatecheck extends JPlugin
{
	public function onAfterInitialise()
	{
		if (JFactory::getApplication()->isAdmin())
		{
			$this->loadLanguage();
			$msg = JText::_('PLG_SYSTEM_ATOOLSUPDATECHECK_MSG');

			if ($msg == 'PLG_SYSTEM_ATOOLSUPDATECHECK_MSG')
			{
				$msg = 'The <b>System - Admin Tools Update Email</b> plugin is now obsolete. You will find your Admin Tools updates under Extensions, Extensions Manager, Update in your Joomla! back-end. You will no longer be receiving emails about Admin Tools updates. Please disable this plugin to stop this message from appearing.';
			}

			JFactory::getApplication()->enqueueMessage($msg, 'warning');

			$db = JFactory::getDbo();

			// Let's get the information of the update plugin
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__extensions'))
				->where($db->qn('folder') . ' = ' . $db->quote('system'))
				->where($db->qn('element') . ' = ' . $db->quote('atoolsupdatecheck'))
				->where($db->qn('type') . ' = ' . $db->quote('plugin'))
				->order($db->qn('ordering') . ' ASC');
			$db->setQuery($query);
			$plugin = $db->loadObject();

			if (!is_object($plugin))
			{
				return;
			}

			// Otherwise, try to enable it and report false (so the user knows what he did wrong)
			$pluginObject = (object)array(
				'extension_id' => $plugin->extension_id,
				'enabled'      => 0
			);

			try
			{
				$db->updateObject('#__extensions', $pluginObject, 'extension_id');
				F0FUtilsCacheCleaner::clearPluginsCache();
			}
			catch (Exception $e)
			{
			}
		}
	}
}