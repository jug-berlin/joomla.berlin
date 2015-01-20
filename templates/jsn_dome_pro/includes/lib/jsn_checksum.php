<?php
/**
 * @author    JoomlaShine.com http://www.joomlashine.com
 * @copyright Copyright (C) 2008 - 2011 JoomlaShine.com. All rights reserved.
 * @license   GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 * @version   $Id: jsn_checksum.php 17002 2012-10-13 09:39:19Z tuyetvt $
 */
defined('_JEXEC') or die('Restricted access');
class JSNChecksum
{
	var $_checksum_file_name		= 'template.checksum';
	var $_template_folder_path  	= '';
	var $_template_folder_name 		= '';
	var $_obj_utils					= null;

	function JSNChecksum()
	{
		$this->_setPhysicalTmplInfo();
		require_once($this->_template_folder_path. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_utils.php');
		$this->_setUtilsInstance();
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
	 *
	 */
	function _setPhysicalTmplInfo()
	{
		$template_name 					= explode(DIRECTORY_SEPARATOR, str_replace(array('\includes\lib', '/includes/lib'), '', dirname(__FILE__)));
		$template_name 					= $template_name [count( $template_name ) - 1];
		$path_base 						= str_replace(DIRECTORY_SEPARATOR."templates". DIRECTORY_SEPARATOR .$template_name. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib', "", dirname(__FILE__));
		$this->_template_folder_name    = $template_name;
		$this->_template_folder_path 	= $path_base. DIRECTORY_SEPARATOR .'templates'. DIRECTORY_SEPARATOR .$template_name;
	}

	function _getFileContent($file, $exclude = array('.svn', 'CVS'))
	{
		$data		= array();
		if (!is_file($file)) return $data;
		$content 	= file($file);
		if (!$content) return $data;
		for ($i = 0, $count = count($content); $i < $count; $i++)
		{
			$tmpContent = (string) trim($content[$i]);
			if (!empty($tmpContent))
			{
				$tmp = explode("\t", $tmpContent);
				if (!in_array($tmp[0], $exclude))
				{
					$data [$tmp[0]] = $tmp[1];
				}
			}
		}
		//unset($data[@$this->_checksum_file_name]);
		unset($data['templateDetails.xml']);
		return $data;
	}

	function compare($comparedContent, $comparingContent)
	{
		if (!count($comparedContent) || !count($comparingContent)) return array();
		$data = array('added'=>array(), 'modified'=>array(), 'deleted'=>array());
		foreach ($comparedContent as $key => $value)
		{
			if (isset($comparingContent[$key]))
			{
				if ($comparingContent[$key] != $value)
				{
					$data['modified'][] = $key;
				}
				unset($comparingContent[$key]);
			}
			else
			{
				$data['added'][] = $key;
			}
		}
		if (count($comparingContent))
		{
			foreach ($comparingContent as $key => $value)
			{
				$data['deleted'][] = $key;
			}
		}
		return $data;
	}

}