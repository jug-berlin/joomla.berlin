<?php
/**
 * @version		$Id: default_items.php 13471 2009-11-12 00:38:49Z eddieajau
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$app 		= JFactory::getApplication();
$template 	= $app->getTemplate();
require_once JPATH_THEMES.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'jsn_utils.php';
$jsnUtils   = JSNUtils::getInstance();

// Code to support edit links for weblinks
// Create a shortcut for params.
$params = &$this->item->params;
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
// Get the user object.
$user = JFactory::getUser();
// Check if user is allowed to add/edit based on weblinks permissinos.
$canEdit = $user->authorise('core.edit', 'com_weblinks');
$canCreate = $user->authorise('core.create', 'com_weblinks');
$canEditState = $user->authorise('core.edit.state', 'com_weblinks');

$n = count($this->items);
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>
<?php if (!$jsnUtils->isJoomla3()): ?>
<?php if (empty($this->items)) : ?>

<p> <?php echo JText::_('COM_WEBLINKS_NO_WEBLINKS'); ?></p>
<?php else : ?>
<form action="<?php echo JFilterOutput::ampReplace(JFactory::getURI()->toString()); ?>" method="post" name="adminForm" id="adminForm">
	<?php if ($this->params->get('show_pagination_limit')) : ?>
	<div class="jsn-infofilter">
		<?php
		echo JText::_('JGLOBAL_DISPLAY_NUM') .'&nbsp;';
		echo $this->pagination->getLimitBox();
	?>
	</div>
	<?php endif; ?>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="jsn-infotable">
		<?php if ( $this->params->get('show_headings')==1) : ?>
		<tr class="jsn-tableheader">
			<td class="sectiontableheader jsn-table-column-order" width="10" align="center"><?php echo JText::_('JGLOBAL_NUM'); ?></td>
			<td class="sectiontableheader jsn-table-column-details"><?php echo JHtml::_('grid.sort',  'COM_WEBLINKS_GRID_TITLE', 'title', $listDirn, $listOrder); ?></td>
			<?php if ( $this->params->get( 'show_link_hits' ) ) : ?>
			<td class="sectiontableheader jsn-table-column-hits" width="10" align="center" ><?php echo JHtml::_('grid.sort',  'JGLOBAL_HITS', 'hits', $listDirn, $listOrder); ?></td>
			<?php endif; ?>
		</tr>
		<?php endif; ?>
		<?php foreach ($this->items as $i => $item) : ?>
		<?php if ($this->items[$i]->state == 0) : ?>
		<tr class="system-unpublished sectiontableentry<?php echo $i % 2 +1; ?>">
			<?php else: ?>
		<tr class="sectiontableentry<?php echo $i % 2 +1; ?>">
			<?php endif; ?>
			<td class="jsn-table-column-order" width="10" align="center"><?php echo $this->pagination->getRowOffset( $i ); ?></td>
			<td class="jsn-table-column-details">
				<?php if ($this->params->get('icons') == 0) : ?>
					<?php echo JText::_('COM_WEBLINKS_LINK'); ?>
				<?php elseif ($this->params->get('icons') == 1) : ?>
					<?php if (!$this->params->get('link_icons')) : ?>
						<?php echo JHtml::_('image','system/'.$this->params->get('link_icons', 'weblink.png'), JText::_('COM_WEBLINKS_LINK'), NULL, true); ?>
					<?php else: ?>
						<?php echo '<img src="'.$this->params->get('link_icons').'" alt="'.JText::_('COM_WEBLINKS_LINK').'" />'; ?>
					<?php endif; ?>
				<?php endif; ?>
				<?php
						// Compute the correct link
						$menuclass = 'category'.$this->pageclass_sfx;
						$link = $item->link;
						$width	= $item->params->get('width');
						$height	= $item->params->get('height');
						if ($width == null || $height == null) {
							$width	= 600;
							$height	= 500;
						}

						switch ($item->params->get('target', $this->params->get('target')))
						{
							case 1:
								// open in a new window
								echo '<a href="'. $link .'" target="_blank" class="'. $menuclass .'" rel="nofollow">'.
									$this->escape($item->title) .'</a>';
								break;

							case 2:
								// open in a popup window
								$attribs = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width='.$this->escape($width).',height='.$this->escape($height).'';
								echo "<a href=\"$link\" onclick=\"window.open(this.href, 'targetWindow', '".$attribs."'); return false;\">".
									$this->escape($item->title).'</a>';
								break;
							case 3:
								// open in a modal window
								JHtml::_('behavior.modal', 'a.modal'); ?>
				<a class="modal" href="<?php echo $link;?>"  rel="{handler: 'iframe', size: {x:<?php echo $this->escape($width);?>, y:<?php echo $this->escape($height);?>}}"> <?php echo $this->escape($item->title);?> </a>
				<?php
								break;

							default:
								// open in parent window
								echo '<a href="'.  $link . '" class="'. $menuclass .'" rel="nofollow">'.
									$this->escape($item->title) . ' </a>';
								break;
						}
					?>
				<?php // Code to add the edit link for the weblink. ?>
				<?php if ($canEdit) : ?>
				<ul class="actions">
					<li class="edit-icon"> <?php echo JHtml::_('icon.edit',$item, $params); ?> </li>
				</ul>
				<?php endif; ?>
				<br />
				<?php if (($this->params->get('show_link_description')) AND ($item->description !='')): ?>
				<div class="description"><?php echo nl2br($item->description); ?></div>
				<?php endif; ?></td>
			<?php if ( $this->params->get( 'show_link_hits' ) ) : ?>
			<td class="jsn-table-column-hits" width="10" align="center"><?php echo $item->hits; ?></td>
			<?php endif; ?>
		</tr>
		<?php endforeach; ?>
	</table>
	<?php // Code to add a link to submit a weblink. ?>
	<?php /* if ($canCreate) : // TODO This is not working due to some problem in the router, I think. Ref issue #23685 ?>
		<?php echo JHtml::_('icon.create', $item, $item->params); ?>
 	<?php  endif; */ ?>
	<?php if ($this->params->def('show_pagination_results', 2) == 1  || ($this->params->get('show_pagination') == 2 && $this->pagination->get('pages.total') > 1)) : ?>
	<div class="jsn-pagination-container"> <?php echo $this->pagination->getPagesLinks(); ?>
		<p class="jsn-pageinfo"><?php echo $this->pagination->getPagesCounter(); ?></p>
	</div>
	<?php endif; ?>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
</form>
<?php endif; ?>
<?php else : ?>
<?php if (empty($this->items)) : ?>
	<p> <?php echo JText::_('COM_WEBLINKS_NO_WEBLINKS'); ?></p>
<?php else : ?>
<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm">
		<?php if ($this->params->get('filter_field') != 'hide') :?>
			<div class="btn-group">
				<label class="filter-search-lbl element-invisible" for="filter-search"><span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span><?php echo JText::_('COM_WEBLINKS_'.$this->params->get('filter_field').'_FILTER_LABEL').'&#160;'; ?></label>
				<input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->state->get('list.filter')); ?>" class="inputbox" onchange="document.adminForm.submit();" title="<?php echo JText::_('COM_WEBLINKS_FILTER_SEARCH_DESC'); ?>" placeholder="<?php echo JText::_('COM_WEBLINKS_FILTER_SEARCH_DESC'); ?>" />
			</div>
		<?php endif; ?>

		<?php if ($this->params->get('show_pagination_limit')) : ?>
			<div class="display-limit">
				<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
		<?php endif; ?>

		<?php if ($this->params->get('filter_field') != 'hide') :?>
			</fieldset>
		<?php endif; ?>
		<ul class="category list-striped list-condensed">

			<?php foreach ($this->items as $i => $item) : ?>
				<?php if (in_array($item->access, $this->user->getAuthorisedViewLevels())) : ?>
					<?php if ($this->items[$i]->state == 0) : ?>
						<li class="system-unpublished cat-list-row<?php echo $i % 2; ?>">
					<?php else: ?>
						<li class="cat-list-row<?php echo $i % 2; ?>" >
					<?php endif; ?>
					<?php if ($this->params->get('list_show_hits', 1)) : ?>
						<span class="list-hits badge badge-info pull-right">
							<?php echo JText::sprintf('JGLOBAL_HITS_COUNT', $item->hits); ?>
						</span>
					<?php endif; ?>

					<?php if ($canEdit) : ?>
						<span class="list-edit pull-left width-50">
							<?php echo JHtml::_('icon.edit', $item, $params); ?>
						</span>
					<?php endif; ?>

					<strong class="list-title">
						<?php if ($this->params->get('icons') == 0) : ?>
							 <?php echo JText::_('COM_WEBLINKS_LINK'); ?>
						<?php elseif ($this->params->get('icons') == 1) : ?>
							<?php if (!$this->params->get('link_icons')) : ?>
								<?php echo JHtml::_('image', 'system/'.$this->params->get('link_icons', 'weblink.png'), JText::_('COM_WEBLINKS_LINK'), null, true); ?>
							<?php else: ?>
								<?php echo '<img src="'.$this->params->get('link_icons').'" alt="'.JText::_('COM_WEBLINKS_LINK').'" />'; ?>
							<?php endif; ?>
						<?php endif; ?>
						<?php
							// Compute the correct link
							$menuclass = 'category'.$this->pageclass_sfx;
							$link = $item->link;
							$width	= $item->params->get('width');
							$height	= $item->params->get('height');
							if ($width == null || $height == null) {
								$width	= 600;
								$height	= 500;
							}
							if ($this->items[$i]->state == 0): ?>
								<span class="label label-warning">Unpublished</span>
							<?php endif; ?>

							<?php switch ($item->params->get('target', $this->params->get('target')))
							{
								case 1:
									// open in a new window
									echo '<a href="'. $link .'" target="_blank" class="'. $menuclass .'" rel="nofollow">'.
										$this->escape($item->title) .'</a>';
									break;

								case 2:
									// open in a popup window
									$attribs = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width='.$this->escape($width).',height='.$this->escape($height).'';
									echo "<a href=\"$link\" onclick=\"window.open(this.href, 'targetWindow', '".$attribs."'); return false;\">".
										$this->escape($item->title).'</a>';
									break;
								case 3:
									// open in a modal window
									JHtml::_('behavior.modal', 'a.modal'); ?>
									<a class="modal" href="<?php echo $link;?>"  rel="{handler: 'iframe', size: {x:<?php echo $this->escape($width);?>, y:<?php echo $this->escape($height);?>}}">
										<?php echo $this->escape($item->title). ' </a>';
									break;

								default:
									// open in parent window
									echo '<a href="'.  $link . '" class="'. $menuclass .'" rel="nofollow">'.
										$this->escape($item->title) . ' </a>';
									break;
							}
						?>
						</strong>
						<?php if (($this->params->get('show_link_description')) and ($item->description != '')): ?>
							<?php echo $item->description; ?>
						<?php endif; ?>

						</li>
				<?php endif;?>
			<?php endforeach; ?>
		</ul>

		<?php // Code to add a link to submit a weblink. ?>
		<?php /* if ($canCreate) : // TODO This is not working due to some problem in the router, I think. Ref issue #23685 ?>
			<?php echo JHtml::_('icon.create', $item, $item->params); ?>
		<?php  endif; */ ?>
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