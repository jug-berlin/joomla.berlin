<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsControllerScan extends F0FController
{
	/**
	 * Apply hard-coded filters before rendering the Browse page
	 *
	 * @return bool
	 */
	protected function onBeforeBrowse()
	{
		$result = $this->checkACL('admintools.security');
		if ($result)
		{
			$limitstart = $this->input->getInt('limitstart', null);
			if (is_null($limitstart))
			{
				$total = $this->getThisModel()->getTotal();
				$limitstart = $this->getThisModel()->getState('limitstart', 0);
				if ($limitstart > $total)
				{
					$this->getThisModel()->limitstart(0);
				}
			}

			$this->getThisModel()
				->status('complete')
				->profile_id(1);

            // If the diff isn't enabled, let's automatically purge the table with file contents
            $params = JComponentHelper::getParams('com_admintools');
		}

		return $result;
	}

	protected function onAfterBrowse()
	{
		$this->getThisModel()->removeIncompleteScans();

		return true;
	}

	public function add()
	{
		JError::raiseError('403', JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));
	}

	public function edit()
	{
		JError::raiseError('403', JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));
	}

	public function save()
	{
		JError::raiseError('403', JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));
	}

	public function remove()
	{
		$this->getThisModel()->setIDsFromRequest();

		return parent::remove();
	}

	public function startscan()
	{
		if (!$this->checkACL('admintools.security'))
		{
			JError::raiseError('403', JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));
		}

		$this->input->set('layout', 'scan');
		$this->getThisView()->retarray = $this->getThisModel()->startScan();
		$this->getThisView()->setLayout('scan');
		$this->layout = 'scan';

		parent::display(false);
	}

	public function stepscan()
	{
		if (!$this->checkACL('admintools.security'))
		{
			JError::raiseError('403', JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));
		}

		$this->input->set('layout', 'scan');
		$this->getThisView()->retarray = $this->getThisModel()->stepScan();
		$this->getThisView()->setLayout('scan');
		$this->layout = 'scan';

		parent::display(false);
	}

    public function purge()
    {
        /** @var AdmintoolsModelScans $model */
        $model = $this->getThisModel();

        $type = null;

        if($model->purgeFilesCache())
        {
            $msg = JText::_('COM_ADMINTOOLS_MSG_SCANS_PURGE_COMPLETED');
        }
        else
        {
            $msg = JText::_('COM_ADMINTOOLS_MSG_SCANS_PURGE_ERROR');
            $type = 'error';
        }

        $this->setRedirect('index.php?option=com_admintools&view=scans', $msg, $type);
    }

	protected function onBeforeRemove()
	{
		return $this->checkACL('admintools.security');
	}
}