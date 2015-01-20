<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * The Control Panel controller class
 *
 */
class AkeebaControllerAlice extends F0FController
{
	public function ajax()
	{
		$model = $this->getThisModel();

		$model->setState('ajax', $this->input->get('ajax', '', 'cmd'));
		$model->setState('log', $this->input->get('log', '', 'cmd'));

		$ret_array = $model->runAnalysis();

		@ob_end_clean();
		header('Content-type: text/plain');
		echo '###' . json_encode($ret_array) . '###';
		flush();
		JFactory::getApplication()->close();
	}

	public function domains()
	{
		$return = array();
		$domains = AliceUtilScripting::getDomainChain();

		foreach ($domains as $domain)
		{
			$return[] = array($domain['domain'], $domain['name']);
		}

		@ob_end_clean();
		header('Content-type: text/plain');
		echo '###' . json_encode($return) . '###';
		flush();
		JFactory::getApplication()->close();
	}
}