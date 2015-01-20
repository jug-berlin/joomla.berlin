<?php
/**
 * @version		$Id: _item.php 17002 2012-10-13 09:39:19Z tuyetvt $
 * @package		Joomla.Site
 * @subpackage	mod_articles_news
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<div class="jsn-article">
<?php if ($params->get('item_title')) : ?>
	<<?php echo $params->get('item_heading'); ?> class="contentheading <?php if ($jsnUtils->isJoomla3()) {echo 'newsflash-title';}?>">
		<?php if ($params->get('link_titles') && $item->link != '') : ?>
			<a href="<?php echo $item->link;?>"><?php echo $item->title;?></a>
		<?php else : ?>
			<?php echo $item->title; ?>
		<?php endif; ?>
	</<?php echo $params->get('item_heading'); ?>>
<?php endif; ?>
<?php if (!$params->get('intro_only')) : echo $item->afterDisplayTitle; endif; ?>
<?php echo $item->beforeDisplayContent; ?>
<?php echo $item->introtext; ?>
<?php if (isset($item->link) && $item->readmore && $params->get('readmore')) : echo '<a class="readon" href="'.$item->link.'">'.$item->linkText.'</a>'; endif; ?>
</div>