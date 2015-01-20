<?php
/**
 * @version		$Id: horizontal.php 17002 2012-10-13 09:39:19Z tuyetvt $
 * @package		Joomla.Site
 * @subpackage	mod_articles_news
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
<?php if (!$jsnUtils->isJoomla3()): ?>
<div class="jsn-mod-newsflash jsn-horizontal-container">
<?php $column_width = 99.9/count($list); ?>
<?php for ($i = 0, $n = count($list); $i < $n; $i ++) :
	$item = $list[$i]; ?>
	<div class="jsn-article-container" style="float: left; width: <?php echo $column_width;?>%;">
	<?php require JModuleHelper::getLayoutPath('mod_articles_news', '_item');

	if ($n > 1 && (($i < $n - 1) || $params->get('showLastSeparator'))) : ?>
	<?php endif; ?>
	</div>
<?php endfor; ?>
</div>
<?php else : ?>
<ul class="newsflash-horiz<?php echo $params->get('moduleclass_sfx'); ?>">
<?php for ($i = 0, $n = count($list); $i < $n; $i ++) :
	$item = $list[$i]; ?>
	<li>
	<?php require JModuleHelper::getLayoutPath('mod_articles_news', '_item');

	if ($n > 1 && (($i < $n - 1) || $params->get('showLastSeparator'))) : ?>

	<span class="article-separator">&#160;</span>

	<?php endif; ?>
	</li>
<?php endfor; ?>
</ul>
<?php endif; ?>