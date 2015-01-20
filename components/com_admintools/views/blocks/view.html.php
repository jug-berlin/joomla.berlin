<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

// Load framework base classes
JLoader::import('joomla.application.component.view');

class AdmintoolsViewBlocks extends F0FView
{
	public function display($tpl = null)
	{
		// Get the message
		if (interface_exists('JModel'))
		{
			$cparams = JModelLegacy::getInstance('Storage', 'AdmintoolsModel');
		}
		else
		{
			$cparams = JModel::getInstance('Storage', 'AdmintoolsModel');
		}
		$message = JFactory::getSession()->get('message', null, 'com_admintools');

		if (empty($message))
		{
			$customMessage = $cparams->getValue('custom403msg', '');
			if (!empty($customMessage))
			{
				$message = $customMessage;
			}
			else
			{
				$message = 'ADMINTOOLS_BLOCKED_MESSAGE';
			}
		}

		// Merge the default translation with the current translation
		$jlang = JFactory::getLanguage();
		// Front-end translation
		$jlang->load('plg_system_admintools', JPATH_ADMINISTRATOR, 'en-GB', true);
		$jlang->load('plg_system_admintools', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
		$jlang->load('plg_system_admintools', JPATH_ADMINISTRATOR, null, true);

		if ((JText::_('ADMINTOOLS_BLOCKED_MESSAGE') == 'ADMINTOOLS_BLOCKED_MESSAGE') && ($message == 'ADMINTOOLS_BLOCKED_MESSAGE'))
		{
			$message = "Access Denied";
		}
		else
		{
			$message = JText::_($message);
		}

		$this->message = $message;

		parent::display($tpl);

		JFactory::getApplication()->close();
	}
}