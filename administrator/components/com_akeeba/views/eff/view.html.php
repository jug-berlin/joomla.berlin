<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 *
 * @since     2.1
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\Engine\Platform;

/**
 * Extra Directories inclusion filter
 *
 */
class AkeebaViewEff extends F0FViewHtml
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
		$media_folder = JUri::base() . '../media/com_akeeba/';

		// Get the root URI for media files
		$this->mediadir = AkeebaHelperEscape::escapeJS($media_folder . 'theme/');

		// Get a JSON representation of the directories data
		$model = $this->getModel();
		$directories = $model->get_directories();
		$json = json_encode($directories);
		$this->json = $json;

		// Add live help
		AkeebaHelperIncludes::addHelp('eff');

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