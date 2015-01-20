<?php
/*------------------------------------------------------------------------
# JSN Template Framework
# ------------------------------------------------------------------------
# author    JoomlaShine.com Team
# copyright Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
# Websites: http://www.joomlashine.com
# Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
# @license - GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
# @version $Id: jsn_sampledata_helper.php 17121 2012-10-17 03:47:09Z tuyetvt $
-------------------------------------------------------------------------*/

defined( '_JEXEC' ) or die( 'Restricted access' );

class JSNSampleDataHelper
{
	var $_template_folder_path  = '';
	var $_template_folder_name 	= '';
	var $_obj_utils				= null;
	var $_template_edition 		= '';
	var $_template_version 		= '';
	var $_template_name 		= '';
	var $_template_copyright	= '';
	var $_template_author		= '';
	var $_template_author_url	= '';

	function JSNSampleDataHelper()
	{
		$this->_setPhysicalTmplInfo();
		require_once($this->_template_folder_path. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_utils.php');
		$this->_setUtilsInstance();
		$this->_setTmplInfo();
	}
	/**
	 *
	 * Initialize instance of JSNUtils class
	 */
	function _setUtilsInstance()
	{
		$this->_obj_utils = JSNUtils::getInstance();
	}
	/**
	 * Initialize Physical template information variable
	 */
	function _setPhysicalTmplInfo()
	{
		$template_name 					= explode(DIRECTORY_SEPARATOR, str_replace(array('\includes\lib', '/includes/lib'), '', dirname(__FILE__)));
		$template_name 					= $template_name [count( $template_name ) - 1];
		$path_base 						= str_replace(DIRECTORY_SEPARATOR."templates". DIRECTORY_SEPARATOR .$template_name. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib', "", dirname(__FILE__));
		$this->_template_folder_name    = $template_name;
		$this->_template_folder_path 	= $path_base . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $template_name;
	}

	function _setTmplInfo()
	{
		$result 				 	= $this->_obj_utils->getTemplateDetails();
		$manifest_cache				= $this->_obj_utils->getTemplateManifestCache();
		$manifest_cache				= json_decode($manifest_cache);
		$this->_template_edition 	= $result->edition;
		$this->_template_version 	= $manifest_cache->version;
		$this->_template_name 		= $result->name;
		$this->_template_copyright 	= $result->copyright;
		$this->_template_author 	= $result->author;
		$this->_template_author_url = $result->authorUrl;
		$template_name	  			= JString::strtolower($this->_template_name);
		$exploded_template_name 	= explode('_', $template_name);
		$template_name				= @$exploded_template_name[0].'-'.@$exploded_template_name[1];
	}
	/**
	 * This function checks if the defined extension is already installed on
	 * the system.
	 * @param  string $extensionIdName Identifier Name for the extension
	 * @param  string $extensionType   Type of the extension
	 *                                 could be 'component', 'module'
	 *                                 or 'plugin-plugin_type'
	 * @return boolean extensions existance
	 */
	function checkExistingExtension($extensionIdName, $extensionType)
	{
		$db	= JFactory::getDBO();

		if ($extensionType == '')
		{
			$extensionType = 'component';
		}
		$extAttr = self::determineExtensionAttrribute($extensionType);

		$query = 'SELECT COUNT(extension_id) FROM #__extensions'
			. ' WHERE element = "' . $extAttr->prefix . $extensionIdName . '"'
			. ' AND type = "' . $extAttr->type . '"'
			. ' AND folder = "' . $extAttr->folder . '"';

		$db->setQuery($query);
		$result = $db->loadResult();

		if ($result == 0)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	function checkExistingExtensions($data)
	{
		$array_extentions 	= array();
		if ($data && is_array($data))
		{
	        foreach ($data as $value)
			{
				$author = $value->author;

				if ($author != '' && strtolower(trim($author)) == "joomlashine")
				{
					$name 			= $value->name;
					if ($value->hasData == 'true')
					{
						$array_extentions[$name]['hasData'] = true;
						$result = self::checkExistingExtension($name, $value->type);
						if ($result === true)
						{
							$array_extentions[$name]['exist'] = 1;
						}
						else
						{
							$array_extentions[$name]['exist'] = 0;
						}

						if (isset($value->manifestPath) && trim($value->manifestPath) != '')
						{
							$array_extentions[$name]['manifestPath'] = str_replace('/', DIRECTORY_SEPARATOR, trim($value->manifestPath));
						}
						else
						{
							$array_extentions[$name]['manifestPath'] = '';
						}
					}
					else
					{
						$array_extentions[$name]['hasData'] = false;
					}
				}
			}
		}
		return 	$array_extentions;
	}

	function deleteRecordAssetsTableByName($name)
	{
		$db	 	= JFactory::getDBO();
		$where	= array();
		if (count($name))
		{
			foreach ($name as $value)
			{
				$where [] = 'LOWER(name) LIKE '.$db->Quote('%'.'com_'.$value.'%', false);
			}
			$where 	= (count($where) ? ' WHERE '. implode(' OR ', $where) : '');
			$query 	= 'DELETE FROM #__assets'.$where;
			$db->setQuery($query);
			$db->query();
		}
		return false;
	}

	function unpackPackage($p_file)
	{
		jimport('joomla.filesystem.archive');
		$obj_read_xml_file 	= new JSNReadXMLFile();
		$template_manifest	= $obj_read_xml_file->getTemplateManifestFileInformation();
		$tmp_dest 			= JPATH_ROOT. DIRECTORY_SEPARATOR .'tmp';
		$prefix				= $template_manifest['name'].'_sample_data_';
		$tmpdir				= uniqid($prefix);
		$archive_name 		= $p_file;
		$extract_dir 		= JPath::clean($tmp_dest. DIRECTORY_SEPARATOR .$tmpdir);
		$archive_name 		= JPath::clean($tmp_dest. DIRECTORY_SEPARATOR .$archive_name);
		$result				= @JArchive::extract($archive_name, $extract_dir);
		if ($result)
		{
			$path = $tmp_dest. DIRECTORY_SEPARATOR .$tmpdir;
			return $path;
		}
		return false;
	}

	function installSampleData($data)
	{
		$db			= JFactory::getDBO();
		$queries 	= array();
		foreach ($data as $rows)
		{
			$datas 	= $rows->queries;
			if(count($datas))
			{
				foreach ($datas as $value)
				{
					$queries [] = $value;
				}
			}
		}

		if (count($queries))
		{
			foreach ($queries as $query)
			{
				$query = trim($query);
				if ($query != '')
				{
					$db->setQuery($query);
					if (!$db->query())
					{
						return false;
					}
				}
			}
			return true;
		}
		return false;
	}

	function deleteSampleDataFolder($path)
	{
		$path = JPath::clean($path);
		if (JFolder::exists($path))
		{
			JFolder::delete($path);
			return true;
		}
		return false;
	}

	function deleteSampleDataFile($file_name)
	{
		$path = JPATH_ROOT. DIRECTORY_SEPARATOR .'tmp'. DIRECTORY_SEPARATOR .$file_name;

		if(JFile::exists($path))
		{
			JFile::delete($path);
			return true;
		}
		return false;
	}

	function returnError($result, $msg)
	{
		global $error;
		$error = $msg;
	}

	function getNonBasicModule()
	{
		$db					= JFactory::getDBO();
		$str_query 			= '';
		$str_field 			= '';
		$arrayValue			= array();
		$field_module 		= array();
		$queries			= array();

		$table_info_module 	= $db->getTableColumns('#__modules', false);
		$field_module = array_keys($table_info_module);

		$str_field = implode(',', $field_module);
		$query     = "SELECT " . $str_field . " FROM #__modules WHERE `module` NOT IN ('mod_login', 'mod_stats', 'mod_users_latest', "
						." 'mod_footer', 'mod_stats', 'mod_menu', 'mod_articles_latest', 'mod_languages', 'mod_articles_category', "
						." 'mod_whosonline', 'mod_articles_popular', 'mod_articles_archive', 'mod_articles_categories', "
						." 'mod_articles_news', 'mod_related_items', 'mod_search', 'mod_random_image', 'mod_banners', "
						." 'mod_wrapper', 'mod_feed', 'mod_breadcrumbs', 'mod_syndicate', 'mod_custom', 'mod_weblinks') AND `client_id` = 0";
		$db->setQuery($query);
		$rows_module_query = $db->loadAssocList();

		foreach ($rows_module_query as $value)
		{
			reset($field_module);
			foreach ($field_module as $field_value)
			{
				if ($value[$field_value] == '')
				{
					$str_query .= '"", ';
				}
				else
				{
					if($field_value == 'id')
					{
						$str_query .= '"null", ';
					}
					elseif ($field_value == 'published'){
						$str_query .= '"0", ';
					}
					elseif ($field_value == 'params')
					{
						$str_query .= '"'.str_replace('"', '\"', $value['params']).'", ';
					}
					else
					{
						$str_query .= '"'.$value[$field_value].'", ';
					}
				}
			}
			$str_query = substr($str_query, 0 , -2);
			$queries[] = 'INSERT INTO #__modules ('.$str_field.') VALUES ('.$str_query.')';
			$str_query = '';
		}
		return $queries;
	}

	function getNonBasicAdminModule()
	{
		$db					= JFactory::getDBO();
		$str_query 			= '';
		$str_field 			= '';
		$arrayValue			= array();
		$field_module 		= array();
		$queries			= array();

		$table_info_module 	= $db->getTableColumns('#__modules', false);
		$field_module = array_keys($table_info_module);

		$str_field 			= implode(',', $field_module);
		$query 				= "SELECT " . $str_field . " FROM #__modules WHERE `id` NOT IN (2, 3, 4, 6, 7, 8, 9, 10, 12, 13, 14, 15, 70) AND `client_id` = 1";
		$db->setQuery($query);
		$rows_module_query = $db->loadAssocList();

		foreach ($rows_module_query as $value)
		{
			reset($field_module);
			foreach ($field_module as $field_value)
			{
				if ($value[$field_value] == '')
				{
					$str_query .= '"", ';
				}
				elseif ($field_value == 'published'){
					$str_query .= '"0", ';
				}
				else
				{
					if($field_value == 'id')
					{
						$str_query .= '"null", ';
					}
					elseif ($field_value == 'params')
					{
						$str_query .= '"'.str_replace('"', '\"', $value['params']).'", ';
					}
					else
					{
						$str_query .= '"'.$value[$field_value].'", ';
					}
				}
			}
			$str_query = substr($str_query, 0 , -2);
			$queries[] = 'INSERT INTO #__modules ('.$str_field.') VALUES ('.$str_query.' )';
			$str_query = '';
		}
		return $queries;
	}

	function deleteNonBasicAdminModule()
	{
		$db	= JFactory::getDBO();
		$query = 'DELETE FROM #__modules WHERE `id` NOT IN (2, 3, 4, 8, 9, 10, 12, 13, 14, 15) AND `client_id` = 1';
		$db->setQuery($query);
		$db->query();
	}

	function runQueryNonBasicModule($queries, $admin = false)
	{
		$db	= JFactory::getDBO();
		if(count($queries))
		{
			foreach ($queries as $query)
			{
				$query = trim($query);
				if ($query != '')
				{
					$db->setQuery($query);
					$db->query();
					if($admin)
					{
						$id  = $db->insertid();
						$this->insertModuleMenu($id);
					}
				}
			}
		}
		return true;
	}

	function login($username, $password)
	{
		jimport('joomla.user.helper');
		$app 					 = JFactory::getApplication();
		$credentials 			 = array();
		$credentials['username'] = $username;
		$credentials['password'] = $password;

		// Get the log in options.
		$options = array();

		// Perform the login action
		$error = $app->login($credentials, $options);

		// Check if the log in succeeded.
		if (!JError::isError($error) && $error)
		{
			return true;
		}
		else
		{

			return false;
		}
	}

	function setDefaultTemplate($template_name)
	{
		if ($template_name != '')
		{
			$db 	= JFactory::getDBO();
			$query  = 'UPDATE #__template_styles SET home = 0 WHERE client_id = 0';
			$db->setQuery($query);
			$db->query();
			$query = 'UPDATE #__template_styles SET home = 1 WHERE client_id = 0 AND template = '.$db->quote($template_name);
			$db->setQuery($query);
			$db->query();
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * This function check for folder permission requirements by some process,
	 * such as sample data, upgrader, etc.
	 * If the folder has a "marker" tells "all", all subfolders of it will also
	 * be checked recursively.
	 * @param  array  	$folderList list of folder with "marker" to be checked
	 * @return array 	list of folder that failed permission checking, with short description
	 */
	function checkFolderPermission($folderList = array())
	{
		$failedList = array();

		if (!$folderList) {
			/**
			 * Folder must start at root and contains full path
			 * 'all' mark that subfolders also must be writable
			 */
			$folderList = array(
				'/administrator/components' => '',
				'/administrator/language'   => 'all',
				'/administrator/modules'    => '',
				'/components'               => '',
				'/language'                 => 'all',
				'/modules'                  => '',
				'/plugins'                  => 'all',
				'/tmp'                      => ''
			);
		}

		foreach ($folderList as $folder => $marker)
		{
			$folderPath = JPATH_ROOT.$folder;

			if ($marker === 'all')
			{
				$unwritable = false;
				if (!is_writable($folderPath))
				{
					$unwritable = true;
				}
				else
				{
					/* See if any subfolder is unwritable */
					$it = new RecursiveDirectoryIterator($folderPath);
					$subFiles = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
					foreach($subFiles as $file) {
						$basename = $file->getBasename();

						if ($file->isDir() && $basename !== '.' && $basename !== '..')
						{
							if (!$file->isWritable())
							{
								$unwritable = true;
								break;
							}
						}
					}
				}

				if ($unwritable === true)
				{
					$failedList[] = $folder . JText::_('JSN_SAMPLE_DATA_INCLUDE_SUBS');
				}
			}
			else
			{
				if (!is_writable($folderPath))
				{
					$failedList[] = $folder;
				}
			}
		}

		return $failedList;
	}

	function getDomain()
	{
		$pathURL = array();
		$uri	= JURI::getInstance();
		$pathURL['prefix'] = $uri->toString(array('host'));
		return $pathURL['prefix'];
	}

	function getUserActions()
	{
		$user	 = JFactory::getUser();
		$result	 = new JObject;
		$actions = array('core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.delete');
		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, 'com_templates'));
		}
		return $result;
	}

	function getThirdExtensionMenus()
	{
		$db					= JFactory::getDBO();
		$str_query 			= '';
		$sub_str_query 		= '';
		$str_field 			= '';
		$arrayValue			= array();
		$field_menu 		= array();
		$queries			= array();
		$sub_queries		= array();

		$menu_info_module 	= $db->getTableColumns('#__menu', false);
		$field_menu = array_keys($menu_info_module);

		$str_field 			= implode(',', $field_menu);
		$query 				= "SELECT " . $str_field . " FROM #__menu WHERE `client_id` = 1 AND `parent_id` = 1 ORDER BY id ASC";
		$db->setQuery($query);
		$rows_menu_query = $db->loadAssocList();
		$defined			= array('menutype', 'client_id', 'title', 'alias', 'type', 'published', 'component_id', 'img', 'home', 'link');
		foreach ($rows_menu_query as $value)
		{
			reset($field_menu);
			$sub_queries = array();
			foreach ($field_menu as $field_value)
			{
				if ($value[$field_value] == '')
				{
					$str_query .= '"", ';
				}
				else
				{
					if($field_value == 'id')
					{
						$str_query .= 'null, ';
					}
					else
					{
						$str_query .= '"'.$value[$field_value].'", ';
					}
				}
			}
			$str_query = substr($str_query, 0 , -2);
			$queries [strtolower($value['title'])]['parent']= 'INSERT INTO #__menu ('.$str_field.') VALUES ('.$str_query.' )';
			$sub_query = "SELECT " . $str_field . " FROM #__menu WHERE `client_id` = 1 AND `parent_id` = ". (int) $value['id']. ' ORDER BY id ASC';

			$db->setQuery($sub_query);
			$rows_sub_menu_query = $db->loadAssocList();

			if (count($rows_sub_menu_query))
			{
				foreach ($rows_sub_menu_query as $sub_value)
				{
					$sub_menu_data = array();
					reset($field_menu);
					foreach ($field_menu as $sub_field_value)
					{
						if (in_array($sub_field_value, $defined))
						{
							if ($sub_value[$sub_field_value] == '')
							{
								$sub_menu_data[$sub_field_value] = '';
							}
							else
							{
								$sub_menu_data[$sub_field_value] = $sub_value[$sub_field_value];
							}
						}
					}
					$sub_queries [] = $sub_menu_data;
				}

			}
			$queries [strtolower($value['title'])]['sub']= $sub_queries;

			$str_query = '';
		}
		return $queries;
	}

	function restoreThirdExtensionMenus($data)
	{
		$db	= JFactory::getDBO();
		if (count($data))
		{
			foreach ($data as $menu)
			{
				$db->setQuery($menu['parent']);
				$db->query();
				$id  		= $db->insertid();
				$array_id 	=  array('parent_id'=>$id);
				if (count($menu['sub']))
				{
					foreach ($menu['sub'] as $sub_menu)
					{
						$table = JTable::getInstance('menu');
						$sub_menu = $sub_menu;
						$tmp_sub_data = array_merge($sub_menu, $array_id);

						$table->setLocation($id, 'last-child'); // In 3.0 this function return nothing if success

						if ($table->bind($tmp_sub_data))
						{
							if ($table->check())
							{
								$table->store();
							}
						}
					}
				}
			}
		}
	}

	function insertModuleMenu($moduleID)
	{
		$db 	= JFactory::getDBO();
		$query  = 'INSERT INTO #__modules_menu (moduleid, menuid) VALUES ("'.$moduleID.'", "0")';
		$db->setQuery($query);
		$db->query();
	}

	function rebuildMenu()
	{
		$db 	= JFactory::getDbo();
		$table 	= JTable::getInstance('Menu', 'JTable');

		if (!$table->rebuild())
		{
			$this->setError($table->getError());
			return false;
		}

		$db->setQuery(
			'SELECT id, params' .
			' FROM #__menu' .
			' WHERE params NOT LIKE '.$db->quote('{%') .
			'  AND params <> '.$db->quote('')
		);

		$items = $db->loadObjectList();
		if ($error = $db->getErrorMsg())
		{
			return false;
		}

		foreach ($items as &$item)
		{
			$registry = new JRegistry;
			$registry->loadString($item->params);
			$params = (string)$registry;

			$db->setQuery(
				'UPDATE #__menu' .
				' SET params = '.$db->quote($params).
				' WHERE id = '.(int) $item->id
			);
			if (!$db->query())
			{
				return false;
			}

			unset($registry);
		}
		// Clean the cache
		$this->cleanCache('com_modules');
		$this->cleanCache('mod_menu');
		return true;
	}

	function cleanCache($group)
	{
		$conf = JFactory::getConfig();
		$options = array(
			'defaultgroup' 	=> $group,
			'cachebase'		=> $conf->get('cache_path', JPATH_SITE. DIRECTORY_SEPARATOR .'cache')
		);
		jimport('joomla.cache.cache');
		$cache = JCache::getInstance('callback', $options);
		$cache->clean();
	}

	function copyContentFromFilesFolder($path)
	{
		$folderPath = $path. DIRECTORY_SEPARATOR .'files';
		if (JFolder::exists($folderPath))
		{
			$fileList 	= JFolder::files($folderPath);
			$folderList = JFolder::folders($folderPath);
			if ($fileList !== false)
			{
				foreach ($fileList as $file)
				{
					if (JFile::exists($folderPath. DIRECTORY_SEPARATOR .$file) && substr($file, 0, 1) != '.')
					{
						JFile::copy($folderPath. DIRECTORY_SEPARATOR .$file, JPATH_ROOT. DIRECTORY_SEPARATOR .$file);
					}
				}
			}

			if ($folderList !== false)
			{
				foreach ($folderList as $folder)
				{
					if (JFolder::exists($folderPath. DIRECTORY_SEPARATOR .$folder))
					{
						JFolder::copy($folderPath. DIRECTORY_SEPARATOR .$folder, JPATH_ROOT. DIRECTORY_SEPARATOR .$folder, '', true);
					}
				}
			}

			return true;
		}
		return false;
	}

	function getPackageFromUpload()
	{
		$install_file = JRequest::getVar('install_package', null, 'files', 'array');
		if (!(bool) ini_get('file_uploads'))
		{
			$msg 	= 'File upload function is disabled, please enable it in file "php.ini"';
			JError::raiseWarning('SOME_ERROR_CODE', JText::_($msg));
			return false;
		}
		if (!extension_loaded('zlib'))
		{
			$msg = 'Zlib library is disabled, please enable it in file "php.ini"';
			JError::raiseWarning('SOME_ERROR_CODE', JText::_($msg));
			return false;
		}
		if ($install_file['name'] == '')
		{
			$msg 	= 'Sample data package is not selected, please download and select it';
			JError::raiseWarning('SOME_ERROR_CODE', JText::_($msg));
			return false;
		}
		if (JFile::getExt($install_file['name']) != 'zip')
		{
			$msg = 'Sample data package has incorrect format, please use exactly the file you downloaded';
			JError::raiseWarning('SOME_ERROR_CODE', JText::_($msg));
			return false;
		}
		$tmp_dest 	= JPath::clean(JPATH_ROOT. DIRECTORY_SEPARATOR .'tmp'. DIRECTORY_SEPARATOR .$install_file['name']);
		$tmp_src	= $install_file['tmp_name'];
		if (!JFile::upload($tmp_src, $tmp_dest))
		{
			$msg = 'Folder "tmp" is Unwritable, please set it to Writable (chmod 777). You can set the folder back to Unwritable after sample data installation';
			JError::raiseWarning('SOME_ERROR_CODE', JText::_($msg));
			return false;
		}
		return 	$install_file['name'];
	}

	function installSampleDataManually()
	{
		$filename 			= $this->getPackageFromUpload();
		if (!$filename) return false;
		$obj_read_xml_file = new JSNReadXMLFile();
		$obj_utils         = new JSNUtils();
		$unpack_package		= $this->unpackPackage($filename);
		$template_manifest	= json_decode($obj_utils->getTemplateManifestCache(), true);

		$array_non_basic_module			= $this->getNonBasicModule();
		$array_non_basic_admin_module	= $this->getNonBasicAdminModule();
		$array_3rd_extension_menu		= $this->getThirdExtensionMenus();
		$domain							= $this->getDomain();
		$errors							= array();
		$isNotExsited					= array();
		$folderbackup					= $this->_template_folder_path. DIRECTORY_SEPARATOR .'backups';
		if ($unpack_package)
		{
			$sample_data_xml = $obj_read_xml_file->getSampleDataFileContent($unpack_package, $this->_template_folder_name);
			$installed_data = $sample_data_xml['installed_data'];

			if ($installed_data && is_array($installed_data))
			{
				if (trim($sample_data_xml['version']) != trim($template_manifest['version']))
				{
					$this->deleteSampleDataFile($filename);
					$this->deleteSampleDataFolder($unpack_package);
					$msg = JText::_('JSN_SAMPLE_DATA_OUTDATED_PRODUCT')
						. ' <a class="link-action" href="http://www.joomlashine.com/docs/general/how-to-update-product.html" target="_blank">' . JText::_('JSN_SAMPLE_DATA_HOW_TO_UPDATE') . '</a>';
					JError::raiseWarning('SOME_ERROR_CODE', JText::_($msg));
					return false;
				}
				else
				{
					$result_check_existing_extension = $this->checkExistingExtensions($installed_data);
					foreach ($result_check_existing_extension as $key => $value)
					{
						if ($value['hasData'] === false)
						{
							unset($installed_data[$key]);
						}
						else
						{
							if ($value['exist'])
							{
								$extenstion_manifest = $obj_read_xml_file->getExtensionManifestFileInformation($key, $value['manifestPath']);
								if (count($extenstion_manifest))
								{
									if (@$extenstion_manifest['version'] != $installed_data[$key]->version)
									{
	    								$errors [] = '<strong>'.$installed_data[$key]->description.'</strong> - '.'Version <strong>'.$installed_data[$key]->version.'</strong> is required. <a class="link-action" href="http://www.joomlashine.com/docs/general/how-to-update-product.html" target="_blank">How to update?</a>';
	    								unset($installed_data[$key]);
									}
								}
								else
								{
									$errors [] = '<strong>'.$installed_data[$key]->description.'</strong> - '.'the version information not found in sample data package.';
									unset($installed_data[$key]);
								}
							}
							else
							{
								$errors [] = '<strong>'.$installed_data[$key]->description.'</strong> - '.'Extension is not installed. <a class="link-action" href="http://www.joomlashine.com/joomla-extensions/jsn-'.$key.'.html" target="_blank">Get it now.</a>';
								$isNotExsited [] = $key;
								unset($installed_data[$key]);
							}
						}
					}
					$this->returnError('false', $errors);

					$obj_backup	= JSNBackup::getInstance();
					if (is_writable($folderbackup))
					{
						$backup_file_name 		= $obj_backup->executeBackup($this->_template_folder_path. DIRECTORY_SEPARATOR .'backups', $domain, $installed_data);
					}
					$this->deleteNonBasicAdminModule();
					$this->installSampleData($installed_data);
					if (count($isNotExsited))
					{
						$asset = JTable::getInstance('Asset');
						foreach ($isNotExisted as $element)
						{
							$element = 'com_' . $element;
							if ($asset->loadByName($element))
							{
								$asset->delete();
							}
						}
					}

					$this->runQueryNonBasicModule($array_non_basic_module);
					$this->runQueryNonBasicModule($array_non_basic_admin_module, true);
					$this->restoreThirdExtensionMenus($array_3rd_extension_menu);
					$this->rebuildMenu();
					$this->copyContentFromFilesFolder($unpack_package);
					$this->deleteSampleDataFolder($unpack_package);
					$this->setDefaultTemplate(strtolower($template_manifest['name']));
					$this->deleteSampleDataFile($filename);
					return true;
				}
			}
			else
			{
				$this->deleteSampleDataFile($filename);
				$msg = JText::_('JSN_SAMPLE_DATA_INVALID');
				JError::raiseWarning('SOME_ERROR_CODE', $msg);
			}
			return false;
		}
		else
		{
			$msg = JText::_('JSN_SAMPLE_DATA_UNABLE_EXTRACT_PACKAGE');
			JError::raiseWarning('SOME_ERROR_CODE', $msg);
		}
		return false;
	}

	function determineExtInstallation($extInfo)
	{
		$needInstall = false;
		$outdated    = false;
		$proEdition  = false;

		$sdHelperInstance = new JSNSampleDataHelper();

		/* Check if the extension has already been installed */
		$extensionExists = $this->checkExistingExtension($extInfo->name, $extInfo->type);

		if ($extensionExists === false)
		{
			$needInstall = true;
		}
		else
		{
			$xmlReaderInstance = new JSNReadXMLFile();
			$utilInstance      = new JSNUtils();
			$extManifestInfo = $xmlReaderInstance->getExtensionManifestFileInformation($extInfo->name, $extInfo->manifestPath);

			// $extLatestVersion = $utilInstance->getLatestProductVersion($extInfo->identifiedName, $extInfo->name);

			/* Fall-back: use version supplied in sample data file */
			// if ($extLatestVersion === false)
			// {
			// 	$extLatestVersion = $extInfo->version;
			// }

			$extLatestVersion = $extInfo->version;

			/* Version outdated, need to update */
			if ($extManifestInfo['version'] != $extLatestVersion)
			{
				$outdated = true;

				/* FREE edition will get updated */
				if ($extManifestInfo['edition'] == '' || strtolower($extManifestInfo['edition']) == 'free')
				{
					$needInstall = true;
				}
				else
				{
					if ($extInfo->hasData === true)
					{
						$proEdition = true;
					}
				}
			}
		}

		$result = new stdClass();

		$result->toInstall  = $needInstall;
		$result->exist      = $extensionExists;
		$result->outdated   = $outdated;
		$result->proEdition = $proEdition;

		return $result;
	}

	function determineFailedExtensions(&$failedExts, $extInfoArray, $selectedExts = array())
	{
		$extInfoKeys = array_keys($extInfoArray);

		/* Not-selected exts will be treated as failed ones */
		$notSelected = array_diff($extInfoKeys, $selectedExts);
		foreach ($notSelected as $ext)
		{
			$mes = '';

			if ($extInfoArray[$ext]->hasData === true)
			{
				if ($extInfoArray[$ext]->outdated === true || $extInfoArray[$ext]->exist === false)
				{
					if ($extInfoArray[$ext]->outdated === true)
					{
						$mes = JText::sprintf('JSN_SAMPLE_DATA_WARNING_EXT_PRO_EXIST', $extInfoArray[$ext]->description);
						// Include the internal update link
						$internalUpdateLink = JURI::root() . 'administrator/index.php?option=com_' . $extInfoArray[$ext]->name . '&controller=updater';
						$mes .= '&nbsp;<a class="link-action" target="_blank" href="' . $internalUpdateLink . '">' . JText::_('JSN_SAMPLE_DATA_EXT_INTERNAL_UPDATE') . '</a>';
					}
					elseif ($extInfoArray[$ext]->exist === false)
					{
						$downloadUrl = 'http://www.joomlashine.com/joomla-extensions/jsn-' . $extInfoArray[$ext]->name . '.html';
						$mes = JText::sprintf('JSN_SAMPLE_DATA_EXT_NOT_INSTALLED', $extInfoArray[$ext]->description, $downloadUrl);
					}

					$failedExts[$ext]['message'] = $mes;
					$failedExts[$ext]['exist']   = $extInfoArray[$ext]->exist;
				}
			}
		}
	}

	/**
	 * This function will return an object of neccessary attributes for querying
	 * an extension information from #__extensions table.
	 * @param  string $extensionType Extension type get from sample data XML
	 *                               file, with value like "component", "module"
	 *                               or "plugin-pluginfolder".
	 * @return object                Extension attribute, including prefix,
	 *                               actual type and folder.
	 */
	function determineExtensionAttrribute($extensionType)
	{
		$extObj = new stdClass();

		if ($extensionType == 'component')
		{
			$extObj->prefix = 'com_';
			$extObj->type   = 'component';
			$extObj->folder = '';
		}
		else if ($extensionType == 'module')
		{
			$extObj->prefix = 'mod_';
			$extObj->type   = 'module';
			$extObj->folder = '';
		}
		else if (strpos($extensionType, 'plugin') !== false)
		{
			$extObj->prefix = '';
			$extObj->type   = 'plugin';

			$extensionType = explode('-', $extensionType);
			$extObj->folder = $extensionType[1];
		}

		return $extObj;
	}

	function enableInstalledPlugin($pluginElement, $pluginType)
	{
		$db = JFactory::getDBO();
		$pluginAttr = self::determineExtensionAttrribute($pluginType);

		if ($pluginAttr->type != 'plugin')
		{
			return;
		}

		$query = 'SELECT extension_id FROM #__extensions'
			. ' WHERE element = "' . $pluginElement . '"'
			. ' AND type = "' . $pluginAttr->type . '"'
			. ' AND folder = "' . $pluginAttr->folder . '"';

		$db->setQuery($query);
		$pluginId = $db->loadResult();

		if ($pluginId)
		{
			$query = 'UPDATE #__extensions SET enabled = 1'
				. ' WHERE extension_id = ' . $pluginId;
			$db->setQuery($query);
			$db->query();
		}
	}

	function setSampleDataURL()
	{
		$joomlaVersion    	= $this->_obj_utils->getJoomlaVersion();

		if (!defined('JSN_SAMPLE_DATA_FILE_URL'))
		{
			define("JSN_SAMPLE_DATA_FILE_URL",'http://www.joomlashine.com/joomla-templates/'.strtolower(str_replace('_', '-', $this->_template_name)).'-sample-data-j'.$joomlaVersion.'.zip');
		}
	}

	function disableMenuItems($element)
	{
		$db = JFactory::getDBO();

		$query = 'UPDATE #__menu SET published = 0 WHERE menutype = "mainmenu"'
			. ' AND link LIKE "%option=' . $element . '%"';

		$db->setQuery($query);
		$db->query();
	}
}
