<?php
/**
 * @version		$Id: default_children.php 20196 2011-01-09 02:40:25Z ian $
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
if (count($this->children[$this->category->id]) > 0 && $this->maxLevel != 0) :
?>

<ul class="jsn-infolist <?php if ($jsnUtils->isJoomla3()){ echo 'list-striped list-condensed';}?>">
	<?php foreach($this->children[$this->category->id] as $id => $child) : ?>
	<?php
	if($this->params->get('show_empty_categories') || $child->numitems || count($child->getChildren())) :
	if(!isset($this->children[$this->category->id][$id + 1]))
	{
		$class = ' class="last"';
	}
	?>
	<li<?php echo $class; ?>>
		<?php $class = ''; ?>
		<?php if ($jsnUtils->isJoomla3()): ?>
			<h4 class="item-title">
				<a href="<?php echo JRoute::_(ContactHelperRoute::getCategoryRoute($child->id)); ?>">
				<?php echo $this->escape($child->title); ?>
				</a>

				<?php if ($this->params->get('show_cat_items') == 1) :?>
					<span class="badge badge-info pull-right" title="<?php echo JText::_('COM_CONTACT_CAT_NUM'); ?>"><?php echo $child->numitems; ?></span>
				<?php endif; ?>
			</h4>

			<?php if ($this->params->get('show_subcat_desc') == 1) : ?>
				<?php if ($child->description) : ?>
					<small class="category-desc">
						<?php echo JHtml::_('content.prepare', $child->description, '', 'com_contact.category'); ?>
					</small>
				<?php endif; ?>
			<?php endif; ?>
		<?php else: ?>
			<a class="category" href="<?php echo JRoute::_(ContactHelperRoute::getCategoryRoute($child->id));?>"> <?php echo $this->escape($child->title); ?></a>
			<?php if ($this->params->get('show_subcat_desc') == 1) :?>
			<?php if ($child->description) : ?>
			<div class="category-desc"> <?php echo JHtml::_('content.prepare', $child->description); ?> </div>
			<?php endif; ?>
			<?php endif; ?>
			<?php if ($this->params->get('show_cat_items') == 1) :?>
			<dl>
				<dt> <?php echo JText::_('COM_CONTACT_CAT_NUM'); ?></dt>
				<dd><?php echo $child->numitems; ?></dd>
			</dl>
			<?php endif; ?>
		<?php endif; ?>
		<?php if(count($child->getChildren()) > 0 ) :
				$this->children[$child->id] = $child->getChildren();
				$this->category = $child;
				$this->maxLevel--;
				echo $this->loadTemplate('children');
				$this->category = $child->getParent();
				$this->maxLevel++;
			endif; ?>
	</li>
	<?php endif; ?>
	<?php endforeach; ?>
</ul>
<?php endif;