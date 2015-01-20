<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 *
 * @since 1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

JHtml::_('behavior.framework');
if (version_compare(JVERSION, '3.0.0', 'ge')) JHtml::_('formbehavior.chosen');

?>

<div class="alert alert-warning">
	<span class="icon icon-warning-circle"></span>
	<?php echo JText::_('COM_AKEEBA_DEPRECATED_FEATURE'); ?>
</div>

<form name="adminForm" id="adminForm" action="index.php" method="get" class="akeeba-formstyle-reset form-horizontal">
<input type="hidden" name="option" value="com_akeeba" />
<input type="hidden" name="view" value="stw" />
<input type="hidden" name="task" value="step2" />
<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken()?>" value="1" />

<h3>
	<?php echo JText::_('STW_LBL_STEP1') ?>
</h3>
<p class="help-block">
	<?php echo JText::_('STW_LBL_STEP1_INTRO');?>
</p>
<div class="control-group">
	<label class="control-label"></label>
	<div class="controls">
		<?php if($this->stw_profile_id > 0): ?>
		<label class="radio">
			<input type="radio" name="method" value="none" checked="checked" />
			<?php echo JText::_('STW_PROFILE_STW') ?>
		</label>
		<br/>
		<?php endif; ?>
		
		<label class="radio">
			<input type="radio" name="method" value="copyfrom" />
			<?php echo JText::_('STW_PROFILE_COPYFROM') ?>
			<?php echo JHTML::_('select.genericlist', $this->profilelist, 'oldprofile', 'class="advancedSelect"'); ?>
		</label>
		<br/>
		
		<label class="radio">
			<input type="radio" name="method" value="blank" <?php echo ($this->stw_profile_id > 0) ? '' : 'checked="checked"' ?> />
			<?php echo JText::_('STW_PROFILE_BLANK') ?>
		</label>
		<br/>
	</div>
</div>
<div class="form-actions">
	<button class="btn btn-primary" onclick="this.form.submit(); return false;"><?php echo JText::_('STW_LBL_NEXT') ?></button>
</div>
	
</form>