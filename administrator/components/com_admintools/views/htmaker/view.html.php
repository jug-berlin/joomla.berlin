<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

// Load framework base classes
JLoader::import('joomla.application.component.view');

class AdmintoolsViewHtmaker extends F0FViewHtml
{
	public function display($tpl = null)
	{
		parent::display($tpl);
	}

	protected function onBrowse($tpl = null)
	{
		$task = $this->input->getCmd('task', 'browse');

		switch ($task)
		{
			case 'preview':
				$model = $this->getModel();
				$htaccess = $model->makeHtaccess();

				$this->htaccess = $htaccess;

				$this->setLayout('plain');

				break;

			default:
				/** @var AdmintoolsModelHtmaker $model */
				$model = $this->getModel();
				$config = $model->loadConfiguration();

				$this->htconfig = $config;

				$this->loadHelper('servertech');
				$this->isSupported = AdmintoolsHelperServertech::isHtaccessSupported();
				break;
		}
	}
}