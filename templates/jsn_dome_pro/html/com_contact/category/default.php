<?php
/**
 * @version		$Id: default.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Joomla.Site
 * @subpackage	com_contacts
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
<?php if (!$jsnUtils->isJoomla3()):
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>
<div class="com-contact <?php echo $this->pageclass_sfx; ?>">
	<div class="contact-category<?php echo $this->pageclass_sfx;?>">
		<?php if ($this->params->def('show_page_heading', 1)) : ?>
		<h2 class="componentheading"> <?php echo $this->escape($this->params->get('page_heading')); ?> </h2>
		<?php endif; ?>
		<?php if($this->params->get('show_category_title', 1)) : ?>
		<h2><?php echo JHtml::_('content.prepare', $this->category->title); ?></h2>
		<?php endif; ?>
		<?php if ($this->params->def('show_description', 1) || $this->params->def('show_description_image', 1)) : ?>
		<div class="contentdescription clearafter">
			<?php if ($this->params->get('show_description_image') && $this->category->getParams()->get('image')) : ?>
			<img src="<?php echo $this->category->getParams()->get('image'); ?>"/>
			<?php endif; ?>
			<?php if ($this->params->get('show_description') && $this->category->description) : ?>
			<?php echo JHtml::_('content.prepare', $this->category->description); ?>
			<?php endif; ?>
		</div>
		<?php endif; ?>
		<?php if (empty($this->items)) : ?>
		<p> <?php echo JText::_('COM_CONTACT_NO_ARTICLES'); ?> </p>
		<?php else : ?>
		<form action="<?php echo JFilterOutput::ampReplace(JFactory::getURI()->toString()); ?>" method="post" name="adminForm" id="adminForm">
			<?php if ($this->params->get('show_pagination_limit')) : ?>
			<div class="jsn-infofilter"> <?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160; <?php echo $this->pagination->getLimitBox(); ?> </div>
			<?php endif; ?>
			<table width="100%" border="0" cellspacing="0" cellpadding="0" class="jsn-infotable">
				<?php if ($this->params->get('show_headings')) : ?>
				<tr class="jsn-tableheader">
					<td class="sectiontableheader jsn-table-column-order" width="10" align="center"><?php echo JText::_('JGLOBAL_NUM'); ?></td>
					<td class="sectiontableheader jsn-table-column-name"><?php echo JHtml::_('grid.sort', 'COM_CONTACT_CONTACT_EMAIL_NAME', 'a.name', $listDirn, $listOrder); ?></td>
					<?php if ($this->params->get('show_country_headings')) : ?>
					<td class="sectiontableheader jsn-table-column-country"><?php echo JHtml::_('grid.sort', 'COM_CONTACT_COUNTRY', 'a.country', $listDirn, $listOrder); ?></td>
					<?php endif; ?>
					<?php if ($this->params->get('show_state_headings')) : ?>
					<td class="sectiontableheader jsn-table-column-state"><?php echo JHtml::_('grid.sort', 'COM_CONTACT_STATE', 'a.state', $listDirn, $listOrder); ?></td>
					<?php endif; ?>
					<?php if ($this->params->get('show_suburb_headings')) : ?>
					<td class="sectiontableheader jsn-table-column-suburb"><?php echo JHtml::_('grid.sort', 'COM_CONTACT_SUBURB', 'a.suburb', $listDirn, $listOrder); ?></td>
					<?php endif; ?>
					<?php if ($this->params->get('show_email_headings')) : ?>
					<td class="sectiontableheader jsn-table-column-email"><?php echo JText::_('JGLOBAL_EMAIL'); ?></td>
					<?php endif; ?>
					<?php if ($this->params->get('show_telephone_headings')) : ?>
					<td class="sectiontableheader jsn-table-column-telephone"><?php echo JText::_('COM_CONTACT_TELEPHONE'); ?></td>
					<?php endif; ?>
					<?php if ($this->params->get('show_mobile_headings')) : ?>
					<td class="sectiontableheader jsn-table-column-mobile"><?php echo JText::_('COM_CONTACT_MOBILE'); ?></td>
					<?php endif; ?>
					<?php if ($this->params->get('show_fax_headings')) : ?>
					<td class="sectiontableheader jsn-table-column-fax"><?php echo JText::_('COM_CONTACT_FAX'); ?></td>
					<?php endif; ?>
					<?php if ($this->params->get('show_position_headings')) : ?>
					<td class="sectiontableheader jsn-table-column-position"><?php echo JHtml::_('grid.sort', 'COM_CONTACT_POSITION', 'a.con_position', $listDirn, $listOrder); ?></td>
					<?php endif; ?>
				</tr>
				<?php endif; ?>
				<?php echo $this->loadTemplate('items'); ?>
			</table>
			<?php if ($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2 && $this->pagination->get('pages.total') > 1)) : ?>
			<div class="jsn-pagination-container"> <?php echo $this->pagination->getPagesLinks(); ?>
				<p class="jsn-pageinfo"><?php echo $this->pagination->getPagesCounter(); ?></p>
			</div>
			<?php endif; ?>
			<div>
				<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
				<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			</div>
		</form>
		<?php endif; ?>
		<?php if (!empty($this->children[$this->category->id])&& $this->maxLevel != 0) : ?>
		<div class="cat-children">
			<h3><?php echo JText::_('JGLOBAL_SUBCATEGORIES') ; ?></h3>
			<?php echo $this->loadTemplate('children'); ?> </div>
		<?php endif; ?>
	</div>
</div>
<?php else : ?>
<div class="contact-category<?php echo $this->pageclass_sfx;?>">
<?php if ($this->params->get('show_page_heading')) : ?>
	<div class="page-header">
		<h1>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h1>
	</div>
<?php endif; ?>
<?php if($this->params->get('show_category_title', 1)) : ?>
<h2>
	<?php echo JHtml::_('content.prepare', $this->category->title, '', 'com_contact.category'); ?>
</h2>
<?php endif; ?>
<?php if ($this->params->def('show_description', 1) || $this->params->def('show_description_image', 1)) : ?>
	<div class="category-desc">
	<?php if ($this->params->get('show_description_image') && $this->category->getParams()->get('image')) : ?>
		<img src="<?php echo $this->category->getParams()->get('image'); ?>"/>
	<?php endif; ?>
	<?php if ($this->params->get('show_description') && $this->category->description) : ?>
		<?php echo JHtml::_('content.prepare', $this->category->description, '', 'com_contact.category'); ?>
	<?php endif; ?>
	<div class="clr"></div>
	</div>
<?php endif; ?>

<?php echo $this->loadTemplate('items'); ?>

<?php if (!empty($this->children[$this->category->id])&& $this->maxLevel != 0) : ?>
<div class="cat-children">
	<h3><?php echo JText::_('JGLOBAL_SUBCATEGORIES'); ?></h3>
	<?php echo $this->loadTemplate('children'); ?>
</div>
<?php endif; ?>
</div>
<?php endif; ?>