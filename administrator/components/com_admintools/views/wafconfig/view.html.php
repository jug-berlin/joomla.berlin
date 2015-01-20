<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsViewWafconfig extends F0FViewHtml
{
	protected function onBrowse($tpl = null)
	{
		// Set the toolbar title
		/** @var AdmintoolsModelWafconfig $model */
		$model = $this->getModel();
		$config = $model->getConfig();

		// I'm converting these two fields only here,
		// since in the whole component they are handled as a comma-separated list
		$config['reasons_nolog'] = explode(',', $config['reasons_nolog']);
		$config['reasons_noemail'] = explode(',', $config['reasons_noemail']);

		$this->wafconfig = $config;

		JLoader::import('joomla.application.component.helper'); // Joomla! 2.5
		JLoader::import('cms.component.helper'); // Joomla! 3.x+

		$params = JComponentHelper::getParams('com_admintools', false);
		$this->longConfig = $params->get('longconfigpage', 0);
	}
}