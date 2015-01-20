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

use Akeeba\Engine\Platform;
use Akeeba\Engine\Factory;

class AkeebaControllerUpload extends AkeebaControllerDefault
{
	/**
	 * This controller does not support a default task, thank you.
	 */
	public function display($cachable = false, $urlparams = false, $tpl = null)
	{
		JError::raiseError(500, 'Invalid task');

		return false;
	}

	public function upload($cachable = false, $urlparams = false)
	{
		// Get the parameters from the URL
		$id = $this->getAndCheckId();
		$part = $this->input->get('part', 0, 'int');
		$frag = $this->input->get('frag', 0, 'int');

		// Check the backup stat ID
		if ($id === false)
		{
			$url = 'index.php?option=com_akeeba&view=upload&tmpl=component&task=cancelled&id=' . $id;
			$this->setRedirect($url, JText::_('AKEEBA_TRANSFER_ERR_INVALIDID'), 'error');

			return true;
		}

		// Set the model state
		/** @var AkeebaModelUploads $model */
		$model = $this->getThisModel();
		$model->setState('id', $id);
		$model->setState('part', $part);
		$model->setState('frag', $frag);

		// Try uploading
		$result = $model->upload();

		// Get the modified model state
		$id = $model->getState('id');
		$part = $model->getState('part');
		$frag = $model->getState('frag');
		$stat = $model->getState('stat');
		$remote_filename = $model->getState('remotename');

		// Push the state to the view. We assume we have to continue uploading. We only change that if we detect an
		// upload completion or error condition in the if-blocks further below.
		$view = $this->getThisView();

		$view->setLayout('uploading');
		$view->parts = $stat['multipart'];
		$view->part = $part;
		$view->frag = $frag;
		$view->id = $id;
		$view->done = 0;
		$view->error = 0;

		if (($part >= 0) && ($result === true))
		{
			// If we are told the upload finished successfully we can display the "done" page
			$view->setLayout('done');
			$view->done = 1;
			$view->error = 0;

			// Also reset the saved post-processing engine
			$session = JFactory::getSession();
			$session->set('postproc_engine', null, 'akeeba');
		}
		elseif ($result === false)
		{
			// If we have an error we have to display it and stop the upload
			$view->done = 0;
			$view->error = 1;
			$view->errorMessage = $model->getError();
			$view->setLayout('error');

			// Also reset the saved post-processing engine
			$session = JFactory::getSession();
			$session->set('postproc_engine', null, 'akeeba');
		}

		parent::display($cachable, $urlparams);
	}

	/**
	 * This task is called when we have to cancel the upload
	 *
	 * @param bool $cachable
	 * @param bool $urlparams
	 */
	public function cancelled($cachable = false, $urlparams = false)
	{
		$view = $this->getThisView();

		$view->setLayout('error');

		parent::display($cachable, $urlparams);
	}

	/**
	 * Start uploading
	 *
	 * @param bool $cachable
	 * @param bool $urlparams
	 */
	public function start($cachable = false, $urlparams = false)
	{
		$id = $this->getAndCheckId();

		// Check the backup stat ID
		if ($id === false)
		{
			$url = 'index.php?option=com_akeeba&view=upload&tmpl=component&task=cancelled&id=' . $id;
			$this->setRedirect($url, JText::_('AKEEBA_TRANSFER_ERR_INVALIDID'), 'error');

			return true;
		}

		// Start by resetting the saved post-processing engine
		$session = JFactory::getSession();
		$session->set('postproc_engine', null, 'akeeba');

		// Initialise the view
		/** @var AkeebaViewUpload $view */
		$view = $this->getThisView();

		$view->done = 0;
		$view->error = 0;

		$view->id = $id;
		$view->setLayout('default');

		parent::display($cachable, $urlparams);
	}

	/**
	 * Gets the stats record ID from the request and checks that it does exist
	 *
	 * @return bool|int False if an invalid ID is found, the numeric ID if it's valid
	 */
	private function getAndCheckId()
	{
		$id = $this->input->get('id', 0, 'int');

		if ($id <= 0)
		{
			return false;
		}

		$statObject = Platform::getInstance()->get_statistics($id);

		if (empty($statObject) || !is_array($statObject))
		{
			return false;
		}

		return $id;
	}
}