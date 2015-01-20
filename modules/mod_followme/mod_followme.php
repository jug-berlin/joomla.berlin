<?php

/**
* FollowMe Joomla! Module
*
* @author    herdboy.com
* @copyright Copyright (C) 2007 Herdboy Web Design cc. All rights reserved.
* @license	 GNU/GPL
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

$position = $params->get( 'position' );
$img = $params->get( 'img' );
$timg = $params->get( 'timg' );
$title = $params->get( 'title' );
$link = $params->get( 'link' );
$fixscroll = $params->get( 'fixscroll' );
$paddingtop = $params->get( 'paddingtop' );
$paddingbottom = $params->get( 'paddingbottom' );
$paddingleft = $params->get( 'paddingleft' );
$paddingright = $params->get( 'paddingright' );
$zindex = $params->get( 'z-index' );

$imgtoshow = $params->get( 'imgtoshow' );

$sourceimg = ($imgtoshow == 1) ? JURI::base() . 'modules/mod_followme/images/' . $timg : $img ;

if(!defined('_TWITTERICON'))
{
	DEFINE('_TWITTERICON', 1);
	$document =& JFactory::getDocument();
    $document->addScript("http://platform.twitter.com/widgets.js");
    $document->addCustomTag( '<link rel="stylesheet" type="text/css" href="'. JURI::base() . 'modules/mod_followme/style.css" title="default" />' );
}

switch($position)
{
	case 'topleft':
		$positions = "position:".$fixscroll."; top:".$paddingtop."px; left:".$paddingleft."px; z-index:".$zindex.";";
        $sourceimg = 'modules/mod_followme/images/followme_left.png';
	break;
	case 'topright':
		$positions = "position:".$fixscroll."; top:".$paddingtop."px; right:".$paddingright."px; z-index:".$zindex.";";
        $sourceimg = 'modules/mod_followme/images/followme_right.png';
	break;
	case 'bottomleft':
		$positions = "position:".$fixscroll."; bottom:".$paddingbottom."px; left:".$paddingleft."px; z-index:".$zindex.";";
        $sourceimg = 'modules/mod_followme/images/followme_left.png';
	break;
	case 'bottomright':
	default:
		$positions = "position:".$fixscroll."; bottom:".$paddingbottom."px; right:".$paddingright."px; z-index:".$zindex.";";
        $sourceimg = 'modules/mod_followme/images/followme_right.png';
	break;
}

require(JModuleHelper::getLayoutPath('mod_followme'));

?>
