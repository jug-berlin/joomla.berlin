<?php
/**
 * @version		$Id: default_separator.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Joomla.Site
 * @subpackage	mod_menu
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
require_once (JPATH_THEMES. DIRECTORY_SEPARATOR .JFactory::getApplication()->getTemplate(). DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_utils.php');
global $jsn_richmenu_separator;
$jsnUtils 	= JSNUtils::getInstance();
$menus		= JSite::getMenu();
$menu 		= $menus->getActive();
$class 		= '';

// Note. It is important to remove spaces between elements.
$title = $item->anchor_title ? 'title="'.$item->anchor_title.'" ' : '';
if ($item->menu_image) {
		$item->params->get('menu_text', 1 ) ? 
		$linktype = '<img src="'.$item->menu_image.'" alt="'.$item->title.'" /><span class="image-title">'.$item->title.'</span> ' :
		$linktype = '<img src="'.$item->menu_image.'" alt="'.$item->title.'" />';
} 
else { $linktype = $item->title;
}

?><a href="javascript: void(0)">
	<span>
		<?php 			
		if ($item->anchor_title) {
			echo '<span class="jsn-menutitle">'.$linktype.'</span>';
			echo '<span class="jsn-menudescription">'.$item->anchor_title.'</span>';		
		} else {
			echo $linktype;
		}
		?>
	</span>
  </a>
