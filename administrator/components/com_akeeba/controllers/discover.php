<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @since     3.2
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * Archive discovery view - Controller
 */
class AkeebaControllerDiscover extends AkeebaControllerDefault
{
	public function execute($task)
	{
		if (!in_array($task, array('discover', 'import')))
		{
			$task = 'browse';
		}
		parent::execute($task);
	}

	/**
	 * Discovers JPA, JPS and ZIP files in the selected profile's directory and
	 * lets you select them for inclusion in the import process.
	 */
	public function discover()
	{
		// CSRF prevention
		if (!$this->csrfProtection)
		{
			$this->_csrfProtection();
		}

		$directory = $this->input->get('directory', '', 'string');

		if (empty($directory))
		{
			$url = 'index.php?option=com_akeeba&view=discover';
			$msg = JText::_('DISCOVER_ERROR_NODIRECTORY');
			$this->setRedirect($url, $msg, 'error');

			return true;
		}

		$model = $this->getThisModel();
		$model->setState('directory', $directory);

		parent::display();
	}

	/**
	 * Performs the actual import
	 */
	public function import()
	{
		// CSRF prevention
		if (!$this->csrfProtection)
		{
			$this->_csrfProtection();
		}

		$directory = $this->input->get('directory', '', 'string');
		$files = $this->input->get('files', array(), 'array');

		if (empty($files))
		{
			$url = 'index.php?option=com_akeeba&view=discover';
			$msg = JText::_('DISCOVER_ERROR_NOFILESSELECTED');
			$this->setRedirect($url, $msg, 'error');

			return true;
		}

		$model = $this->getThisModel();
		$model->setState('directory', $directory);
		foreach ($files as $file)
		{
			$model->import($file);
		}
		$url = 'index.php?option=com_akeeba&view=buadmin';
		$msg = JText::_('DISCOVER_LABEL_IMPORTDONE');
		$this->setRedirect($url, $msg);
	}
}