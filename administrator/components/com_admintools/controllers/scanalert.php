<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsControllerScanalert extends F0FController
{
	protected function onBeforeBrowse()
	{
		$result = $this->checkACL('admintools.security');
		if ($result)
		{
			if (
				($this->input->getCmd('format', 'html') == 'csv') ||
				($this->input->getCmd('layout', 'default') == 'print')
			)
			{
				$this->getThisModel()
					->savestate(0);
				$this->getThisModel()
					->setState('limit', 0);
				$this->getThisModel()
					->setState('limitstart', 0);
				$this->getThisView()->scan =
					F0FModel::getTmpInstance('Scans', 'AdmintoolsModel')
						->id($this->input->getInt('scan_id', 0))
						->getItem();
			}
		}

		return $result;
	}

	public function onBeforeEdit()
	{
		$result = $this->checkACL('admintools.security');
		if ($result)
		{
			// Get the componetn parameters
			$db = JFactory::getDbo();
			$sql = $db->getQuery(true)
				->select($db->qn('params'))
				->from($db->qn('#__extensions'))
				->where($db->qn('type') . ' = ' . $db->q('component'))
				->where($db->qn('element') . ' = ' . $db->q('com_admintools'));
			$db->setQuery($sql);
			$rawparams = $db->loadResult();
			$params = new JRegistry();
			$params->loadString($rawparams, 'JSON');

			$this->getThisView()->generateDiff = $params->get('scandiffs', false);

			// Look for Geshi
			JLoader::import('joomla.filesystem.file');
			$geshiPath = JPATH_PLUGINS . '/content/geshi/geshi/geshi.php';
			if (JFile::exists($geshiPath))
			{
				require_once $geshiPath;
			}
		}

		return $result;
	}

	public function onAfterCancel()
	{
		$item = $this->getThisModel()->getItem();
		$this->redirect .= '&scan_id=' . (int)$item->scan_id;

		return true;
	}

	public function onAfterSave()
	{
		$item = $this->getThisModel()->getItem();
		$this->redirect .= '&scan_id=' . (int)$item->scan_id;

		return true;
	}

	public function add()
	{
		JError::raiseError('403', JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));
	}

	public function delete()
	{
		JError::raiseError('403', JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));
	}

	public function onBeforePublish()
	{
		$customURL = $this->input->getString('returnurl', '');
		if (!$customURL)
		{
			$scan_id = $this->input->getCmd('scan_id', 0);
			$url = 'index.php?option=' . $this->component . '&view=' . F0FInflector::pluralize($this->view) . '&scan_id=' . $scan_id;
			$this->input->set('returnurl', base64_encode($url));
		}

		return $this->checkACL('admintools.security');
	}

	public function onBeforeUnpublish()
	{
		$customURL = $this->input->getString('returnurl', '');
		if (!$customURL)
		{
			$scan_id = $this->input->getCmd('scan_id', 0);
			$url = 'index.php?option=' . $this->component . '&view=' . F0FInflector::pluralize($this->view) . '&scan_id=' . $scan_id;
			$this->input->set('returnurl', base64_encode($url));
		}

		return $this->checkACL('admintools.security');
	}
}