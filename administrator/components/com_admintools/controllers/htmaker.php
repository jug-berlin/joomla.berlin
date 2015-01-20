<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsControllerHtmaker extends F0FController
{
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->modelName = 'htmaker';
	}

	public function execute($task)
	{
		if (!in_array($task, array('save', 'apply')))
		{
			$task = 'browse';
		}
		parent::execute($task);
	}

	public function save()
	{
		// CSRF prevention
		$this->_csrfProtection();

		$model = $this->getThisModel();
		if (is_array($this->input))
		{
			$data = $this->input;
		}
		elseif ($this->input instanceof F0FInput)
		{
			$data = $this->input->getData();
		}
		else
		{
			$data = JRequest::get('POST', 2);
		}
		$model->saveConfiguration($data);

		$this->setRedirect('index.php?option=com_admintools&view=htmaker', JText::_('ATOOLS_LBL_HTMAKER_SAVED'));
	}

	public function apply()
	{
		$model = $this->getThisModel();
		if (is_array($this->input))
		{
			$data = $this->input;
		}
		elseif ($this->input instanceof F0FInput)
		{
			$data = $this->input->getData();
		}
		else
		{
			$data = JRequest::get('POST', 2);
		}
		$model->saveConfiguration($data);
		$status = $model->writeHtaccess();
		if (!$status)
		{
			$this->setRedirect('index.php?option=com_admintools&view=htmaker', JText::_('ATOOLS_LBL_HTMAKER_NOTAPPLIED'), 'error');
		}
		else
		{
			$this->setRedirect('index.php?option=com_admintools&view=htmaker', JText::_('ATOOLS_LBL_HTMAKER_APPLIED'));
		}
	}

	protected function onBeforeBrowse()
	{
		return $this->checkACL('admintools.security');
	}

	protected function onBeforeSave()
	{
		return $this->checkACL('admintools.security');
	}

	protected function onBeforeApply()
	{
		return $this->checkACL('admintools.security');
	}
}
