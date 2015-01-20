<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 *
 * @since     3.0
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * Integrated restoration
 */
class AkeebaControllerRestore extends AkeebaControllerDefault
{
	public function execute($task)
	{
		if (!in_array($task, array('start', 'ajax')))
		{
			$task = 'browse';
		}
		parent::execute($task);
	}

	public function browse($cachable = false, $urlparams = false)
	{
		$model = $this->getThisModel();
		$message = $model->validateRequest();
		if ($message !== true)
		{
			$this->setRedirect('index.php?option=com_akeeba&view=buadmin', $message, 'error');
			$this->redirect();

			return true;
		}

		$model->setState('restorationstep', 0);

		parent::display($cachable, $urlparams);
	}

	function start($cachable = false, $urlparams = false)
	{
		$model = $this->getThisModel();
		$model->setState('restorationstep', 1);

		$message = $model->validateRequest();
		if ($message !== true)
		{
			$this->setRedirect('index.php?option=com_akeeba&view=buadmin', $message, 'error');
			$this->redirect();

			return true;
		}

		$model->setState('jps_key', $this->input->get('jps_key', '', 'cmd'));
		$model->setState('procengine', $this->input->get('procengine', 'direct', 'cmd'));
		$model->setState('ftp_host', $this->input->get('ftp_host', '', 'none', 2));
		$model->setState('ftp_port', $this->input->get('ftp_port', 21, 'int'));
		$model->setState('ftp_user', $this->input->get('ftp_user', '', 'none', 2));
		$model->setState('ftp_pass', $this->input->get('ftp_pass', '', 'none', 2));
		$model->setState('ftp_root', $this->input->get('ftp_root', '', 'none', 2));
		$model->setState('tmp_path', $this->input->get('tmp_path', '', 'none', 2));
		$model->setState('ftp_ssl', $this->input->get('usessl', 'false', 'cmd') == 'true');
		$model->setState('ftp_pasv', $this->input->get('passive', 'true', 'cmd') == 'true');

		$status = $model->createRestorationINI();
		if ($status === false)
		{
			$this->setRedirect('index.php?option=com_akeeba&view=buadmin', JText::_('RESTORE_ERROR_CANT_WRITE'), 'error');
			$this->redirect();

			return true;
		}

		parent::display($cachable, $urlparams);
	}

	function ajax($cachable = false, $urlparams = false)
	{
		$ajax = $this->input->get('ajax', '', 'cmd');
		$model = $this->getThisModel();
		$model->setState('ajax', $ajax);

		$ret = $model->doAjax();

		@ob_end_clean();
		echo '###' . json_encode($ret) . '###';
		flush();
		JFactory::getApplication()->close();
	}
}