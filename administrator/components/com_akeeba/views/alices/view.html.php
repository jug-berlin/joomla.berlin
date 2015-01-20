<?php

defined('_JEXEC') or die;

use Akeeba\Engine\Platform;

class AkeebaViewAlices extends F0FViewHtml
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

	public function onBrowse($tpl = null)
	{
		// Add live help
		//AkeebaHelperIncludes::addHelp('alice');

		// Get a list of log names
		$model = $this->getModel();
		$this->logs = F0FModel::getTmpInstance('Logs', 'AkeebaModel')
			->getLogList();

		$log = $model->getState('log');
		if (empty($log))
		{
			$log = null;
		}
		$this->log = $log;

		// Get profile ID
		$profileid = Platform::getInstance()->get_active_profile();
		$this->profileid = $profileid;

		// Get profile name
		$pmodel = F0FModel::getAnInstance('Profiles', 'AkeebaModel');
		$pmodel->setId($profileid);
		$profile_data = $pmodel->getItem();
		$this->profilename = $profile_data->description;

		return true;
	}
}