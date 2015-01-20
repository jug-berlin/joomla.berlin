<?php
/**
 * @version		$Id: default_items.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Joomla.Site
 * @subpackage	com_contact
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
<?php 
JHtml::_('behavior.framework');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>
<?php if (empty($this->items)) : ?>
	<p> <?php echo JText::_('COM_CONTACT_NO_ARTICLES'); ?>	 </p>
<?php else : ?>

	<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm">
		<?php if ($this->params->get('filter_field') != 'hide') :?>
			<div class="btn-group">
				<label class="filter-search-lbl element-invisible" for="filter-search"><span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span><?php echo JText::_('COM_CONTACT_'.$this->params->get('filter_field').'_FILTER_LABEL').'&#160;'; ?></label>
				<input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->state->get('list.filter')); ?>" class="inputbox" onchange="document.adminForm.submit();" title="<?php echo JText::_('COM_CONTACT_FILTER_SEARCH_DESC'); ?>" placeholder="<?php echo JText::_('COM_CONTACT_FILTER_SEARCH_DESC'); ?>" />
			</div>
		<?php endif; ?>

		<?php if ($this->params->get('show_pagination_limit')) : ?>
			<div class="display-limit">
				<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
		<?php endif; ?>

		<ul class="category list-striped">
			<?php foreach ($this->items as $i => $item) : ?>

				<?php if (in_array($item->access, $this->user->getAuthorisedViewLevels())) : ?>
					<?php if ($this->items[$i]->state == 0) : ?>
						<li class="system-unpublished cat-list-row<?php echo $i % 2; ?>">
					<?php else: ?>
						<li class="cat-list-row<?php echo $i % 2; ?>" >
					<?php endif; ?>

						<span class="pull-right">
							<?php if ($this->params->get('show_telephone_headings') AND !empty($item->telephone)) : ?>
								<?php echo JTEXT::sprintf('COM_CONTACT_TELEPHONE_NUMBER', $item->telephone); ?><br/>
							<?php endif; ?>

							<?php if ($this->params->get('show_mobile_headings') AND !empty ($item->mobile)) : ?>
									<?php echo JTEXT::sprintf('COM_CONTACT_MOBILE_NUMBER', $item->mobile); ?><br/>
							<?php endif; ?>

							<?php if ($this->params->get('show_fax_headings') AND !empty($item->fax) ) : ?>
								<?php echo JTEXT::sprintf('COM_CONTACT_FAX_NUMBER', $item->fax); ?><br/>
							<?php endif; ?>
					</span>

					<p>
						<strong class="list-title">
							<a href="<?php echo JRoute::_(ContactHelperRoute::getContactRoute($item->slug, $item->catid)); ?>">
								<?php echo $item->name; ?></a>
							<?php if ($this->items[$i]->published == 0): ?>
								<span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
							<?php endif; ?>

						</strong><br/>
						<?php if ($this->params->get('show_position_headings')) : ?>
								<?php echo $item->con_position; ?><br/>
						<?php endif; ?>
						<?php if ($this->params->get('show_email_headings')) : ?>
								<?php echo $item->email_to; ?>
						<?php endif; ?>
						<?php if ($this->params->get('show_suburb_headings') AND !empty($item->suburb)) : ?>
							<?php echo $item->suburb . ', '; ?>
						<?php endif; ?>

						<?php if ($this->params->get('show_state_headings') AND !empty($item->state)) : ?>
							<?php echo $item->state . ', '; ?>
						<?php endif; ?>

						<?php if ($this->params->get('show_country_headings') AND !empty($item->country)) : ?>
							<?php echo $item->country; ?><br/>
						<?php endif; ?>
					</p>
					</li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>

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
		<div>
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		</div>
	</form>
<?php endif; ?>
<?php else : ?>
<?php foreach($this->items as $i => $item) : ?>
<?php if ($this->items[$i]->published == 0) : ?>

<tr class="system-unpublished sectiontableentry<?php echo $i % 2 +1; ?>">
	<?php else: ?>
<tr class="sectiontableentry<?php echo $i % 2 +1; ?>">
	<?php endif; ?>
	<td class="jsn-table-column-order" width="10" align="center"><?php echo $this->pagination->getRowOffset( $i ); ?></td>
	<td class="jsn-table-column-name"><a class="category" href="<?php echo JRoute::_(ContactHelperRoute::getContactRoute($item->slug, $item->catid)); ?>"> <?php echo $item->name; ?></a></td>
	<?php if ($this->params->get('show_country_headings')) : ?>
	<td class="jsn-table-column-country"><?php echo $item->country; ?></td>
	<?php endif; ?>
	<?php if ($this->params->get('show_state_headings')) : ?>
	<td class="jsn-table-column-state"><?php echo $item->state; ?></td>
	<?php endif; ?>
	<?php if ($this->params->get('show_suburb_headings')) : ?>
	<td class="jsn-table-column-suburb"><?php echo $item->suburb; ?></td>
	<?php endif; ?>
	<?php if ($this->params->get('show_email_headings')) : ?>
	<td class="jsn-table-column-email"><?php echo $item->email_to; ?></td>
	<?php endif; ?>
	<?php if ($this->params->get('show_telephone_headings')) : ?>
	<td class="jsn-table-column-telephone"><?php echo $item->telephone; ?></td>
	<?php endif; ?>
	<?php if ($this->params->get('show_mobile_headings')) : ?>
	<td class="jsn-table-column-mobile"><?php echo $item->mobile; ?></td>
	<?php endif; ?>
	<?php if ($this->params->get('show_fax_headings')) : ?>
	<td class="jsn-table-column-fax"><?php echo $item->fax; ?></td>
	<?php endif; ?>
	<?php if ($this->params->get('show_position_headings')) : ?>
	<td class="jsn-table-column-position"><?php echo $item->con_position; ?></td>
	<?php endif; ?>
	</ul>
</tr>
<?php endforeach; ?>
<?php endif; ?>