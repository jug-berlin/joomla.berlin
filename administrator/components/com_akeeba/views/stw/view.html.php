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

use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;

/**
 * Akeeba Backup Configuration Wizard view class
 *
 */
class AkeebaViewStw extends F0FViewHtml
{
	/**
	 * Modified constructor to enable loading layouts from the plug-ins folder
	 *
	 * @param $config
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$tmpl_path = dirname(__FILE__) . '/tmpl';
		$this->addTemplatePath($tmpl_path);
	}

	public function onDisplay($tpl = null)
	{
		$aeconfig = Factory::getConfiguration();

		// Add live help
		AkeebaHelperIncludes::addHelp('stw');

		$model = $this->getModel();
		$step = $model->getState('stwstep', 1);

		switch ($step)
		{
			case 1:
			default:
				$cpanelmodel = F0FModel::getTmpInstance('Cpanels', 'AkeebaModel');
				$this->profilelist = $cpanelmodel->getProfilesList();
				$this->stw_profile_id = $model->getSTWProfileID();
				break;

			case 2:
				$this->opts = $model->getTransferSettings();
				break;

			case 3:
				break;
		}

		$this->setLayout('step' . $step);

		return true;
	}
}