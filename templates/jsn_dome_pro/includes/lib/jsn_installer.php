<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * @modified   JoomlaShine.com Team
 * @version   $Id: jsn_installer.php 17002 2012-10-13 09:39:19Z tuyetvt $
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.installer.installer');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
class JSNInstaller extends JInstaller
{
	public function __construct()
	{
		parent::__construct();
	}

	public static function getInstance()
	{
		static $instance;

		if (!isset ($instance))
		{
			$instance = new JSNInstaller();
		}
		return $instance;
	}

	public function setAdapter($name, &$adapter = null, $options = Array())
	{
		if (!is_object($adapter))
		{
			include_once dirname(__FILE__). DIRECTORY_SEPARATOR .'adapters'. DIRECTORY_SEPARATOR .strtolower($name).'.php';
			$class = 'JSNInstaller'.ucfirst($name);
			if (!class_exists($class))
			{
				return false;
			}
			$adapter = new $class($this, $this->_db, $options);
		}
		$this->_adapters[$name] = &$adapter;
		return true;
	}

	public function setupInstall()
	{
		// We need to find the installation manifest file
		if (!$this->findManifest()) {
			return false;
		}

		// Load the adapter(s) for the install manifest
		$type = (string)$this->manifest->attributes()->type;

		if($type != 'template')
		{
			$this->abort();
			return false;
		}
		// Lazy load the adapter
		if (!isset($this->_adapters[$type]) || !is_object($this->_adapters[$type])) {
			if (!$this->setAdapter($type)) {
				return false;
			}
		}

		return true;
	}

	public function install($path=null, $new_manifest = null, $deleted_files = array())
	{
		if ($path && JFolder::exists($path))
		{
			$this->setPath('source', $path);
		}
		else
		{
			return false;
		}

		if (!$this->setupInstall())
		{
			return false;
		}

		$root		= $this->manifest;
		$type		= (string) $root->attributes()->type;

		if (is_object($this->_adapters[$type]))
		{
			return $this->_adapters[$type]->install($new_manifest, $deleted_files);
		}
		return false;
	}

	public function parseFiles($element, $cid=0, $oldFiles=null, $oldMD5=null)
	{
		// Get the array of file nodes to process; we checked whether this had children above.
		if ( ! $element || ! count($element->children())) {
			// Either the tag does not exist or has no children (hence no files to process) therefore we return zero files processed.
			return 0;
		}

		// Initialise variables.
		$copyfiles = array ();

		// Get the client info
		jimport('joomla.application.helper');
		$client = JApplicationHelper::getClientInfo($cid);

		/*
		 * Here we set the folder we are going to remove the files from.
		 */
		if ($client) {
			$pathname = 'extension_'.$client->name;
			$destination = $this->getPath($pathname);
		}
		else {
			$pathname = 'extension_root';
			$destination = $this->getPath($pathname);
		}

		/*
		 * Here we set the folder we are going to copy the files from.
		 *
		 * Does the element have a folder attribute?
		 *
		 * If so this indicates that the files are in a subdirectory of the source
		 * folder and we should append the folder attribute to the source path when
		 * copying files.
		 */
		$folder = (string)$element->attributes()->folder;

		if ($folder && file_exists($this->getPath('source') . '/' . $folder)) {
			$source = $this->getPath('source') . '/' . $folder;
		}
		else {
			$source = $this->getPath('source');
		}

		//Work out what files have been deleted
		if (!is_null($oldFiles))
		{
			foreach ($oldFiles as $value)
			{
				$deleted_file = $destination. DIRECTORY_SEPARATOR .str_replace('/', DIRECTORY_SEPARATOR, $value);
				if (JFile::exists($deleted_file))
				{
					JFile::delete($deleted_file);
				}
			}
		}
		// Copy the MD5SUMS file if it exists
		if (file_exists($source . '/MD5SUMS')) {
			$path['src'] = $source . '/MD5SUMS';
			$path['dest'] = $destination . '/MD5SUMS';
			$path['type'] = 'file';
			$copyfiles[] = $path;
		}

		// Process each file in the $files array (children of $tagName).

		foreach ($element->children() as $file)
		{
			$path['src']	= $source . '/' . $file;
			$path['dest']	= $destination . '/' . $file;

			// Is this path a file or folder?
			$path['type']	= ($file->getName() == 'folder') ? 'folder' : 'file';

			/*
			 * Before we can add a file to the copyfiles array we need to ensure
			 * that the folder we are copying our file to exits and if it doesn't,
			 * we need to create it.
			 */

			if (basename($path['dest']) != $path['dest']) {
				$newdir = dirname($path['dest']);

				if (!JFolder::create($newdir)) {
					JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_CREATE_DIRECTORY', $newdir));
					return false;
				}
			}

			// Add the file to the copyfiles array
			$copyfiles[] = $path;
		}
		return $this->copyFiles($copyfiles);
	}
}