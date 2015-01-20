<?php
/*------------------------------------------------------------------------
# JSN Template Framework
# ------------------------------------------------------------------------
# author    JoomlaShine.com Team
# copyright Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
# Websites: http://www.joomlashine.com
# Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
# @license - GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
# @version $Id: jsn_readxmlfile.php 17002 2012-10-13 09:39:19Z tuyetvt $
-------------------------------------------------------------------------*/

defined('_JEXEC' ) or die('Restricted access');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
class JSNReadXMLFile
{
	var $_template_folder_path  = '';
	var $_template_folder_name 	= '';

	function JSNReadXMLFile()
	{
		$this->_setPhysicalTmplInfo();
	}

	function _setPhysicalTmplInfo()
	{
		$template_name 					= explode(DIRECTORY_SEPARATOR, str_replace(array('\includes\lib', '/includes/lib'), '', dirname(__FILE__)));
		$template_name 					= $template_name [count( $template_name ) - 1];
		$path_base 						= str_replace(DIRECTORY_SEPARATOR."templates". DIRECTORY_SEPARATOR .$template_name. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib', "", dirname(__FILE__));
		$this->_template_folder_name    = $template_name;
		$this->_template_folder_path 	= $path_base. DIRECTORY_SEPARATOR .'templates'. DIRECTORY_SEPARATOR .$template_name;
	}

	function parseXmlFile($filePath) {
		$result = array();

		/* JFactory::getXML() is the recommended method of Joomla 2.5 */
		if (!$xml = JFactory::getXML($filePath)) {
			return false;
		} else {
			return $xml;
		}
	}

	function getTemplateInfo()
	{
		$filePath = $this->_template_folder_path. DIRECTORY_SEPARATOR .'templateDetails.xml';
		$xml = $this->parseXmlFile($filePath);

		if (!$xml) {
			return false;
		}

		$data = new stdClass();

		$data->directory = $this->_template_folder_name;
		$data->checked_out = 0;

		$xmlAttributes = $xml->attributes();
		$data->type = $xmlAttributes['type'] ? $xmlAttributes['type'] : '';
		if ($data->type != 'template') {
			return false;
		}

		$data->name 		= $xml->name ? (string) $xml->name : '';
		$data->creationDate = $xml->creationDate ? (string) $xml->creationDate : JText::_('Unknown');
		$data->author 		= $xml->author ? (string) $xml->author : JText::_('Unknown');
		$data->copyright 	= $xml->copyright ? (string) $xml->copyright : '';
		$data->authorEmail 	= $xml->authorEmail ? (string) $xml->authorEmail : '';
		$data->authorUrl	= $xml->authorUrl ? (string) $xml->authorUrl : '';
		$data->version 		= $xml->version ? (string) $xml->version : '';
		$data->edition 		= $xml->edition ? (string) $xml->edition : '';
 		$data->license 		= $xml->license ? (string) $xml->license : '';
 		$data->description 	= $xml->description ? (string) $xml->description : '';
 		$data->group 		= $xml->group ? (string) $xml->group : '';
 		$data->mosname 		= JString::strtolower(str_replace(' ', '_', $data->name));

 		unset($xml);

		return $data;
	}

	function getTemplatePositions()
	{
		$filePath = $this->_template_folder_path. DIRECTORY_SEPARATOR .'templateDetails.xml';
		$xml = $this->parseXmlFile($filePath);

		if (!$xml) {
			return false;
		}

		$positions = array();

		if ($xml->positions)
		{
			foreach ($xml->positions->position as $pos) {
				$positions[] = (string) $pos;
			}
		}

		unset($xml);

		return $positions;
	}

	function getTemplateManifestFileInformation()
	{
		$array_result = array();

		$filePath = $this->_template_folder_path. DIRECTORY_SEPARATOR .'templateDetails.xml';
		$xml = $this->parseXmlFile($filePath);

		if (!$xml) {
			return false;
		}

		if ($xml->name)
		{
			$arr_name 							= explode('_', (string) $xml->name);
			$array_result['name'] 				= strtolower(@$arr_name[0].'_'.@$arr_name[1]);
			$array_result['name_uppercase'] 	= @$arr_name[0].' '.@$arr_name[1];
			$array_result['full_name'] 			= @$arr_name[0].'_'.@$arr_name[1].'_'.@$arr_name[2];
		}

		$array_result['version'] = $xml->version ? (string) $xml->version : '';
		$array_result['edition'] = $xml->edition ? (string) $xml->edition : '';

		unset($xml);

		return $array_result;
	}

	function getExtensionManifestFileInformation($name, $manifestPath)
	{
		$data = array();

		if ($manifestPath == '')
		{
			return $data;
		}

		$filePath = JPATH_ROOT. DIRECTORY_SEPARATOR .str_replace('/', DIRECTORY_SEPARATOR, $manifestPath);
		if ($name == 'imageshow')
		{
			if (!JFile::exists($filePath))
			{
				$filePath = JPATH_ADMINISTRATOR. DIRECTORY_SEPARATOR .'components'. DIRECTORY_SEPARATOR .'com_'.$name. DIRECTORY_SEPARATOR .'com_'.$name.'.xml';
			}
		}

		$xml = $this->parseXmlFile($filePath);
		if (!$xml) {
			return false;
		}

		$data['edition'] = $xml->edition ? (string) $xml->edition : '';
		$data['version'] = $xml->version ? (string) $xml->version : '';

		unset($xml);

		return $data;
	}

	function getSampleDataFileContent($path, $templateName, $onlyExtInfo = false)
	{
		$nameParts = explode('_', $templateName);
		$xml_file = strtolower($nameParts[0].'_'.$nameParts[1]).'_sample_data.xml';
		$path = JPath::clean($path. DIRECTORY_SEPARATOR .$xml_file);

		$xml = $this->parseXmlFile($path);

		if (!$xml) {
			return false;
		}

		$attributes = $xml->attributes();

		$installDataArray = array();
		$installExtArray = array();
		foreach ($xml->extension as $extension)
		{
			$array_backup = array();
			$array_query = array();
			$obj = new stdClass();

			$extAttributes = $extension->attributes();

			$obj->show = false;
			if ($extAttributes['show'] && (string)$extAttributes['show'] === 'true')
			{
				$obj->show = true;
			}

			$attrName = (string) $extAttributes['name'];
			$obj->name           = trim(strtolower($attrName));
			$obj->identifiedName = $extAttributes['identifiedname'] ? (string) $extAttributes['identifiedname'] : '';
			$obj->type           = $extAttributes['type'] ? (string) $extAttributes['type'] : '';
			$obj->version        = $extAttributes['version'] ? (string) $extAttributes['version'] : '';
			$obj->author         = $extAttributes['author'] ? (string) $extAttributes['author'] : '';
			$obj->manifestPath   = $extAttributes['manifest_path'] ? (string) $extAttributes['manifest_path'] : '';
			$obj->description    = $extAttributes['description'] ? (string) $extAttributes['description'] : $obj->name;
			$obj->productUrl     = $extAttributes['producturl'] ? (string) $extAttributes['producturl'] : '';
			$obj->productDesc    = $extAttributes['productdesc'] ? (string) $extAttributes['productdesc'] : '';
			$obj->versionUrl     = $extAttributes['versionurl'] ? (string) $extAttributes['versionurl'] : '';
			$obj->downloadUrl    = $extAttributes['downloadurl'] ? (string) $extAttributes['downloadurl'] : '';
			$obj->hasData        = false;

			if (isset($extension->dependency))
			{
				foreach($extension->dependency->parameter as $parameter)
				{
					$obj->extDep[] = (string) $parameter;
				}
			}

			/* Determine if the extension actually has sample data to be installed */
			if (isset($extension->task))
			{
				$obj->hasData = true;
			}

			if ($obj->type != '')
			{
				$installExtArray[$attrName] = $obj;
			}

			if ($onlyExtInfo === false)
			{
				foreach ($extension->task as $task) {
					$taskAttributes = $task->attributes();
					if ($taskAttributes['name'] == 'dbbackup')
					{
						foreach ($task->parameters as $parameters)
						{
							foreach ($parameters->parameter as $parameter)
							{
								$array_backup[] = (string) $parameter;
							}
						}
					}

					if ($taskAttributes['name'] == 'dbinstall')
					{
						foreach ($task->parameters as $parameters)
						{
							foreach ($parameters->parameter as $parameter)
							{
								$array_query[] = (string) $parameter;
							}
						}
					}
				}

				$obj->backup 	= $array_backup;
				$obj->queries 	= $array_query;
				$installDataArray[$attrName] = $obj;
			}
		}

		unset($xml);

		if ($onlyExtInfo === true)
		{
			return $installExtArray;
		}
		else
		{
			$returnData = array();
			$returnData['version'] = $attributes['version'] ? (string) $attributes['version'] : '';
			$returnData['installed_data'] = $installDataArray;

			return $returnData;
		}
	}
}
