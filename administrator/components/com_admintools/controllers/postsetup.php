<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsControllerPostsetup extends F0FController
{
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->modelName = 'postsetup';
	}

	public function execute($task)
	{
		if (!in_array($task, array('save')))
		{
			$task = 'browse';
		}
		parent::execute($task);
	}

	public function save()
	{
		// CSRF prevention
		$this->_csrfProtection();

		$enableAutoupdate = $this->input->getBool('autoupdate', 0);
		$enableAutojupdate = $this->input->getBool('autojupdate', 0);
		$acceptlicense = $this->input->getBool('acceptlicense', 0);
		$acceptsupport = $this->input->getBool('acceptsupport', 0);

		$db = JFactory::getDBO();

		if ($enableAutoupdate || $enableAutojupdate)
		{
			$query = $db->getQuery(true)
				->update($db->qn('#__extensions'))
				->set($db->qn('enabled') . ' = ' . $db->q('1'))
				->where($db->qn('element') . ' = ' . $db->q('oneclickaction'))
				->where($db->qn('folder') . ' = ' . $db->q('system'));
			$db->setQuery($query);
			$db->execute();
		}
		else
		{
			$query = $db->getQuery(true)
				->update($db->qn('#__extensions'))
				->set($db->qn('enabled') . ' = ' . $db->q('0'))
				->where($db->qn('element') . ' = ' . $db->q('oneclickaction'))
				->where($db->qn('folder') . ' = ' . $db->q('system'));
			$db->setQuery($query);
			$db->execute();
		}

		$query = $db->getQuery(true)
			->update($db->qn('#__extensions'))
			->set($db->qn('enabled') . ' = ' . $db->q('0'))
			->where($db->qn('element') . ' = ' . $db->q('atoolsupdatecheck'))
			->where($db->qn('folder') . ' = ' . $db->q('system'));
		$db->setQuery($query);
		$db->execute();

		if ($enableAutojupdate)
		{
			$query = $db->getQuery(true)
				->update($db->qn('#__extensions'))
				->set($db->qn('enabled') . ' = ' . $db->q('1'))
				->where($db->qn('element') . ' = ' . $db->q('atoolsjupdatecheck'))
				->where($db->qn('folder') . ' = ' . $db->q('system'));
			$db->setQuery($query);
			$db->execute();
		}
		else
		{
			$query = $db->getQuery(true)
				->update($db->qn('#__extensions'))
				->set($db->qn('enabled') . ' = ' . $db->q('0'))
				->where($db->qn('element') . ' = ' . $db->q('atoolsjupdatecheck'))
				->where($db->qn('folder') . ' = ' . $db->q('system'));
			$db->setQuery($query);
			$db->execute();
		}

		// Load the component parameters. DO NOT USE JCOMPONENTHELPER!
		$query = $db->getQuery(true)
			->select(array(
				$db->qn('params')
			))
			->from($db->qn('#__extensions'))
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->where($db->qn('element') . ' = ' . $db->q('com_admintools'));
		$db->setQuery($query);
		$rawparams = $db->loadResult();
		$params = new JRegistry();
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$params->loadString($rawparams, 'JSON');
		}
		else
		{
			$params->loadJSON($rawparams);
		}

		// Apply htmaker folders fix.
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$has240Fix = $params->get('htmaker_folders_fix_at240', '0');
		}
		else
		{
			$has240Fix = $params->getValue('htmaker_folders_fix_at240', '0');
		}
		if (!$has240Fix)
		{
			$htmaker = $this->getModel('htmaker');
			$config = $htmaker->loadConfiguration();
			$isConfigChanged = false;
			$jUpdateRestore = 'administrator/components/com_joomlaupdate/restore.php';
			if (!in_array($jUpdateRestore, $config->exceptionfiles))
			{
				array_push($config->exceptionfiles, $jUpdateRestore);
				$isConfigChanged = true;
			}
			$fontDir = 'media/jui/fonts';
			if (!in_array($fontDir, $config->fepexdirs))
			{
				array_push($config->fepexdirs, $fontDir);
				$isConfigChanged = true;
			}
			if ($isConfigChanged)
			{
				$htmaker->saveConfiguration($config, true);
			}
			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				$params->set('htmaker_folders_fix_at240', '1');
			}
			else
			{
				$params->setValue('htmaker_folders_fix_at240', '1');
			}
		}

		// Update last version check.
		if ($acceptlicense && $acceptsupport)
		{
			$version = ADMINTOOLS_VERSION;
		}
		else
		{
			$version = '0.0.0';
		}
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$params->set('lastversion', $version);
			$params->set('acceptlicense', $acceptlicense);
			$params->set('acceptsupport', $acceptsupport);
		}
		else
		{
			$params->setValue('lastversion', $version);
			$params->setValue('acceptlicense', $acceptlicense);
			$params->setValue('acceptsupport', $acceptsupport);
		}

		// Joomla! 1.6
		$data = $params->toString('JSON');
		$sql = $db->getQuery(true)
			->update($db->qn('#__extensions'))
			->set($db->qn('params') . ' = ' . $db->q($data))
			->where($db->qn('element') . ' = ' . $db->q('com_admintools'))
			->where($db->qn('type') . ' = ' . $db->q('component'));
		$db->setQuery($sql);
		$db->execute();

		// Even better, create the "admintools.lastversion.php" file with this information
		$fileData = "<" . "?php\ndefined('_JEXEC') or die;\ndefine('ADMINTOOLS_LASTVERSIONCHECK','" .
			$version . "');";
		JLoader::import('joomla.filesystem.file');
		$fileName = JPATH_COMPONENT_ADMINISTRATOR . '/admintools.lastversion.php';
		JFile::write($fileName, $fileData);

		$url = 'index.php?option=com_admintools&view=cpanel';

		// Reset the Joomla! plugins cache
		F0FUtilsCacheCleaner::clearPluginsCache();

		if (!$acceptlicense)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_ADMINTOOLS_POSTSETUP_ERR_ACCEPTLICENSE'), 'error');
			$url = 'index.php?option=com_admintools&view=postsetup';
		}
		if (!$acceptsupport)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_ADMINTOOLS_POSTSETUP_ERR_ACCEPTSUPPORT'), 'error');
			$url = 'index.php?option=com_admintools&view=postsetup';
		}

		JFactory::getApplication()->redirect($url);
	}
}