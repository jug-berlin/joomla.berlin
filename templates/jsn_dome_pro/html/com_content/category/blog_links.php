<?php
/**
 * @version		$Id: blog_links.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Joomla.Site
 * @subpackage	com_content
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
<div class="items-more">
<ul class="nav nav-tabs nav-stacked">
<?php else : ?>
<h2><?php echo JText::_('COM_CONTENT_MORE_ARTICLES'); ?></h2>
<ul>
<?php endif; ?>
<?php
	foreach ($this->link_items as &$item) :
?>
	<li>
		<a class="blogsection" href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid)); ?>">
			<?php echo $item->title; ?></a>
	</li>
<?php endforeach; ?>
</ul>
<?php if ($jsnUtils->isJoomla3()): ?>
</div><?php endif; ?>
