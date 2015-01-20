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

echo '<div style="'.$positions.'">';
echo (!empty($link)) ? '<a href="http://twitter.com/intent/user?screen_name=' . $link . '" target="_blank">' : '';
echo '<img title="'.$title.'" alt="Follow us on Twitter" src="'.$sourceimg.'" border="0" />';
echo (!empty($link)) ? '</a>' : '';
echo '</div>';

?>
