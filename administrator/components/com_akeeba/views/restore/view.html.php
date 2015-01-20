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
 * Multiple databases definition View
 *
 */
class AkeebaViewRestore extends F0FViewHtml
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
		AkeebaStrapper::addJSfile('media://com_akeeba/js/encryption.js');

		$model = $this->getModel();
		$restorationstep = $model->getState('restorationstep', 0);
		if ($restorationstep == 1)
		{
			$password = $model->getState('password');
			$this->password = $password;
			$this->setLayout('restore');
		}
		else
		{
			$id = $model->getId();
			$ftpparams = $model->getFTPParams();
			$extractionmodes = $model->getExtractionModes();

			$this->id = $id;
			$this->ftpparams = $ftpparams;
			$this->extractionmodes = $extractionmodes;
		}

		// Add live help
		AkeebaHelperIncludes::addHelp('restore');

		return true;
	}
}