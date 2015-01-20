<?php
/**
 * @version		$Id: default.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Joomla.Site
 * @subpackage	com_newsfeeds
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');

$app 		= JFactory::getApplication();
$template 	= $app->getTemplate();
require_once JPATH_THEMES.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'jsn_utils.php';
$jsnUtils   = JSNUtils::getInstance();
?>
<div class="categories-list<?php echo $this->pageclass_sfx;?>">
<?php if ($this->params->get('show_page_heading', 1)) : ?>
<?php if ($jsnUtils->isJoomla3()): ?>
<div class="page-header">
<?php endif; ?>
<h1>
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php if ($jsnUtils->isJoomla3()): ?>
</div>
<?php endif; ?>
<?php endif; ?>
	<?php if ($this->params->get('show_base_description')) : ?>
	<?php 	//If there is a description in the menu parameters use that; ?>
		<?php if($this->params->get('categories_description')) : ?>
		<div class="category-desc base-desc">
			<?php if ($jsnUtils->isJoomla3()): ?>
			<?php echo  JHtml::_('content.prepare', $this->params->get('categories_description'), '', 'com_contact.categories'); ?>
			<?php else: ?>
			<?php echo  JHtml::_('content.prepare',$this->params->get('categories_description')); ?>
			<?php endif; ?>
			</div>
		<?php  else: ?>
			<?php //Otherwise get one from the database if it exists. ?>
			<?php  if ($this->parent->description) : ?>
				<div class="category-desc base-desc">
				<?php if ($jsnUtils->isJoomla3()): ?>
				<?php  echo JHtml::_('content.prepare', $this->parent->description, '', 'com_contact.categories'); ?>
				<?php else : ?>
				<?php  echo JHtml::_('content.prepare', $this->parent->description); ?>
				<?php endif; ?>
				</div>
			<?php  endif; ?>
		<?php  endif; ?>
	<?php endif; ?>
<?php
echo $this->loadTemplate('items');
?>
</div>