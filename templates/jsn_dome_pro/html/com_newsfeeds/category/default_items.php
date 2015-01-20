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

$n = count($this->items);
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>
<?php if ($jsnUtils->isJoomla3()):?>
<?php if (empty($this->items)) : ?>
	<p> <?php echo JText::_('COM_NEWSFEEDS_NO_ARTICLES'); ?></p>
<?php else : ?>

	<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm">
		<?php if ($this->params->get('filter_field') != 'hide') :?>
			<div class="btn-group">
				<label class="filter-search-lbl element-invisible" for="filter-search"><span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span><?php echo JText::_('COM_NEWSFEEDS_'.$this->params->get('filter_field').'_FILTER_LABEL').'&#160;'; ?></label>
				<input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->state->get('list.filter')); ?>" class="inputbox" onchange="document.adminForm.submit();" title="<?php echo JText::_('COM_NEWSFEEDS_FILTER_SEARCH_DESC'); ?>" placeholder="<?php echo JText::_('COM_NEWSFEEDS_FILTER_SEARCH_DESC'); ?>" />
			</div>
		<?php endif; ?>
		<?php if ($this->params->get('show_pagination_limit')) : ?>
			<div class="display-limit">
				<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
		<?php endif; ?>

		<ul class="category list-striped list-condensed">
			<?php foreach ($this->items as $i => $item) : ?>
				<?php if ($this->items[$i]->published == 0) : ?>
					<li class="system-unpublished cat-list-row<?php echo $i % 2; ?>">
				<?php else: ?>
					<li class="cat-list-row<?php echo $i % 2; ?>" >
				<?php endif; ?>
				<?php  if ($this->params->get('show_articles')) : ?>
					<span class="list-hits badge badge-info pull-right">
						<?php echo  JText::sprintf('COM_NEWSFEEDS_NUM_ARTICLES_COUNT', $item->numarticles); ?>
					</span>
				<?php  endif; ?>
				<span class="list pull-left">
					<strong class="list-title">
						<a href="<?php echo JRoute::_(NewsFeedsHelperRoute::getNewsfeedRoute($item->slug, $item->catid)); ?>">
							<?php echo $item->name; ?></a>
					</strong>
				</span>
				<?php if ($this->items[$i]->published == 0): ?>
					<span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
				<?php endif; ?>
				<br />
				<?php  if ($this->params->get('show_link')) : ?>					
					<span class="list pull-left">
							<a href="<?php echo $item->link; ?>"><?php echo $item->link; ?></a>
					</span>
					<br/>
				<?php  endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php // Add pagination links ?>
		<?php if (!empty($this->items)) : ?>
			<?php if (($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->pagination->pagesTotal > 1)) : ?>
				<div class="pagination">
					<?php if ($this->params->def('show_pagination_results', 1)) : ?>
						<p class="counter pull-right">
							<?php echo $this->pagination->getPagesCounter(); ?>
						</p>
					<?php endif; ?>

					<?php echo $this->pagination->getPagesLinks(); ?>
				</div>
			<?php endif; ?>
		<?php  endif; ?>
	</form>
<?php endif; ?>
<?php else : ?>
<?php JHtml::core(); ?>
<?php if (empty($this->items)) : ?>
	<p> <?php echo JText::_('COM_NEWSFEEDS_NO_ARTICLES'); ?>	 </p>
<?php else : ?>

<form action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset class="filters">
	<legend class="hidelabeltxt"><?php echo JText::_('JGLOBAL_FILTER_LABEL'); ?></legend>
	<?php if ($this->params->get('show_pagination_limit')) : ?>
		<div class="display-limit">
			<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
	<?php endif; ?>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	</fieldset>
	<table class="category">
		<?php if ($this->params->get('show_headings')==1) : ?>
		<thead><tr>

				<th class="item-title" id="tableOrdering">
					<?php echo JHtml::_('grid.sort', 'COM_NEWSFEEDS_FEED_NAME', 'a.name', $listDirn, $listOrder); ?>
				</th>


				<?php if ($this->params->get('show_articles')) : ?>
				<th class="item-num-art" id="tableOrdering2">
					<?php echo JHtml::_('grid.sort', 'COM_NEWSFEEDS_NUM_ARTICLES', 'a.numarticles', $listDirn, $listOrder); ?>
				</th>
				<?php endif; ?>

				<?php if ($this->params->get('show_link')) : ?>
				<th class="item-link" id="tableOrdering3">
					<?php echo JHtml::_('grid.sort', 'COM_NEWSFEEDS_FEED_LINK', 'a.link', $listDirn, $listOrder); ?>
				</th>
				<?php endif; ?>

			</tr>
		</thead>
		<?php endif; ?>

		<tbody>
			<?php foreach ($this->items as $i => $item) : ?>
		<?php if ($this->items[$i]->published == 0) : ?>
			<tr class="system-unpublished cat-list-row<?php echo $i % 2; ?>">
		<?php else: ?>
			<tr class="cat-list-row<?php echo $i % 2; ?>" >
		<?php endif; ?>

					<td class="item-title">
						<a href="<?php echo JRoute::_(NewsFeedsHelperRoute::getNewsfeedRoute($item->slug, $item->catid)); ?>">
							<?php echo $item->name; ?></a>
					</td>

					<?php  if ($this->params->get('show_articles')) : ?>
						<td class="item-num-art">
							<?php echo $item->numarticles; ?>
						</td>
					<?php  endif; ?>

					<?php  if ($this->params->get('show_link')) : ?>
						<td class="item-link">
							<a href="<?php echo $item->link; ?>"><?php echo $item->link; ?></a>
						</td>
					<?php  endif; ?>

				</tr>

			<?php endforeach; ?>

		</tbody>
	</table>

	<?php if ($this->params->get('show_pagination')) : ?>
	<div class="pagination">
	<?php if ($this->params->def('show_pagination_results', 1)) : ?>
		<p class="counter">
			<?php echo $this->pagination->getPagesCounter(); ?>
		</p>
	<?php endif; ?>
	<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
	<?php endif; ?>

</form>
<?php endif; ?>
<?php endif; ?>