<?php
/**
 * @version		$Id: confirm.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.5
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');

$app 		= JFactory::getApplication();
$template 	= $app->getTemplate();
require_once JPATH_THEMES.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'jsn_utils.php';
$jsnUtils   = JSNUtils::getInstance();
?>
<?php if ($jsnUtils->isJoomla3()): ?>
<div class="reset-confirm<?php echo $this->pageclass_sfx?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
	<h1>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
	<?php endif; ?>

	<form action="<?php echo JRoute::_('index.php?option=com_users&task=reset.confirm'); ?>" method="post" class="form-validate">

		<?php foreach ($this->form->getFieldsets() as $fieldset): ?>
		<p><?php echo JText::_($fieldset->label); ?></p>		<fieldset>
			<dl>
			<?php foreach ($this->form->getFieldset($fieldset->name) as $name => $field): ?>
				<dt><?php echo $field->label; ?></dt>
				<dd><?php echo $field->input; ?></dd>
			<?php endforeach; ?>
			</dl>
		</fieldset>
		<?php endforeach; ?>

		<div>
			<button type="submit" class="validate"><?php echo JText::_('JSUBMIT'); ?></button>
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
<?php else : ?>
<div class="com-user reset-confirm <?php echo $this->pageclass_sfx?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
	<h2 class="componentheading">
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h2>
	<?php endif; ?>

	<form action="<?php echo JRoute::_('index.php?option=com_users&task=reset.confirm'); ?>" method="post" class="form-validate">

		<?php foreach ($this->form->getFieldsets() as $fieldset): ?>
		<p><?php echo JText::_($fieldset->label); ?></p>
			<div class="jsn-formRow clearafter">
			<?php foreach ($this->form->getFieldset($fieldset->name) as $name => $field): ?>
				<div class="jsn-formRow-lable"><?php echo $field->label; ?></div>
				<div class="jsn-formRow-input"><?php echo $field->input; ?></div>
			<?php endforeach; ?>
			</div>
		<?php endforeach; ?>

		<div>
			<button type="submit"><?php echo JText::_('JSUBMIT'); ?></button>
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
<?php endif; ?>