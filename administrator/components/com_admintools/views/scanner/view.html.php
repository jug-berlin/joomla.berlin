<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2009-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @since     1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsViewScanner extends F0FViewHtml
{
	protected function onBrowse($tpl = null)
	{
		$model = $this->getModel();
		$this->fileExtensions = $model->getFileExtensions();
		$this->excludeFolders = $model->getExcludeFolders();
		$this->excludeFiles = $model->getExcludeFiles();
		$this->minExecTime = $model->getMinExecTime();
		$this->maxExecTime = $model->getMaxExecTime();
		$this->runtimeBias = $model->getRuntimeBias();

		return true;
	}
}