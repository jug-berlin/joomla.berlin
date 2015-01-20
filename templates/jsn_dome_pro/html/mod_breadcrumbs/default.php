<?php
/**
 * @version		$Id: default.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Joomla.Site
 * @subpackage	mod_breadcrumbs
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$app 		= JFactory::getApplication();
$template 	= $app->getTemplate();
require_once JPATH_THEMES.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'jsn_utils.php';
$jsnUtils   = JSNUtils::getInstance();
?>
<?php if ($jsnUtils->isJoomla3()): ?>
<?php JHtml::_('bootstrap.tooltip'); ?>
<ul class="breadcrumb <?php echo $moduleclass_sfx; ?>">
<?php if ($params->get('showHere', 1))
	{
		echo '<li class="active"><span class="divider"><i class="icon-location" class="hasTooltip" title="' .JText::_('MOD_BREADCRUMBS_HERE').'"></i></span></li>';
	}
?>
<?php for ($i = 0; $i < $count; $i ++) :
	// Workaround for duplicate Home when using multilanguage
	if ($i == 1 && !empty($list[$i]->link) && !empty($list[$i - 1]->link) && $list[$i]->link == $list[$i - 1]->link)
	{
		continue;
	}
	// If not the last item in the breadcrumbs add the separator
	echo '<li>';
	if ($i < $count - 1)
	{
		if (!empty($list[$i]->link)) {
			echo '<a href="'.$list[$i]->link.'" class="pathway">'.$list[$i]->name.'</a>';
		} else {
			echo '<span>';
			echo $list[$i]->name;
			echo '</span>';
		}
		if ($i < $count - 2)
		{
			echo '<span class="divider"></span>';
		}
	}  elseif ($params->get('showLast', 1)) { // when $i == $count -1 and 'showLast' is true
		if($i > 0){
			echo '<span class="divider"></span>';
		}
		echo '<span>';
		echo $list[$i]->name;
		echo '</span>';
	}
	echo '</li>';
endfor; ?>
</ul>	
<?php else: ?>
<span class="breadcrumbs pathway clearafter <?php echo $moduleclass_sfx; ?>">
<?php if ($params->get('showHere', 1))
	{
		echo '<span class="showHere">' .JText::_('MOD_BREADCRUMBS_HERE').'</span>';
	}
?>
<?php for ($i = 0; $i < $count; $i ++) :
	// If not the last item in the breadcrumbs add the separator
	if ($i < $count-1) {
		if(!empty($list[$i]->link)) {
			echo '<a href="'.$list[$i]->link.'"'.($i==0?' class="first">':'>').$list[$i]->name.'</a>';
		} else {
			echo '<span>'.$list[$i]->name.'</span>';
		}
	}  else if ($params->get('showLast', 1)) { // when $i == $count -1 and 'showLast' is true
	    echo '<span class="current">'.$list[$i]->name.'</span>';
	}
endfor; ?>

</span>
<?php endif; ?>