<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsControllerCpanel extends F0FController
{
	/**
	 * Overridden task dispatcher to whitelist specific tasks
	 *
	 * @param string $task The task to execute
	 *
	 * @return bool|null|void
	 */
	public function execute($task)
	{
		// Preload the model class of this view (we have a problem with the name, you know)
		$cpanelModel = $this->getModel('Cpanel', 'AdmintoolsModel');

		// We only allow specific tasks. If none matches, assume the user meant the "browse" tasl
		if (!in_array($task, array('login', 'updategeoip', 'updateinfo', 'fastcheck')))
		{
			$task = 'browse';
		}

		$this->task = $task;

		parent::execute($task);
	}

	public function onBeforeBrowse()
	{
		$result = parent::onBeforeBrowse();

		if ($result)
		{
			$view = $this->getThisView();
			$view->setModel($this->getThisModel(), true);

			// Upgrade the database schema if necessary
			$this->getThisModel()->checkAndFixDatabase();

			// Migrate user data if necessary
			$this->getThisModel()->autoMigrate();

			// Refresh the update site definitions if required. Also takes into account any change of the Download ID
			// in the Options.
			/** @var AdmintoolsModelUpdates $updateModel */
			$updateModel = F0FModel::getTmpInstance('Updates', 'AdmintoolsModel');
			$updateModel->refreshUpdateSite();

			// Is a Download ID needed but missing?
			$needDLID = $this->getThisModel()->needsDownloadID();
			$view->needsdlid = $needDLID;

			// Check the last installed version and show the post-setup page on Joomla! 3.1 or earlier
			if (!version_compare(JVERSION, '3.2.0', 'ge'))
			{
				$versionLast = null;

				if (file_exists(JPATH_COMPONENT_ADMINISTRATOR . '/admintools.lastversion.php'))
				{
					include_once JPATH_COMPONENT_ADMINISTRATOR . '/admintools.lastversion.php';

					if (defined('ADMINTOOLS_LASTVERSIONCHECK'))
					{
						$versionLast = ADMINTOOLS_LASTVERSIONCHECK;
					}
				}

				if (is_null($versionLast))
				{
					// FIX 2.1.13: Load the component parameters WITHOUT using JComponentHelper
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);

					$query->select(array($db->quoteName('params')))
						->from($db->quoteName('#__extensions'))
						->where($db->quoteName('type') . ' = ' . $db->Quote('component'))
						->where($db->quoteName('element') . ' = ' . $db->Quote('com_admintools'));
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

					$versionLast = $params->get('lastversion', '');
				}

				if (version_compare(ADMINTOOLS_VERSION, $versionLast, 'ne') || empty($versionLast))
				{
					$this->setRedirect('index.php?option=com_admintools&view=postsetup');

					return true;
				}
			}
		}

		return $result;
	}

	public function login()
	{
		$model = $this->getModel('Masterpw');
		$password = $this->input->getVar('userpw', '');
		$model->setUserPassword($password);

		$url = 'index.php?option=com_admintools';
		$this->setRedirect($url);
	}

	public function updategeoip()
	{
		if ($this->csrfProtection)
		{
			$this->_csrfProtection();
		}

		// Load the GeoIP library if it's not already loaded
		if (!class_exists('AkeebaGeoipProvider'))
		{
			if (@file_exists(JPATH_PLUGINS . '/system/akgeoip/lib/akgeoip.php'))
			{
				if (@include_once JPATH_PLUGINS . '/system/akgeoip/lib/vendor/autoload.php')
				{
					@include_once JPATH_PLUGINS . '/system/akgeoip/lib/akgeoip.php';
				}
			}
		}

		$geoip = new AkeebaGeoipProvider();
		$result = $geoip->updateDatabase();

		$url = 'index.php?option=com_admintools';

		if ($result === true)
		{
			$msg = JText::_('ATOOLS_GEOBLOCK_MSG_DOWNLOADEDGEOIPDATABASE');
			$this->setRedirect($url, $msg);
		}
		else
		{
			$this->setRedirect($url, $result, 'error');
		}
	}

	public function updateinfo()
	{
		/** @var AdmintoolsModelUpdates $updateModel */
		$updateModel = F0FModel::getTmpInstance('Updates', 'AdmintoolsModel');
		$updateInfo = (object)$updateModel->getUpdates();

		$result = '';

		if ($updateInfo->hasUpdate)
		{
			$strings = array(
				'header'  => JText::sprintf('COM_ADMINTOOLS_CPANEL_MSG_UPDATEFOUND', $updateInfo->version),
				'button'  => JText::sprintf('COM_ADMINTOOLS_CPANEL_MSG_UPDATENOW', $updateInfo->version),
				'infourl' => $updateInfo->infoURL,
				'infolbl' => JText::_('COM_ADMINTOOLS_CPANEL_MSG_MOREINFO'),
			);

			$result = <<<ENDRESULT
	<div class="alert alert-warning">
		<h3>
			<span class="icon icon-exclamation-sign glyphicon glyphicon-exclamation-sign"></span>
			{$strings['header']}
		</h3>
		<p>
			<a href="index.php?option=com_installer&view=update" class="btn btn-primary">
				{$strings['button']}
			</a>
			<a href="{$strings['infourl']}" target="_blank" class="btn btn-small btn-info">
				{$strings['infolbl']}
			</a>
		</p>
	</div>
ENDRESULT;
		}

		echo '###' . $result . '###';

		// Cut the execution short
		JFactory::getApplication()->close();
	}

	public function fastcheck()
	{
		/** @var AdmintoolsModelCpanels $model */
		$model = $this->getThisModel();

		$result = $model->fastCheckFiles();

		echo '###' . ($result ? 'true' : 'false') . '###';

		// Cut the execution short
		JFactory::getApplication()->close();
	}
}
