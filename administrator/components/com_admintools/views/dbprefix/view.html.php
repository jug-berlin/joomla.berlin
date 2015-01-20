<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsViewDbprefix extends F0FViewHtml
{
	protected function onBrowse($tpl = null)
	{
		$model = $this->getModel();
		$this->isDefaultPrefix = $model->isDefaultPrefix();
		$this->currentPrefix = $model->getCurrentPrefix();
		$this->newPrefix = $model->getRandomPrefix(4);

		return true;
	}
}