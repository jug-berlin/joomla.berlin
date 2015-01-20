<?php
/**
 * @version		$Id: edit.php 20250 2011-01-10 14:27:02Z chdemko $
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

$app 		= JFactory::getApplication();
$template 	= $app->getTemplate();
require_once JPATH_THEMES.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'jsn_utils.php';
$jsnUtils   = JSNUtils::getInstance();

// Create shortcut to parameters.
$params = $this->state->get('params');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'weblink.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task);
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<div class="edit<?php echo $this->pageclass_sfx; ?>">
<?php if ($this->params->def('show_page_heading', 1)) : ?>
<h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
<?php endif; ?>
<form action="<?php echo JRoute::_('index.php?option=com_weblinks&view=form&w_id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate <?php if ($jsnUtils->isJoomla3()){ echo 'form-vertical'; }">
<?php if (!$jsnUtils->isJoomla3()): ?>
<fieldset>
		<legend><?php echo JText::_('COM_WEBLINKS_LINK'); ?></legend>
		<div class="clearafter">
			<div style=" float: left;">
				<div class="formelm"><span class="field-title"><?php echo $this->form->getLabel('title'); ?></span><?php echo $this->form->getInput('title'); ?> </div>
			</div>
			<div style=" float: right;">
				<div class="formelm-buttons">
					<button type="button" onclick="Joomla.submitbutton('weblink.save')"> <?php echo JText::_('JSAVE') ?> </button>
					<button type="button" onclick="Joomla.submitbutton('weblink.cancel')"> <?php echo JText::_('JCANCEL') ?> </button>
				</div>
			</div>
		</div>
		<div class="formelm"><span class="field-title"><?php echo $this->form->getLabel('catid'); ?></span><?php echo $this->form->getInput('catid'); ?> </div>
		<div class="formelm"><span class="field-title"><?php echo $this->form->getLabel('url'); ?></span><?php echo $this->form->getInput('url'); ?> </div>
		<?php if ($this->user->authorise('core.edit.state', 'com_weblinks.weblink')): ?>
		<div class="formelm"><span class="field-title"><?php echo $this->form->getLabel('state'); ?></span><?php echo $this->form->getInput('state'); ?> </div>
		<?php endif; ?>
		<div class="formelm"><span class="field-title"><?php echo $this->form->getLabel('language'); ?></span><?php echo $this->form->getInput('language'); ?> </div>
		<div class="formelm clearafter">
			<p><?php echo $this->form->getLabel('description'); ?></p>
			<div><?php echo $this->form->getInput('description'); ?></div>
		</div>
	</fieldset>
<?php else : ?>
<div class="btn-toolbar">
			<div class="btn-group">
				<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('weblink.save')">
					<i class="icon-ok"></i> <?php echo JText::_('JSAVE') ?>
				</button>
			</div>
			<div class="btn-group">
				<button type="button" class="btn" onclick="Joomla.submitbutton('weblink.cancel')">
					<i class="icon-cancel"></i> <?php echo JText::_('JCANCEL') ?>
				</button>
			</div>
		</div>

		<hr class="hr-condensed" />
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('title'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('title'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('alias'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('alias'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('catid'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('catid'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('url'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('url'); ?>
			</div>
		</div>
		<?php if ($this->user->authorise('core.edit.state', 'com_weblinks.weblink')): ?>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('state'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('state'); ?>
				</div>
			</div>
		<?php endif; ?>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('language'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('language'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('description'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('description'); ?>
			</div>
		</div>
<?php endif; ?>
	<input type="hidden" name="return" value="<?php echo $this->return_page;?>" />
	<input type="hidden" name="task" value="" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>