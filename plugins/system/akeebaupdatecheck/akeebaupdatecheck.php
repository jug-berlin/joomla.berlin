<?php
/**
 * @package AkeebaBackup
 * @subpackage OneClickAction
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
if(!version_compare($version, '5.0.0', '>=')) return;

JLoader::import('joomla.application.plugin');

class plgSystemAkeebaupdatecheck extends JPlugin
{
	public function onAfterInitialise()
	{
		if (JFactory::getApplication()->isAdmin())
		{
			$this->loadLanguage();
			$pluginName = JText::_('PLG_SYSTEM_AKEEBAUPDATECHECK_TITLE');

			if ($pluginName == 'PLG_SYSTEM_AKEEBAUPDATECHECK_TITLE')
			{
				$pluginName = 'System - Akeeba Backup Update Check (OBSOLETE)';
			}

			$msg = JText::sprintf('PLG_SYSTEM_AKEEBAUPDATECHECK_MSG', $pluginName);

			if ($msg == 'PLG_SYSTEM_AKEEBAUPDATECHECK_MSG')
			{
				$msg = sprintf('The plugin %s is obsolete and will now disable itself. Please use Extensions, Extension Manager, Update in the back-end of your site to receive and apply updates for Akeeba Backup.', $pluginName);
			}

			JFactory::getApplication()->enqueueMessage($msg, 'warning');

			$db = JFactory::getDbo();

			// Let's get the information of the update plugin
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__extensions'))
				->where($db->qn('folder').' = '.$db->quote('system'))
				->where($db->qn('element').' = '.$db->quote('akeebaupdatecheck'))
				->where($db->qn('type').' = '.$db->quote('plugin'))
				->order($db->qn('ordering').' ASC');
			$db->setQuery($query);
			$plugin = $db->loadObject();

			if (!is_object($plugin))
			{
				return;
			}

			// Otherwise, try to enable it and report false (so the user knows what he did wrong)
			$pluginObject = (object)array(
				'extension_id'	=> $plugin->extension_id,
				'enabled'		=> 0
			);

			try
			{
				$db->updateObject('#__extensions', $pluginObject, 'extension_id');
			}
			catch (Exception $e)
			{
			}

			F0FUtilsCacheCleaner::clearPluginsCache();
		}
	}
}