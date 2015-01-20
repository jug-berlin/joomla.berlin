<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

// Load framework base classes
JLoader::import('joomla.application.component.view');

class AdmintoolsViewNginxmaker extends F0FViewHtml
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
				$nginxconf = $model->makeHtaccess();

				$this->nginxconf = $nginxconf;

				$this->setLayout('plain');

				break;

			default:
				/** @var AdmintoolsModelNginxmaker $model */
				$model = $this->getModel();
				$config = $model->loadConfiguration();

				$this->nginxconf = $config;

				$this->loadHelper('servertech');
				$this->isSupported = AdmintoolsHelperServertech::isNginxSupported();
				break;
		}
	}
}