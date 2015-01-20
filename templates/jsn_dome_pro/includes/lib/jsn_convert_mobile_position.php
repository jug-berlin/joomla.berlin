<?php
/**
 * @author    JoomlaShine.com http://www.joomlashine.com
 * @copyright Copyright (C) 2008 - 2011 JoomlaShine.com. All rights reserved.
 * @license   GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 * @version   $Id: jsn_convert_mobile_position.php 17002 2012-10-13 09:39:19Z tuyetvt $
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

class JSNConvertMobilePosition
{
	var $_template_folder_path  		= '';
	var $_template_folder_name 			= '';
	var $_obj_utils						= null;
	var $_template_edition 				= '';
	var $_template_version 				= '';
	var $_template_name 				= '';
	var $_template_copyright			= '';
	var $_template_author				= '';
	var $_template_author_url			= '';
	var $_mobile_positions				= array();
	var $_desktop_positions				= array();
	var $_default_mapping_positions		= array();
	var $_template_id					= 0;
	var $_is_executed					= false;
	var $_hash							= null;
	var $_cookie_name					= '';

	/**
	 *
	 * Contructor
	 * @param int $id
	 */
	function JSNConvertMobilePosition($id)
	{
		$this->_setPhysicalTmplInfo();
		require_once($this->_template_folder_path. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_utils.php');
		$this->_setUtilsInstance();
		$this->_setTmplInfo();
		$this->_setTemplateID($id);
		$this->_setMobilePosition();
		$this->_setDesktopPosition();
		$this->_setDefaultMappingPosition();
		$this->_setHash();
		$this->_setCookieName();
		$this->_setIsExecuted();
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
		$this->_template_folder_path 	= $path_base . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $template_name;
	}

	/**
	 * Initialize template information variable
	 *
	 */
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
	 *
	 * Set value to template_id
	 * @param $id
	 */
	function _setTemplateID($id)
	{
		$this->_template_id = $id;
	}

	/**
	 *
	 * Set array items to mobile_positions array
	 */
	function _setMobilePosition()
	{
		$this->_mobile_positions = array('logo-m'=>'logo-m', 'content-top-m'=>'content-top-m', 'user-top-m'=>'user-top-m',
											'user-bottom-m'=>'user-bottom-m', 'banner-m'=>'banner-m', 'content-bottom-m'=>'content-bottom-m',
											'mainmenu-m'=>'mainmenu-m', 'footer-m'=>'footer-m');
	}

	/**
	 *
	 * Set array items to desktop_positions array
	 */
	function _setDesktopPosition()
	{
		$this->_desktop_positions = array('logo'=>'logo', 'top'=>'top', 'mainmenu'=>'mainmenu',
											'toolbar'=>'toolbar', 'promo-left'=>'promo-left', 'promo'=>'promo',
											'promo-right'=>'promo-right', 'content-top'=>'content-top',
											'left'=>'left', 'right'=>'right', 'innerleft'=>'innerleft', 'innerright'=>'innerright',
											'breadcrumbs'=>'breadcrumbs', 'user-top'=>'user-top', 'user1'=>'user1',
											'user2'=>'user2', 'mainbody-top'=>'mainbody-top', 'mainbody-bottom'=>'mainbody-bottom',
											'user3'=>'user3', 'user4'=>'user4', 'user-bottom'=>'user-bottom',
											'banner'=>'banner', 'content-bottom'=>'content-bottom', 'user5'=>'user5',
											'user6'=>'user6', 'user7'=>'user7', 'footer'=>'footer', 'bottom'=>'bottom',
											'debug'=>'debug', 'stick-lefttop'=>'stick-lefttop', 'stick-leftmiddle'=>'stick-leftmiddle',
											'stick-leftbottom'=>'stick-leftbottom', 'stick-righttop'=>'stick-righttop',
											'stick-rightmiddle'=>'stick-rightmiddle', 'stick-rightbottom'=>'stick-rightbottom');
	}

	/**
	 *
	 * Set value to hash
	 */
	function _setHash()
	{
		$this->_hash = JApplication::getHash(@$_SERVER['HTTP_USER_AGENT']);
	}

	/**
	 *
	 * Get template style from the table template_styles
	 * @param int $template_id
	 */
	function _getTemplateStyle($template_id)
	{
		$db		= JFactory::getDbo();
		$query	= "SELECT * FROM #__template_styles WHERE id = ". (int) $template_id;
		$db->setQuery($query);
		return $db->loadObject();
	}

	/**
	 *
	 * Set array items to default_mapping_positions array
	 */
	function _setDefaultMappingPosition()
	{
		$tmp_array 	= array();
		$result 	=  $this->_getTemplateStyle($this->_template_id);
		if (!is_null($result))
		{
			$parameters = json_decode($result->params);
			foreach ($this->_mobile_positions as $position)
			{
				if (isset($parameters->{$position}) && $parameters->{$position} != 'none')
				{
					$tmp_array [$position] = $parameters->{$position};
				}
			}
		}
		$this->_default_mapping_positions = $tmp_array;
	}

	/**
	 * Set value to is_executed. If true, then will execute convert position, not otherwise
	 */
	function _setIsExecuted()
	{
		$cookie_value = $this->_getCookieValue();
		if ($cookie_value == 'false' && count($this->_default_mapping_positions))
		{
			$this->_is_executed = true;
		}
	}

	/**
	 *
	 * Set name to cookie
	 */
	function _setCookieName()
	{
		$template_name 	= str_replace('_', '-', JString::strtolower($this->_template_name));
		$cookie_name 	= $template_name.'-check-conversion-position-mobile-status-'.$this->_hash;
		$this->_cookie_name = $cookie_name;
	}

	/**
	 *
	 * Set value to cookie
	 */

	function _setCookieValue($value = 'false')
	{
		@setcookie($this->_cookie_name, $value, time() + 60*60*24*30*12*10, '/' );
	}

	/**
	 *
	 * Get value of a cookie
	 */
	function _getCookieValue()
	{
		if (isset($_COOKIE[$this->_cookie_name]))
		{
			return @$_COOKIE[$this->_cookie_name];
		}
		return 'false';
	}

	/**
	 *
	 * Get all modules in mobile positions
	 */
	function _getAllModulesInMobilePosition()
	{
		$db			= JFactory::getDbo();
		$postions 	= $this->_mobile_positions;
		$postions	= "('".implode("', '", $postions)."')";
		$query		= "SELECT * FROM #__modules WHERE client_id = 0 AND position IN ".$postions;
		$db->setQuery($query);
		$result 	= $db->loadObjectList();
		if (!is_null($result))
		{
			return $result;
		}
		return array();
	}

	/**
	 *
	 * Get all modules in destop positions
	 */
	function _getAllModulesInDesktopPosition()
	{
		$db								= JFactory::getDbo();
		$desktop_positions 				= $this->_desktop_positions;
		$default_mapping_positions 		= $this->_default_mapping_positions;
		$tmp_default_mapping_positions 	= array();
		if (count($default_mapping_positions))
		{
			foreach ($default_mapping_positions as $key => $position)
			{
				if (isset($desktop_positions[$position]))
				{
					unset($desktop_positions[$position]);
				}
			}
			$postions	= "('".implode("', '", $desktop_positions)."')";
			$query		= "SELECT * FROM #__modules WHERE client_id = 0 AND position IN ".$postions;
			$db->setQuery($query);
			$result 	= $db->loadObjectList();
			if (!is_null($result))
			{
				return $result;
			}
			return array();
		}
		return array();
	}

	/**
	 *
	 * Convert mobile position
	 */
	function _convertMobilePosition()
	{
		$default_mapping_positions = $this->_default_mapping_positions;
		$data 		= $this->_getAllModulesInMobilePosition();
		$queries 	= array();
		if (count($data))
		{
			foreach ($data as $item)
			{
				if ($item->params != '' && !is_null(json_decode($item->params)))
				{
					$params = json_decode($item->params);

					if (isset($params->moduleclass_sfx)
					&& strpos($params->moduleclass_sfx, 'display-desktop') === false
					&& strpos($params->moduleclass_sfx, 'display-mobile') === false)
					{
						$params->moduleclass_sfx = trim($params->moduleclass_sfx.' display-mobile');
						$queries [] = "UPDATE #__modules SET position = '".(isset($default_mapping_positions[$item->position])?$default_mapping_positions[$item->position]:$item->position)."', params = '".json_encode($params)."' WHERE client_id = 0 AND id = ".(int) $item->id;
					}
				}
			}
			return $this->_executeQuery($queries);
		}

		return false;
	}

	/**
	 *
	 * Convert desktop position
	 */
	function _convertDesktopPosition()
	{
		$data 		= $this->_getAllModulesInDesktopPosition();
		$queries 	= array();
		if (count($data))
		{
			foreach ($data as $item)
			{
				if ($item->params != '' && !is_null(json_decode($item->params)))
				{
					$params = json_decode($item->params);
					if (isset($params->moduleclass_sfx)
					&& strpos($params->moduleclass_sfx, 'display-desktop') === false
					&& strpos($params->moduleclass_sfx, 'display-mobile') === false)
					{
						$params->moduleclass_sfx = trim($params->moduleclass_sfx.' display-desktop');
						$queries [] = "UPDATE #__modules SET params = '".json_encode($params)."' WHERE client_id = 0 AND id = ".(int) $item->id;
					}
				}
			}
			return $this->_executeQuery($queries);
		}

		return false;
	}

	/**
	 *
	 * Execute the query statements
	 * @param array $queries
	 */
	function _executeQuery($queries)
	{
		$db = JFactory::getDbo();
		if (count($queries))
		{
			foreach ($queries as $query)
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

	/**
	 *
	 * Set logo path
	 */
	function _setLogoPath()
	{
		$queries	= array();
		$result 	= $this->_getTemplateStyle($this->_template_id);
		if (!is_null($result))
		{
			$parameters = json_decode($result->params);
			if ((isset($parameters->logoPath) && $parameters->logoPath == '') || !isset($parameters->logoPath))
			{
				$parameters->logoPath = 'templates/'.JString::strtolower($this->_template_name).'/images/logo.png';
			}
			$queries [] = "UPDATE #__template_styles SET params = '".json_encode($parameters)."' WHERE client_id = 0 AND id = ".(int) $this->_template_id;
			return $this->_executeQuery($queries);
		}
		return false;
	}

	/**
	 *
	 * Execute conversion
	 */
	function convertPosition()
	{
		if ($this->_is_executed)
		{
			$r1 = $this->_convertMobilePosition();
			$r2 = $this->_convertDesktopPosition();
			$r3 = $this->_setLogoPath();
			$this->_setCookieValue('true');
		}
		return true;
	}
}