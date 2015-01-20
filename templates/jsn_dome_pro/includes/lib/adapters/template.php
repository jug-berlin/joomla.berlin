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
 * @version   $Id: template.php 17121 2012-10-17 03:47:09Z tuyetvt $
 */

defined('_JEXEC') or die('Restricted access');
require_once JPATH_LIBRARIES. DIRECTORY_SEPARATOR .'joomla'. DIRECTORY_SEPARATOR .'installer'. DIRECTORY_SEPARATOR .'adapters'. DIRECTORY_SEPARATOR .'template.php';

class JSNInstallerTemplate extends JInstallerTemplate
{
	/**
	 * Custom install method
	 *
	 * @return  boolean  True on success
	 * @since   11.1
	 */
	public function install($new_manifest = null, $deleted_files = array())
	{
		$lang = JFactory::getLanguage();
		$xml = $this->parent->getManifest();

		// Get the client application target
		if ($cname = (string)$xml->attributes()->client) {
			// Attempt to map the client to a base path
			jimport('joomla.application.helper');
			$client = JApplicationHelper::getClientInfo($cname, true);
			if ($client === false) {
				$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_TPL_INSTALL_UNKNOWN_CLIENT', $cname));
				return false;
			}
			$basePath = $client->path;
			$clientId = $client->id;
		}
		else {
			// No client attribute was found so we assume the site as the client
			$cname = 'site';
			$basePath = JPATH_SITE;
			$clientId = 0;
		}

		// Set the extension's name
		$name = JFilterInput::getInstance()->clean((string)$xml->name, 'cmd');

		$element = strtolower(str_replace(" ", "_", $name));
		$this->set('name', $name);
		$this->set('element',$element);

		$db = $this->parent->getDbo();
		$db->setQuery('SELECT extension_id FROM #__extensions WHERE type="template" AND element = "'. $element .'"');
		$id = $db->loadResult();

		// Set the template root path
		$this->parent->setPath('extension_root', $basePath . '/templates/' . $element);

		$isOverwrite = true;
		$isUpgrade   = true;

		// if it's on the fs...
		if (file_exists($this->parent->getPath('extension_root')) && (!$isOverwrite || $isUpgrade))
		{
			$updateElement = $xml->update;
			// Upgrade manually set or
			// Update function available or
			// Update tag detected
			if ($isUpgrade || ($this->parent->manifestClass && method_exists($this->parent->manifestClass,'update')) || is_a($updateElement, 'JXMLElement'))
			{
				// Force this one
				$this->parent->setOverwrite(true);
				$this->parent->setUpgrade(true);

				if ($id) { // if there is a matching extension mark this as an update; semantics really
					$this->route = 'update';
				}
			}
			else if (!$isOverwrite)
			{
				// Overwrite is not set
				// If we didn't have overwrite set, find an udpate function or find an update tag so let's call it safe
				$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_PLG_INSTALL_DIRECTORY', JText::_('JLIB_INSTALLER_'.$this->route), $this->parent->getPath('extension_root')));
				return false;
			}
		}

		/*
		 * If the template directory already exists, then we will assume that the template is already
		 * installed or another template is using that directory.
		 */
		if (file_exists($this->parent->getPath('extension_root')) && !$isOverwrite) {
			JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ABORT_TPL_INSTALL_ANOTHER_TEMPLATE_USING_DIRECTORY', $this->parent->getPath('extension_root')));
			return false;
		}

		// If the template directory does not exist, let's create it
		$created = false;
		if (!file_exists($this->parent->getPath('extension_root'))) {
			if (!$created = JFolder::create($this->parent->getPath('extension_root'))) {
				$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_TPL_INSTALL_FAILED_CREATE_DIRECTORY', $this->parent->getPath('extension_root')));

				return false;
			}
		}

		// If we created the template directory and will want to remove it if we have to roll back
		// the installation, let's add it to the installation step stack
		if ($created) {
			$this->parent->pushStep(array ('type' => 'folder', 'path' => $this->parent->getPath('extension_root')));
		}

		if (!is_null($new_manifest))
		{
			$copied_files = $new_manifest->files;
		}
		else
		{
			$copied_files = $xml->files;
		}

		if (!count($deleted_files))
		{
			$deleted_files = null;
		}
		// Copy all the necessary files
		if ($this->parent->parseFiles($copied_files, -1, $deleted_files) === false) {
			// Install failed, rollback changes
			$this->parent->abort();

			return false;
		}

		if ($this->parent->parseFiles($xml->images, -1) === false) {
			// Install failed, rollback changes
			$this->parent->abort();

			return false;
		}

		if ($this->parent->parseFiles($xml->css, -1) === false) {
			// Install failed, rollback changes
			$this->parent->abort();

			return false;
		}

		// Parse optional tags
		$this->parent->parseMedia($xml->media);
		$this->parent->parseLanguages($xml->languages, $clientId);

		// Get the template description
		$this->parent->set('message', JText::_((string)$xml->description));

		// Lastly, we will copy the manifest file to its appropriate place.
		if (!$this->parent->copyManifest(-1)) {
			// Install failed, rollback changes
			$this->parent->abort(JText::_('JLIB_INSTALLER_ABORT_TPL_INSTALL_COPY_SETUP'));

			return false;
		}

		 // Extension Registration

		$row = JTable::getInstance('extension');

		if($this->route == 'update' && $id)
		{
			$row->load($id);
		}
		else
		{
			$row->type = 'template';
			$row->element = $this->get('element');
			// There is no folder for templates
			$row->folder = '';
			$row->enabled = 1;
			$row->protected = 0;
			$row->access = 1;
			$row->client_id = $clientId;
			$row->params = $this->parent->getParams();
			$row->custom_data = ''; // custom data
		}
		$row->name = $this->get('name'); // name might change in an update
		$row->manifest_cache = $this->parent->generateManifestCache();

		if (!$row->store()) {
			// Install failed, roll back changes
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_TPL_INSTALL_ROLLBACK', $db->stderr(true)));

			return false;
		}

		if($this->route == 'install')
		{
			//insert record in #__template_styles
			$query = $db->getQuery(true);
			$query->insert('#__template_styles');
			$query->set('template='.$db->Quote($row->element));
			$query->set('client_id='.$db->Quote($clientId));
			$query->set('home=0');
			$debug = $lang->setDebug(false);
			$query->set('title='.$db->Quote(JText::sprintf('JLIB_INSTALLER_DEFAULT_STYLE', JText::_($this->get('name')))));
			$lang->setDebug($debug);
			$query->set('params='.$db->Quote($row->params));
			$db->setQuery($query);
			// There is a chance this could fail but we don't care...
			$db->query();
		}

		return $row->get('extension_id');
	}
}