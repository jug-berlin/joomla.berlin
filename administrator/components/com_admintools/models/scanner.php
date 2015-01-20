<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsModelScanner extends F0FModel
{
	private $aeconfig;

	public function __construct($config = array())
	{
		parent::__construct($config);
		define('AKEEBAROOT', JPATH_ADMINISTRATOR . '/components/com_admintools/akeeba');
		define('AKEEBAENGINE', 1); // Enable Akeeba Engine
		define('AKEEBAPLATFORM', 'jfscan'); // Joomla! file scanner
		require_once JPATH_ADMINISTRATOR . '/components/com_admintools/akeeba/factory.php';
		AEPlatform::getInstance()->load_configuration(1);
		$this->aeconfig = AEFactory::getConfiguration();
	}

	private function getTextInputAsArray($input, $trim = '')
	{
		$result = array();
		foreach (explode("\n", $input) as $entry)
		{
			$entry = preg_replace('/\s+/', '', $entry);
			if (!empty($trim))
			{
				$entry = trim($entry, '/');
			}
			if (!empty($entry))
			{
				$result[] = $entry;
			}
		}

		return $result;
	}

	public function saveConfiguration()
	{
		$rawInput = $this->getState('rawinput', array());

		$newFileExtension = trim(F0FInput::getVar('fileextensions', '', $rawInput));
		$newExcludeFolders = trim(F0FInput::getVar('exludefolders', '', $rawInput));
		$newExcludeFiles = trim(F0FInput::getVar('exludefiles', '', $rawInput));
		$newMinExecTime = trim(F0FInput::getInt('mintime', '', $rawInput));
		$newMaxExecTime = trim(F0FInput::getInt('maxtime', '', $rawInput));
		$newRuntimeBias = trim(F0FInput::getInt('runtimebias', '', $rawInput));

		$protectedKeys = $this->aeconfig->getProtectedKeys();
		$this->aeconfig->resetProtectedKeys();

		$this->aeconfig->set('akeeba.basic.file_extensions', join('|',
			$this->getTextInputAsArray(
				$newFileExtension
			)));
		$this->aeconfig->set('akeeba.basic.exclude_folders', join('|',
			$this->getTextInputAsArray(
				$newExcludeFolders, '/'
			)));
		$this->aeconfig->set('akeeba.basic.exclude_files', join('|',
			$this->getTextInputAsArray(
				$newExcludeFiles, '/'
			)));
		$this->aeconfig->set('akeeba.tuning.min_exec_time', $newMinExecTime);
		$this->aeconfig->set('akeeba.tuning.max_exec_time', $newMaxExecTime);
		$this->aeconfig->set('akeeba.tuning.run_time_bias', $newRuntimeBias);

		AEPlatform::getInstance()->save_configuration();

		$this->aeconfig->setProtectedKeys($protectedKeys);
	}

	public function getFileExtensions()
	{
		return explode('|', $this->aeconfig->get('akeeba.basic.file_extensions', 'php|phps|php3|inc'));
	}

	public function getExcludeFolders()
	{
		return explode('|', $this->aeconfig->get('akeeba.basic.exclude_folders', ''));
	}

	public function getExcludeFiles()
	{
		return explode('|', $this->aeconfig->get('akeeba.basic.exclude_files', ''));
	}

	public function getMinExecTime()
	{
		return $this->aeconfig->get('akeeba.tuning.min_exec_time', 1000);
	}

	public function getMaxExecTime()
	{
		return $this->aeconfig->get('akeeba.tuning.max_exec_time', 5);
	}

	public function getRuntimeBias()
	{
		return $this->aeconfig->get('akeeba.tuning.run_time_bias', 75);
	}
}