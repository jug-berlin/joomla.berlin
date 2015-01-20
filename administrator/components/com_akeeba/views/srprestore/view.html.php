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

/**
 * Multiple databases definition View
 *
 */
class AkeebaViewSrprestore extends F0FViewHtml
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
		$model = $this->getModel();
		$step = $model->getState('restorestep', 0);
		if ($step == 1)
		{
			$this->setLayout('restore');
		}
		else
		{
			$model->validateRequest();
			$id = $model->getId();
			$ftpparams = $model->getFTPParams();
			$extractionmodes = $model->getExtractionModes();

			$this->id = $id;
			$this->ftpparams = $ftpparams;
			$this->extractionmodes = $extractionmodes;
			$this->info = $model->info;
		}

		// Add live help
		AkeebaHelperIncludes::addHelp('srprestore');

		return true;
	}
}