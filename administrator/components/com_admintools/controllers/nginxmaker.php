<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsControllerNginxmaker extends F0FController
{
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->modelName = 'nginxmaker';
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

		/** @var AdmintoolsModelNginxmaker $model */
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

		$this->setRedirect('index.php?option=com_admintools&view=nginxmaker', JText::_('ATOOLS_LBL_NGINXMAKER_SAVED'));
	}

	public function apply()
	{
		/** @var AdmintoolsModelNginxmaker $model */
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
		$status = $model->writeNginXConf();

		if (!$status)
		{
			$this->setRedirect('index.php?option=com_admintools&view=nginxmaker', JText::_('ATOOLS_LBL_NGINXMAKER_NOTAPPLIED'), 'error');
		}
		else
		{
			$this->setRedirect('index.php?option=com_admintools&view=nginxmaker', JText::_('ATOOLS_LBL_NGINXMAKER_APPLIED'));
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
