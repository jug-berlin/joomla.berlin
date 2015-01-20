<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @since     3.2
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;

/**
 * Archive discovery view - HTML View
 */
class AkeebaViewDiscover extends F0FViewHtml
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

	public function onDiscover($tpl = null)
	{
		$media_folder = JUri::base() . '../media/com_akeeba/';

		$model = $this->getModel();

		$directory = $model->getState('directory', '');
		$this->setLayout('discover');

		$files = $model->getFiles();

		$this->files = $files;
		$this->directory = $directory;

		AkeebaHelperIncludes::addHelp('discover');

		return true;
	}

	public function onBrowse($tpl = null)
	{
		$media_folder = JUri::base() . '../media/com_akeeba/';

		$model = $this->getModel();

		$directory = $model->getState('directory', '');
		if (empty($directory))
		{
			$config = Factory::getConfiguration();
			$this->directory = $config->get('akeeba.basic.output_directory', '[DEFAULT_OUTPUT]');
		}
		else
		{
			$this->directory = '';
		}

		AkeebaHelperIncludes::addHelp('discover');

		return true;
	}
}