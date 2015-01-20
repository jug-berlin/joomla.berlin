<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 *
 * @since     3.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class AkeebaControllerStw extends AkeebaControllerDefault
{
	public function execute($task)
	{
		if (!in_array($task, array('step1', 'step2', 'step3')))
		{
			$task = 'step1';
		}

		parent::execute($task);
	}

	/**
	 * Step 1 - select profile
	 *
	 * @param type $cachable
	 */
	public function step1($cachable = false, $urlparams = false)
	{
		$model = $this->getThisModel();
		$model->setState('stwstep', 1);
		parent::display($cachable, $urlparams);
	}

	/**
	 * Applies the profile creation preferences and displays the transfer setup
	 * page.
	 *
	 * @return void
	 */
	public function step2($cachable = false, $urlparams = false)
	{
		$model = $this->getThisModel();
		$model->setState('stwstep', 2);

		$method = $this->input->get('method', 'none', 'cmd');
		$oldprofile = $this->input->get('oldprofile', 0, 'int');

		$model->setState('method', $method);
		$model->setState('oldprofile', $oldprofile);

		$result = $model->makeOrUpdateProfile();

		if ($result == false)
		{
			$url = 'index.php?option=com_akeeba&view=stw';
			$this->setRedirect($url, JText::_('STW_PROFILE_ERR_COULDNOTCREATESTWPROFILE'), 'error');

			return true;
		}

		parent::display($cachable, $urlparams);
	}

	/**
	 * Apply the site transfer settings, test the connection, upload a test file
	 * and show the last step's page.
	 */
	public function step3($cachable = false, $urlparams = false)
	{
		$model = $this->getThisModel();
		$model->setState('stwstep', 3);

		$model->setState('method', $this->input->get('method', 'ftp', 'cmd'));
		$model->setState('hostname', $this->input->get('hostname', '', 'none', 2));
		$model->setState('port', $this->input->get('port', '', 'int'));
		$model->setState('username', $this->input->get('username', '', 'none', 2));
		$model->setState('password', $this->input->get('password', '', 'none', 2));
		$model->setState('directory', $this->input->get('directory', '', 'none', 2));
		$model->setState('passive', $this->input->get('passive', false, 'bool'));
		$model->setState('livesite', $this->input->get('livesite', '', 'none', 2));
		$result = $model->applyTransferSettings();

		if ($result != true)
		{
			$url = 'index.php?option=com_akeeba&view=stw&task=step2&method=none';
			$this->setRedirect($url, $result, 'error');

			return true;
		}

		parent::display($cachable, $urlparams);
	}
}