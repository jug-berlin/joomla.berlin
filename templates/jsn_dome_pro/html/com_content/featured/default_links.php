
<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<?php
$app 		= JFactory::getApplication();
$template 	= $app->getTemplate();
require_once JPATH_THEMES.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'jsn_utils.php';
$jsnUtils   = JSNUtils::getInstance();

?>
<?php if ($jsnUtils->isJoomla3()): ?>
<ul class="nav nav-tabs nav-stacked">
<?php foreach ($this->link_items as &$item) : ?>
	<li>
		<a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug)); ?>">
			<?php echo $item->title; ?></a>
	</li>
<?php endforeach; ?>
</ul>
<?php else : ?>
<h2><?php echo JText::_('COM_CONTENT_MORE_ARTICLES'); ?></h2>
<ul>
<?php
	foreach ($this->link_items as &$item) :
?>
	<li>
		<a class="blogsection" href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid)); ?>">
			<?php echo $item->title; ?></a>
	</li>
<?php endforeach; ?>
</ul>
<?php endif; ?>