<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsControllerScanner extends F0FController
{
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->modelName = 'scanner';
	}

	public function execute($task)
	{
		if (!in_array($task, array('save', 'apply')))
		{
			$task = 'browse';
		}
		parent::execute($task);
	}

	/**
	 * Handle the apply task which saves settings and shows the editor again
	 *
	 */
	public function apply()
	{
		// CSRF prevention
		if ($this->csrfProtection)
		{
			$this->_csrfProtection();
		}

		$model = $this->getThisModel();
		$model->setState('rawinput', $this->input);
		$model->saveConfiguration();

		$this->setRedirect(JURI::base() . 'index.php?option=com_admintools&view=scanner', JText::_('ATOOLS_SCANNER_CONFIG_SAVE_OK'));
	}

	/**
	 * Handle the save task which saves settings and returns to the cpanel
	 *
	 */
	public function save()
	{
		$this->apply();
		$this->setRedirect(JURI::base() . 'index.php?option=com_admintools&view=scans', JText::_('ATOOLS_SCANNER_CONFIG_SAVE_OK'));
	}

	protected function onBeforeBrowse()
	{
		return $this->checkACL('admintools.security');
	}

	protected function onBeforeApply()
	{
		return $this->checkACL('admintools.security');
	}

	protected function onBeforeSave()
	{
		return $this->checkACL('admintools.security');
	}
}