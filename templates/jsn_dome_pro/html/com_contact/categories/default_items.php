<?php
/**
 * @version		$Id: default_items.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Joomla.Site
 * @subpackage	com_newsfeeds
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$app 		= JFactory::getApplication();
$template 	= $app->getTemplate();
require_once JPATH_THEMES.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'jsn_utils.php';
$jsnUtils   = JSNUtils::getInstance();

$class = ' class="first"';
if (count($this->items[$this->parent->id]) > 0 && $this->maxLevelcat != 0) :
?>
<ul <?php if ($jsnUtils->isJoomla3()){ echo 'class="list-striped list-condensed"';} ?>>
<?php foreach($this->items[$this->parent->id] as $id => $item) : ?>
	<?php
	if($this->params->get('show_empty_categories_cat') || $item->numitems || count($item->getChildren())) :
	if(!isset($this->items[$this->parent->id][$id + 1]))
	{
		$class = ' class="last"';
	}
	?>
	<li<?php echo $class; ?>>
	<?php $class = ''; ?>
		<?php if ($jsnUtils->isJoomla3()): ?>
			<h4 class="item-title">
				<a href="<?php echo JRoute::_(ContactHelperRoute::getCategoryRoute($item->id)); ?>">
				<?php echo $this->escape($item->title); ?>
				</a>
	
				<?php if ($this->params->get('show_cat_items_cat') == 1) :?>
					<span class="badge badge-info pull-right" title="<?php echo JText::_('COM_CONTACT_COUNT'); ?>"><?php echo $item->numitems; ?></span>
				<?php endif; ?>
			</h4>
	
			<?php if ($this->params->get('show_subcat_desc_cat') == 1) : ?>
				<?php if ($item->description) : ?>
					<small class="category-desc">
						<?php echo JHtml::_('content.prepare', $item->description, '', 'com_contact.categories'); ?>
					</small>
				<?php endif; ?>
			<?php endif; ?>
		<?php else : ?>
			<a class="category" href="<?php echo JRoute::_(ContactHelperRoute::getCategoryRoute($item->id));?>">
				<?php echo $this->escape($item->title); ?></a>
	
			<?php if ($this->params->get('show_subcat_desc_cat') == 1) :?>
			<?php if ($item->description) : ?>
				<div class="category-desc">
					<?php echo JHtml::_('content.prepare', $item->description); ?>
				</div>
			<?php endif; ?>
	        <?php endif; ?>
	        
			<?php if ($this->params->get('show_cat_items_cat') == 1) :?>
				<dl class="contact-count"><dt>
					<?php echo JText::_('COM_CONTACT_COUNT'); ?></dt>
					<dd><?php echo $item->numitems; ?></dd>
				</dl>
			<?php endif; ?>
		<?php endif; ?>
		<?php if(count($item->getChildren()) > 0) :
			$this->items[$item->id] = $item->getChildren();
			$this->parent = $item;
			$this->maxLevelcat--;
			echo $this->loadTemplate('items');
			$this->parent = $item->getParent();
			$this->maxLevelcat++;
		endif; ?>

	</li>
	<?php endif; ?>
<?php endforeach; ?>
</ul>
<?php endif; ?>