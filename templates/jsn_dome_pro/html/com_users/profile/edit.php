<?php
/**
 * @version		$Id: edit.php 20206 2011-01-09 17:11:35Z chdemko $
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

$app 		= JFactory::getApplication();
$template 	= $app->getTemplate();
require_once JPATH_THEMES.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'jsn_utils.php';
$jsnUtils   = JSNUtils::getInstance();

//load user_profile plugin language
$lang = JFactory::getLanguage();
$lang->load( 'plg_user_profile', JPATH_ADMINISTRATOR );
?>
<div class="com-user profile-edit <?php echo $this->pageclass_sfx?>">
<?php if ($this->params->get('show_page_heading')) : ?>
	<?php if ($jsnUtils->isJoomla3()): ?><div class="page-header"><?php endif; ?>	
	<h2 class="componentheading"><?php echo $this->escape($this->params->get('page_heading')); ?></h2><?php if ($jsnUtils->isJoomla3()): ?></div><?php endif; ?>
<?php endif; ?>

<form id="member-profile" action="<?php echo JRoute::_('index.php?option=com_users&task=profile.save'); ?>" method="post" class="form-validate <?php if ($jsnUtils->isJoomla3()){echo 'form-horizontal';} ?>" <?php if (!$jsnUtils->isJoomla3()){ echo 'enctype="multipart/form-data"';} ?>>
<?php foreach ($this->form->getFieldsets() as $group => $fieldset):// Iterate through the form fieldsets and display each one.?>
	<?php $fields = $this->form->getFieldset($group);?>
	<?php if (count($fields)):?>
	<fieldset>
		<?php if (isset($fieldset->label)):// If the fieldset has a label set, display it as the legend.?>
		<legend><?php echo JText::_($fieldset->label); ?></legend>
		<?php endif;?>
		<?php if ($jsnUtils->isJoomla3()): ?>
		<?php foreach ($fields as $field):// Iterate through the fields in the set and display them.?>
			<?php if ($field->hidden):// If the field is hidden, just display the input.?>
				<div class="control-group">
					<div class="controls">
						<?php echo $field->input;?>
					</div>
				</div>
			<?php else:?>
				<div class="control-group">
					<div class="control-label">
						<?php echo $field->label; ?>
						<?php if (!$field->required && $field->type != 'Spacer'): ?>
						<span class="optional"><?php echo JText::_('COM_USERS_OPTIONAL'); ?></span>
						<?php endif; ?>
					</div>
					<div class="controls">
						<?php echo $field->input; ?>
					</div>
				</div>
			<?php endif;?>
		<?php endforeach;?>
	<?php else : ?>
		<dl>
		<?php foreach ($fields as $field):// Iterate through the fields in the set and display them.?>
			<div class="jsn-formRow clearafter">
			<?php if ($field->hidden):// If the field is hidden, just display the input.?>
				<?php echo $field->input;?>
			<?php else:?>
				<div class="jsn-formRow-lable">
					<?php echo $field->label; ?>
					<?php if (!$field->required && $field->type!='Spacer'): ?>
					<span class="optional"><?php echo JText::_('COM_USERS_OPTIONAL'); ?></span>
					<?php endif; ?>
				</div>
				<div class="jsn-formRow-input">
					<?php echo $field->input; ?>
				</div>
			<?php endif;?>
			</div>
		<?php endforeach;?>
		</dl>
	<?php endif;?>
	</fieldset>
	<?php endif;?>
<?php endforeach;?>

		<div <?php if ($jsnUtils->isJoomla3()){ echo 'class="form-actions"';} ?>>
			<button type="submit" class="validate <?php if ($jsnUtils->isJoomla3()){ echo 'btn btn-primary';}"><span><?php echo JText::_('JSUBMIT'); ?></span></button>
			<a <?php if ($jsnUtils->isJoomla3()){ echo 'class="btn"';} ?> href="<?php echo JRoute::_(''); ?>" title="<?php echo JText::_('JCANCEL'); ?>"><?php echo JText::_('JCANCEL'); ?></a>
			<input type="hidden" name="option" value="com_users" />
			<input type="hidden" name="task" value="profile.save" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
