<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsControllerGeoblock extends F0FController
{
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->modelName = 'geoblock';
	}

	public function execute($task)
	{
		if (!in_array($task, array('save', 'cancel', 'downloaddat')))
		{
			$task = 'browse';
		}
		parent::execute($task);
	}

	public function save()
	{
		// CSRF prevention
		$this->_csrfProtection();

		$continents = $this->input->get('continent', array(), 'array', 2);
		if (empty($continents))
		{
			$continents = '';
		}
		else
		{
			$continents = array_keys($continents);
			$continents = implode(',', $continents);
		}

		$countries = $this->input->get('country', array(), 'array', 2);
		if (empty($countries))
		{
			$countries = '';
		}
		else
		{
			$countries = array_keys($countries);
			$countries = implode(',', $countries);
		}

		$model = $this->getThisModel();
		$config = array('countries' => $countries, 'continents' => $continents);
		$model->saveConfig($config);

		$option = $this->input->getCmd('option', 'com_foobar');
		$view = $this->input->getCmd('view', 'default');
		$textkey = 'ATOOLS_LBL_' . strtoupper($view) . '_SAVED';
		$url = 'index.php?option=' . $option . '&view=waf';
		$this->setRedirect($url, JText::_($textkey));
		$this->redirect();
	}

	public function cancel()
	{
		// Redirect to the display task
		$option = $this->input->getCmd('option', 'com_admintools');
		$url = 'index.php?option=' . $option . '&view=waf';
		$this->setRedirect($url);
		$this->redirect();
	}

	protected function onBeforeBrowse()
	{
		return $this->checkACL('admintools.security');
	}

	protected function onBeforeSave()
	{
		return $this->checkACL('admintools.security');
	}

	protected function onBeforeDownloaddat()
	{
		return $this->checkACL('admintools.security');
	}
}