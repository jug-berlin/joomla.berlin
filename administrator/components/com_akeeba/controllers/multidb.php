<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 *
 * @since     1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * Multiple databases definition controller class
 *
 */
class AkeebaControllerMultidb extends AkeebaControllerDefault
{
	public function execute($task)
	{
		if ($task != 'ajax')
		{
			$task = 'browse';
		}

		parent::execute($task);
	}

	public function onBrowse($cachable = false, $urlparams = false)
	{
		parent::display($cachable, $urlparams);
	}

	/**
	 * AJAX proxy.
	 */
	public function ajax()
	{
		// Parse the JSON data and reset the action query param to the resulting array
		// NOTE: Since the db password can be in the form pa<sswo>rd, if it's passed through the
		// getVar() filters, it will get messed up. Therefore, I need to access the raw data.
		$action_json = $this->input->get('action', '', 'none', 2);
		$action = json_decode($action_json, true);

		$model = $this->getThisModel();
		$model->setState('action', $action);

		$ret = $model->doAjax();
		@ob_end_clean();
		echo '###' . json_encode($ret) . '###';
		flush();
		JFactory::getApplication()->close();
	}
}