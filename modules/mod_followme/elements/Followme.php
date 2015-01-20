<?php

/**
* FollowMe Joomla! Module
*
* @author    herdboy.com
* @copyright Copyright (C) 2007 Herdboy Web Design cc. All rights reserved.
* @license	 GNU/GPL
*/

 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

class JElementFollowme extends JElement
{
	function fetchElement($name, $value, &$node, $control_name)
	{
		$options = array();
		$d = @dir( JPATH_SITE.DS.'modules'.DS.'mod_followme'.DS.'images');
		if($d) {
			$images 	= array();
			$allowable 	= 'gif|jpg|png';
	
			while (false !== ($entry = $d->read())) {
				$img_file = $entry;
				if(is_file( JPATH_SITE.DS.'modules'.DS.'mod_followme'.DS.'images'.DS.$img_file) && substr($entry,0,1) != '.' && strtolower($entry) !== 'index.html' ) {
					if (eregi( $allowable, $img_file )) {
						$options[] = JHTML::_('select.option', $img_file, $img_file, 'id', 'title');
					}
				}
			}
			$d->close();
		}
		return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'id', 'title', $value, $control_name.$name );
	}
}
