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

use Akeeba\Engine\Platform;

/**
 * Regular expression based db filters management View
 *
 */
class AkeebaViewRegexdbfilter extends F0FViewHtml
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

		// Get a JSON representation of the available roots
		$model = $this->getModel();
		$root_info = $model->get_roots();
		$roots = array();
		if (!empty($root_info))
		{
			// Loop all dir definitions
			foreach ($root_info as $def)
			{
				$roots[] = $def->value;
				$options[] = JHTML::_('select.option', $def->value, $def->text);
			}
		}
		$site_root = '[SITEDB]';
		$attribs = 'onchange="akeeba_active_root_changed();"';
		$this->root_select = JHTML::_('select.genericlist', $options, 'root', $attribs, 'value', 'text', $site_root, 'active_root');
		$this->roots = $roots;

		$tpl = null;

		// Get a JSON representation of the directory data
		$model = $this->getModel();
		$json = json_encode($model->get_regex_filters($site_root));
		$this->json = $json;

		// Add live help
		AkeebaHelperIncludes::addHelp('regexdbfilter');

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