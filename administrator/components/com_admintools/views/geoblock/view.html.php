<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsViewGeoblock extends F0FViewHtml
{
	protected function onBrowse($tpl = null)
	{
		$model = $this->getModel();
		$model->getConfig();

		$this->countries = $model->getCountries();
		$this->continents = $model->getContinents();
	}
}