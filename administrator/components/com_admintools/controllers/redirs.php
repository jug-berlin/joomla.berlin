<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsControllerRedirs extends F0FController
{
	public function copy()
	{
		// CSRF prevention
		$this->_csrfProtection();

		$model = $this->getThisModel();
		$model->setIDsFromRequest();
		$id = $model->getId();

		$item = $model->getItem();
		$key = $item->getKeyName();
		if ($item->$key == $id)
		{
			$item->id = 0;
			$item->published = 0;
		}
		$status = $model->save($item);

		// redirect
		$option = $this->input->getCmd('option', 'com_admintools');
		$view = $this->input->getCmd('view', 'default');
		$url = 'index.php?option=' . $option . '&view=' . $view;
		if (!$status)
		{
			$this->setRedirect($url, $model->getError(), 'error');
		}
		else
		{
			$this->setRedirect($url);
		}
		$this->redirect();
	}

	public function applypreference()
	{
		$newState = $this->input->getInt('urlredirection', 1);
		$model = $this->getThisModel();
		$model->setRedirectionState($newState);

		$option = $this->input->getCmd('option', 'com_admintools');
		$view = $this->input->getCmd('view', 'default');
		$url = 'index.php?option=' . $option . '&view=' . $view;
		$this->setRedirect($url, JText::_('ATOOLS_LBL_REDIRS_PREFERENCE_SAVED'));

		$this->redirect();
	}

	protected function onBeforeBrowse()
	{
		return $this->checkACL('admintools.maintenance');
	}

	protected function onBeforeApply()
	{
		return $this->checkACL('admintools.maintenance');
	}

	protected function onBeforeSave()
	{
		return $this->checkACL('admintools.maintenance');
	}

	protected function onBeforePublish()
	{
		return $this->checkACL('admintools.maintenance');
	}

	protected function onBeforeUnpublish()
	{
		return $this->checkACL('admintools.maintenance');
	}

	protected function onBeforeRemove()
	{
		return $this->checkACL('admintools.maintenance');
	}

	protected function onBeforeSavenew()
	{
		return $this->checkACL('admintools.maintenance');
	}

	protected function onBeforeCopy()
	{
		return $this->checkACL('admintools.maintenance');
	}

	protected function onBeforeApplypreference()
	{
		return $this->checkACL('admintools.maintenance');
	}
}
