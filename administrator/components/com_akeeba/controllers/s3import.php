<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @since     3.4
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * S3 Import view - Controller
 */
class AkeebaControllerS3import extends AkeebaControllerDefault
{
	public function execute($task)
	{
		if ($task != 'dltoserver')
		{
			$task = 'browse';
		}
		parent::execute($task);
	}

	public function browse($cachable = false, $urlparams = false)
	{
		$s3bucket = $this->input->get('s3bucket', null, 'none', 2);

		$model = $this->getThisModel();
		if ($s3bucket)
		{
			$model->setState('s3bucket', $s3bucket);
		}
		$model->getS3Credentials();
		$model->setS3Credentials(
			$model->getState('s3access'), $model->getState('s3secret')
		);

		parent::display($cachable, $urlparams);
	}

	/**
	 * Fetches a complete backup set from a remote storage location to the local (server)
	 * storage so that the user can download or restore it.
	 */
	public function dltoserver($cachable = false, $urlparams = false)
	{
		$s3bucket = $this->input->get('s3bucket', null, 'none', 2);

		// Get the parameters
		$model = $this->getThisModel();
		if ($s3bucket)
		{
			$model->setState('s3bucket', $s3bucket);
		}
		$model->getS3Credentials();
		$model->setS3Credentials(
			$model->getState('s3access'), $model->getState('s3secret')
		);

		// Set up the model's state
		$part = $this->input->getInt('part', -999);
		if ($part >= -1)
		{
			JFactory::getApplication()->setUserState('com_akeeba.s3import.part', $part);
		}
		$frag = $this->input->getInt('frag', -999);
		if ($frag >= -1)
		{
			JFactory::getApplication()->setUserState('com_akeeba.s3import.frag', $frag);
		}
		$step = $this->input->getInt('step', -999);
		if ($step >= -1)
		{
			JFactory::getApplication()->setUserState('com_akeeba.s3import.step', $step);
		}

		$result = $model->downloadToServer();

		if ($result === true)
		{
			// Part(s) downloaded successfully. Render the view.
			parent::display();
		}
		elseif ($result === false)
		{
			// Part did not download. Redirect to initial page with an error.
			$this->setRedirect('index.php?option=com_akeeba&view=s3import', $model->getError(), 'error');
		}
		else
		{
			// All done. Redirect to intial page with a success message.
			$this->setRedirect('index.php?option=com_akeeba&view=s3import', JText::_('S3IMPORT_MSG_IMPORTCOMPLETE'));
		}
	}
}