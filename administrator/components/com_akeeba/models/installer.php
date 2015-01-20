<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 *
 * @since     3.3
 */

defined('_JEXEC') or die('');

JLoader::import('joomla.application.component.model');
JLoader::import('joomla.installer.installer');
JLoader::import('joomla.installer.helper');

require_once F0FTemplateUtils::parsePath('admin://components/com_installer/models/install.php', true);

/**
 * Class AkeebaModelInstaller extends the core com_installer InstallerModelInstall model,
 * adding the brains which decide how to perform the SRP backup in each case.
 */
class AkeebaModelInstaller extends InstallerModelInstall
{
	/**
	 * Fetches a package from the upload form and saves it to the temporary directory
	 *
	 * @return  boolean  True if the upload is successful
	 */
	public function upload()
	{
		// Get the uploaded file information
		$userfile = JRequest::getVar('install_package', null, 'files', 'array');

		// Make sure that file uploads are enabled in php
		if (!(bool)ini_get('file_uploads'))
		{
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLFILE'));

			return false;
		}

		// Make sure that zlib is loaded so that the package can be unpacked
		if (!extension_loaded('zlib'))
		{
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLZLIB'));

			return false;
		}

		// If there is no uploaded file, we have a problem...
		if (!is_array($userfile))
		{
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_NO_FILE_SELECTED'));

			return false;
		}

		// Check if there was a problem uploading the file.
		if ($userfile['error'] || $userfile['size'] < 1)
		{
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR'));

			return false;
		}

		// Build the appropriate paths
		$config = JFactory::getConfig();
		$tmp_dest = $config->get('tmp_path') . '/' . $userfile['name'];
		$tmp_src = $userfile['tmp_name'];

		// Move uploaded file
		JLoader::import('joomla.filesystem.file');
		$uploaded = JFile::upload($tmp_src, $tmp_dest);

		if (!$uploaded)
		{
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR'));

			return false;
		}

		// Store the uploaded package's location
		$session = JFactory::getSession();
		$session->set('compressed_package', $tmp_dest, 'akeeba');

		return true;
	}

	/**
	 * Downloads a package from a URL and saves it to the temporary directory
	 *
	 * @return  boolean  True is the download was successful
	 */
	public function download()
	{
		$input = JFactory::getApplication()->input;

		// Get the URL of the package to install
		$url = $input->getString('install_url');

		// Did you give us a URL?
		if (!$url)
		{
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_ENTER_A_URL'));

			return false;
		}

		// Handle updater XML file case:
		if (preg_match('/\.xml\s*$/', $url))
		{
			jimport('joomla.updater.update');
			$update = new JUpdate;
			$update->loadFromXML($url);
			$package_url = trim($update->get('downloadurl', false)->_data);
			if ($package_url)
			{
				$url = $package_url;
			}
			unset($update);
		}

		// Download the package at the URL given
		$p_file = JInstallerHelper::downloadPackage($url);

		// Was the package downloaded?
		if (!$p_file)
		{
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_INVALID_URL'));

			return false;
		}

		$config = JFactory::getConfig();
		$tmp_dest = $config->get('tmp_path');

		// Store the uploaded package's location
		$session = JFactory::getSession();
		$session->set('compressed_package', $tmp_dest . '/' . $p_file, 'akeeba');

		return true;
	}

	/**
	 * Extracts a package archive to the temporary directory. The package information is
	 * saved in the session.
	 *
	 * @return  boolean  True if the extraction was successful
	 */
	function extract()
	{
		$session = JFactory::getSession();
		$compressed_package = $session->get('compressed_package', null, 'akeeba');

		// Do we have a compressed package?
		if (is_null($compressed_package))
		{
			JError::raiseWarning('', 'No package specified');

			return false;
		}

		// Extract the package
		$package = JInstallerHelper::unpack($compressed_package);
		$session->set('package', $package, 'akeeba');

		return true;
	}

	/**
	 * Gets the package information from an (already extracted) package in a directory,
	 * then saves the package information in the session.
	 *
	 * @param   string $folder (optional) Which directory to look into.
	 *
	 * @return  boolean  Always true
	 */
	public function fromDirectory($folder = null)
	{
		if (!empty($folder))
		{
			$input = JFactory::getApplication()->input;
			$input->set('install_directory', $folder);
		}

		$package = $this->_getPackageFromFolder();

		$session = JFactory::getSession();
		$session->set('package', $package, 'akeeba');

		return true;
	}

	/**
	 * Cleans up any remaining files after the installation is over (successful or not)
	 *
	 * @return  boolean  True on success
	 */
	function cleanUp()
	{
		$session = JFactory::getSession();
		$package = $session->get('package', '', 'akeeba');

		// Was the package unpacked?
		if (!$package || empty($package))
		{
			return false;
		}

		// Cleanup the install files
		if (!is_file($package['packagefile']))
		{
			$config = JFactory::getConfig();
			$package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
		}

		JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

		return true;
	}

	/**
	 * Identifies the extension whose package is extracted in $p_dir. It returns an array with
	 * the extension type, name and group (folder for plugins, admin/site section for modules,
	 * etc)
	 *
	 * @param   string $p_dir The directory containing the extracted package
	 *
	 * @return  array|bool  Hashed array on success, boolean false on failure
	 */
	public function getExtensionName($p_dir)
	{
		// Search the install dir for an XML file
		JLoader::import('joomla.filesystem.folder');
		$files = JFolder::files($p_dir, '\.xml$', 1, true);

		if (!count($files))
		{
			JError::raiseWarning(1, JText::_('JLIB_INSTALLER_ERROR_NOTFINDXMLSETUPFILE'));

			return false;
		}

		foreach ($files as $file)
		{
			try
			{
				$xml = new SimpleXMLElement($file, LIBXML_NONET, true);
			}
			catch (Exception $e)
			{
				continue;
			}

			if (($xml->getName() != 'install') && ($xml->getName() != 'extension') && ($xml->getName() != 'akeebabackup'))
			{
				unset($xml);
				continue;
			}

			$type = (string)$xml->attributes()->type;

			list($name, $cname, $group) = $this->extractName($xml, $type);

			if (empty($name))
			{
				$name = false;
			}

			if ($name !== false)
			{
				$info = array('name' => $name, 'cname' => $cname, 'group' => $group);

				if (!$this->isInstalled($xml, $type, $info))
				{
					$name = false;
				}
			}
			else
			{
				return false;
			}

			// Free up memory from SimpleXML parser
			unset ($xml);

			if ($name === false)
			{
				return false;
			}

			// Return the name
			return array(
				'name'   => $name,
				'client' => $cname,
				'group'  => $group
			);
		}

		return false;
	}

	/**
	 * Returns the URL used to take a System Restore Point backup for the current extension.
	 * If the extension is not already installed it will return boolean false.
	 *
	 * @return  bool|string  URL on success, false if the extension is not already installed
	 */
	public function getSrpUrl()
	{
		$session = JFactory::getSession();
		$package = $session->get('package', array(), 'akeeba');

		$name = $this->getExtensionName($package['dir']);

		if ($name !== false)
		{
			// If SRPs are supported, get the SRP URL
			$type = $package['type'];
			$url = 'index.php?option=com_akeeba&view=backup&tag=restorepoint&type=' . $type . '&name=' . urlencode($name['name']);

			switch ($type)
			{
				case 'component':
					break;
				case 'file':
					break;
				case 'library':
					break;
				case 'module':
					$url .= '&group=' . $name['client'];
					break;
				case 'package':
					break;
				case 'plugin':
					$url .= '&group=' . $name['group'];
					break;
				case 'template':
					$url .= '&group=' . $name['client'];
					break;
				default:
					return false;
					break;
			}

			$url .= '&returnurl=' . urlencode('index.php?option=com_installer&view=install&task=install.akrealinstall');

			$token = JSession::getFormToken();
			$url .= '&' . $token . '=1';

			// Force the profile
			$profile = 1;

			if (!class_exists('JPluginHelper'))
			{
				JLoader::import('joomla.plugin.helper');
				JLoader::import('cms.plugin.helper');
			}

			$plugin = JPluginHelper::getPlugin('system', 'srp');

			if (!empty($plugin))
			{
				$plugin = (array)$plugin;
				if (array_key_exists('params', $plugin))
				{
					$params = new JRegistry($plugin['params']);
					$profile = $params->get('profileid', 1);
				}
			}

			JFactory::getSession()->set('profile', $profile, 'akeeba');

			// Return the SRP URL
			return $url;
		}
		else
		{
			// If SRPs are not supported, return false
			return false;
		}
	}

	/**
	 * Downloads an update package given an update record ID ($uid). The package is downloaded
	 * and its location recorded in the session.
	 *
	 * @param   integer $uid The update record ID
	 *
	 * @return  True on success
	 */
	public function downloadUpdate($uid)
	{
		// Unset the compressed_package session variable
		$session = JFactory::getSession();
		$session->set('compressed_package', null, 'akeeba');

		// Find the download location from the XML update stream
		jimport('joomla.updater.update');
		$update = new JUpdate;
		$instance = JTable::getInstance('update');
		$instance->load($uid);
		$update->loadFromXML($instance->detailsurl);

		if (isset($update->get('downloadurl')->_data))
		{
			$url = $update->downloadurl->_data;
		}
		else
		{
			JError::raiseWarning('', JText::_('COM_INSTALLER_INVALID_EXTENSION_UPDATE'));

			return false;
		}

		// Sadly JInstallerHelper will only add any extra query param firing installer plugins, it won't check
		// the update_sites table, so we have to that manually
		if (version_compare(JVERSION, '3.2.0', 'ge'))
		{
			$db = JFactory::getDbo();

			// There is no JTable for update_sites, so I have to do that manually...
			$query = $db->getQuery(true)
				->select($db->qn('extra_query'))
				->from($db->qn('#__update_sites'))
				->where($db->qn('update_site_id') . ' = ' . $db->q($instance->update_site_id));

			try
			{
				$extra = $db->setQuery($query)->loadResult();

				if ($extra)
				{
					$url .= '&' . $extra;
				}
			}
			catch (Exception $e)
			{
				// Simply do nothing, we'll cross our fingers
			}
		}

		// Download the package
		$p_file = JInstallerHelper::downloadPackage($url);

		// Was the package downloaded?
		if (!$p_file)
		{
			JError::raiseWarning('', JText::sprintf('COM_INSTALLER_PACKAGE_DOWNLOAD_FAILED', $url));

			return false;
		}

		// Store the uploaded package's location
		$config = JFactory::getConfig();
		$tmp_dest = $config->get('tmp_path');

		$session->set('compressed_package', $tmp_dest . '/' . $p_file, 'akeeba');

		return true;
	}

	/**
	 * Searches inside the manifest file for the extension name
	 *
	 * @param   SimpleXMLElement $xml  XML instance of the manifest file
	 * @param   string           $type Extension type
	 *
	 * @return array
	 */
	private function extractName(SimpleXMLElement $xml, $type)
	{
		$name = false;
		$cname = '';
		$group = '';

		switch ($type)
		{
			case 'component' :
			case 'file'      :
			case 'library'   :
			case 'package'   :
			case 'template'  :
				$name = (string)$xml->name;

				if (version_compare(JVERSION, '3.2.0', 'ge'))
				{
					$jfilter = new JFilterInput();
					$name = $jfilter->clean($name, 'cmd');
				}
				else
				{
					$name = JFilterInput::clean($name, 'cmd');
				}

				if ($type == 'template')
				{
					$cname = (string)$xml->attributes()->client;
				}
				break;

			case 'module':
			case 'plugin':
				$cname = (string)$xml->attributes()->client;
				$group = (string)$xml->attributes()->group;
				$element = $xml->files;

				if (($element instanceof SimpleXMLElement) && $element->count())
				{
					foreach ($element->children() as $file)
					{
						if ($file->attributes()->$type)
						{
							$name = (string)$file->attributes()->$type;
							break;
						}
					}
				}
				break;
		}

		return array($name, $cname, $group);
	}

	/**
	 * Is the selected extension installed?
	 *
	 * @param   SimpleXMLElement $xml  XML instance of the manifest file
	 * @param   string           $type Extension type
	 * @param   array            $info Indexed array that hold extension information: name, client and group
	 *
	 * @return  bool
	 */
	private function isInstalled(SimpleXMLElement $xml, $type, $info)
	{
		// Make sure the extension is already installed - otherwise there is no point!
		JLoader::import('joomla.filesystem.file');
		JLoader::import('joomla.filesystem.folder');

		$db = JFactory::getDbo();

		switch ($type)
		{
			case 'component':
				$info['name'] = strtolower($info['name']);

				if (strpos($info['name'], 'com_') === false)
				{
					$info['name'] = 'com_' . $info['name'];
				}

				$query = $db->getQuery(true)
					->select('COUNT(*)')
					->from($db->qn('#__extensions'))
					->where($db->qn('element') . ' = ' . $db->q($info['name']))
					->where($db->qn('type') . ' = ' . $db->q('component'));

				return (bool)$db->setQuery($query)->loadResult();
				break;

			case 'file';
				$query = $db->getQuery(true)
					->select('COUNT(*)')
					->from($db->qn('#__extensions'))
					->where($db->qn('element') . ' = ' . $db->q($info['name']))
					->where($db->qn('type') . ' = ' . $db->q('file'));

				return (bool)$db->setQuery($query)->loadResult();
				break;

			case 'library';
				$query = $db->getQuery(true)
					->select('COUNT(*)')
					->from($db->qn('#__extensions'))
					->where($db->qn('element') . ' = ' . $db->q($info['name']))
					->where($db->qn('type') . ' = ' . $db->q('library'));

				return (bool)$db->setQuery($query)->loadResult();
				break;

			case 'module':
				$client = $info['cname'] == 'site' ? 1 : 0;

				$query = $db->getQuery(true)
					->select('COUNT(*)')
					->from($db->qn('#__extensions'))
					->where($db->qn('element') . ' = ' . $db->q($info['name']))
					->where($db->qn('client_id') . ' = ' . $db->q($client))
					->where($db->qn('type') . ' = ' . $db->q('module'));

				return (bool)$db->setQuery($query)->loadResult();
				break;

			case 'package' :
				// Package manifest files are a "little" undocumented, so let's try to create
				// a function that is flexible as much as possible

				$element = $info['name'];

				if (strpos($info['name'], 'pkg_') === false)
				{
					$element = 'pkg_' . $element;
				}

				$query = $db->getQuery(true)
					->select('COUNT(*)')
					->from($db->qn('#__extensions'))
					->where($db->qn('element') . ' = ' . $db->q($element))
					->where($db->qn('type') . ' = ' . $db->q('package'));

				if ($db->setQuery($query)->loadResult())
				{
					return true;
				}

				// No luck with the original name, let's try with the element "packagename"
				$element = (string)$xml->packagename;

				if (strpos($info['name'], 'pkg_') === false)
				{
					$element = 'pkg_' . $element;
				}

				$query->clear('where')
					->where($db->qn('element') . ' = ' . $db->q($element))
					->where($db->qn('type') . ' = ' . $db->q('package'));

				if ($db->setQuery($query)->loadResult())
				{
					return true;
				}

				// Still nothing? Ok, maybe it's really not installed
				return false;
				break;

			case 'plugin':
				$query = $db->getQuery(true)
					->select('COUNT(*)')
					->from($db->qn('#__extensions'))
					->where($db->qn('element') . ' = ' . $db->q($info['name']))
					->where($db->qn('folder') . ' = ' . $db->q($info['group']))
					->where($db->qn('type') . ' = ' . $db->q('plugin'));

				return (bool)$db->setQuery($query)->loadResult();
				break;

			case 'template':
				$client = $info['cname'] == 'site' ? 1 : 0;

				$query = $db->getQuery(true)
					->select('COUNT(*)')
					->from($db->qn('#__extensions'))
					->where($db->qn('element') . ' = ' . $db->q($info['name']))
					->where($db->qn('client_id') . ' = ' . $db->q($client))
					->where($db->qn('type') . ' = ' . $db->q('template'));

				return (bool)$db->setQuery($query)->loadResult();
				break;

			default:
				return false;
				break;
		}
	}
}