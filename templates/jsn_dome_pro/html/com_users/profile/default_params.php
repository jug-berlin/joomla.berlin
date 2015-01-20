<?php
/**
 * @version		$Id: default_params.php 20214 2011-01-09 20:25:57Z chdemko $
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

JLoader::register('JHtmlUsers', JPATH_COMPONENT . '/helpers/html/users.php');
JHtml::register('users.spacer', array('JHtmlUsers','spacer'));
JHtml::register('users.helpsite', array('JHtmlUsers','helpsite'));
JHtml::register('users.templatestyle', array('JHtmlUsers','templatestyle'));
JHtml::register('users.admin_language', array('JHtmlUsers','admin_language'));
JHtml::register('users.language', array('JHtmlUsers','language'));
JHtml::register('users.editor', array('JHtmlUsers','editor'));

?>
<?php $fields = $this->form->getFieldset('params'); ?>
<?php if (count($fields)): ?>
<?php if ($jsnUtils->isJoomla3()): ?>
<fieldset id="users-profile-custom">
	<legend><?php echo JText::_('COM_USERS_SETTINGS_FIELDSET_LABEL'); ?></legend>
	<dl class="dl-horizontal">
	<?php foreach ($fields as $field):
		if (!$field->hidden) :?>
		<dt><?php echo $field->title; ?></dt>
		<dd>
			<?php if (JHtml::isRegistered('users.'.$field->id)):?>
				<?php echo JHtml::_('users.'.$field->id, $field->value);?>
			<?php elseif (JHtml::isRegistered('users.'.$field->fieldname)):?>
				<?php echo JHtml::_('users.'.$field->fieldname, $field->value);?>
			<?php elseif (JHtml::isRegistered('users.'.$field->type)):?>
				<?php echo JHtml::_('users.'.$field->type, $field->value);?>
			<?php else:?>
				<?php echo JHtml::_('users.value', $field->value);?>
			<?php endif;?>
		</dd>
		<?php endif;?>
	<?php endforeach;?>
	</dl>
</fieldset>
<?php else : ?>
<fieldset id="users-profile-custom">
	<legend><?php echo JText::_('COM_USERS_SETTINGS_FIELDIRECTORY_SEPARATORET_LABEL'); ?></legend>
	
	<?php foreach ($fields as $field):?>
		<div class="jsn-formRow clearafter">
			<?php if (!$field->hidden) :?>
			<div class="jsn-formRow-lable"><?php echo $field->title; ?></div>
			<div class="jsn-formRow-input">
				<?php if (JHtml::isRegistered('users.'.$field->id)):?>
					<?php echo JHtml::_('users.'.$field->id, $field->value);?>
				<?php elseif (JHtml::isRegistered('users.'.$field->fieldname)):?>
					<?php echo JHtml::_('users.'.$field->fieldname, $field->value);?>
				<?php elseif (JHtml::isRegistered('users.'.$field->type)):?>
					<?php echo JHtml::_('users.'.$field->type, $field->value);?>
				<?php else:?>
					<?php echo JHtml::_('users.value', $field->value);?>
				<?php endif;?>
			</div>
			<?php endif;?>
		</div>
	<?php endforeach;?>
</fieldset>
<?php endif;?>
<?php endif;?>

