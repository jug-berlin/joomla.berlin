<?php
/**
 * @version		$Id: default.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

defined('_JEXEC') or die;

$app 		= JFactory::getApplication();
$template 	= $app->getTemplate();
require_once JPATH_THEMES.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'jsn_utils.php';
$jsnUtils   = JSNUtils::getInstance();

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>
<?php if ($jsnUtils->isJoomla3()): ?>
<div class="registration<?php echo $this->pageclass_sfx?>">
<?php if ($this->params->get('show_page_heading')) : ?>
	<div class="page-header">
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	</div>
<?php endif; ?>

	<form id="member-registration" action="<?php echo JRoute::_('index.php?option=com_users&task=registration.register'); ?>" method="post" class="form-validate form-horizontal">
<?php foreach ($this->form->getFieldsets() as $fieldset): // Iterate through the form fieldsets and display each one.?>
	<?php $fields = $this->form->getFieldset($fieldset->name);?>
	<?php if (count($fields)):?>
		<fieldset>
		<?php if (isset($fieldset->label)):// If the fieldset has a label set, display it as the legend.
		?>
			<legend><?php echo JText::_($fieldset->label);?></legend>
		<?php endif;?>
		<?php foreach($fields as $field):// Iterate through the fields in the set and display them.?>
			<?php if ($field->hidden):// If the field is hidden, just display the input.?>
				<?php echo $field->input;?>
			<?php else:?>
				<div class="control-group">
					<div class="control-label">
					<?php echo $field->label; ?>
					<?php if (!$field->required && $field->type != 'Spacer'): ?>
						<span class="optional"><?php echo JText::_('COM_USERS_OPTIONAL');?></span>
					<?php endif; ?>
					</div>
					<div class="controls">
						<?php echo $field->input;?>
					</div>
				</div>
			<?php endif;?>
		<?php endforeach;?>
		</fieldset>
	<?php endif;?>
<?php endforeach;?>
		<div class="form-actions">
			<button type="submit" class="btn btn-primary validate"><?php echo JText::_('JREGISTER');?></button>
			<a class="cancel" href="<?php echo JRoute::_('');?>" title="<?php echo JText::_('JCANCEL');?>"><?php echo JText::_('JCANCEL');?></a>
			<input type="hidden" name="option" value="com_users" />
			<input type="hidden" name="task" value="registration.register" />
			<?php echo JHtml::_('form.token');?>
		</div>
	</form>
</div>
<?php else : ?>
<div class="com-user <?php echo $this->pageclass_sfx?>"> 
	<?php if ($this->params->get('show_page_heading')) : ?>
		<h2 class="componentheading">
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h2>
	<?php endif; ?>

	<form id="member-registration" action="<?php echo JRoute::_('index.php?option=com_users&task=registration.register'); ?>" method="post" class="form-validate" id="josForm" name="josForm">
	
		<?php foreach ($this->form->getFieldsets() as $fieldset): // Iterate through the form fieldsets and display each one.?>
			<?php $fields = $this->form->getFieldset($fieldset->name);?>
			<?php if (count($fields)):?>
					
				<?php foreach($fields as $field):// Iterate through the fields in the set and display them.?>
					<div class="jsn-formRow clearafter">
					<?php if ($field->hidden):// If the field is hidden, just display the input.?>
						<?php echo $field->input;?>
					<?php else:?>
						<div class="jsn-formRow-lable">
						<?php echo $field->label; ?>
						<?php if (!$field->required && (!$field->type == "spacer")): ?>
							<span class="optional"><?php echo JText::_('COM_USERS_OPTIONAL');?></span>
						<?php endif; ?>
						</div>
						<div class="jsn-formRow-input"><?php echo $field->input;?></div>
					<?php endif;?>
					</div>
				<?php endforeach;?>					
			<?php endif;?>
		<?php endforeach;?>

		<div>
			<button type="submit" class="validate"><?php echo JText::_('JREGISTER');?></button>
			<?php echo JText::_('COM_USERS_OR');?>
			<a href="<?php echo JRoute::_('');?>" title="<?php echo JText::_('JCANCEL');?>"><?php echo JText::_('JCANCEL');?></a>
			<input type="hidden" name="option" value="com_users" />
			<input type="hidden" name="task" value="registration.register" />
			<?php echo JHtml::_('form.token');?>
		</div>
	</form>
</div>
<?php endif; ?>