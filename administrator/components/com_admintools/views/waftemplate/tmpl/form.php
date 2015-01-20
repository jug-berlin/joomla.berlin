<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die;

// Load the required CSS
F0FTemplateUtils::addCSS('media://com_admintools/css/backend.css');

$this->loadHelper('select');
$editor = JFactory::getEditor();
?>

<div class="ats-ticket-replyarea">
	<form action="index.php" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
		<input type="hidden" name="option" value="com_admintools"/>
		<input type="hidden" name="view" value="waftemplate"/>
		<input type="hidden" name="task" value="save"/>
		<input type="hidden" name="admintools_waftemplate_id"
			   value="<?php echo $this->item->admintools_waftemplate_id ?>"/>
		<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken(); ?>" value="1"/>

		<div class="control-group">
			<label for="key_field" class="control-label">
				<?php echo JText::_('ATOOLS_LBL_REASON_SELECT'); ?>
			</label>

			<div class="controls">
				<?php echo AdmintoolsHelperSelect::reasons($this->item->reason, 'reason', array('all' => 1, 'user-reactivate' => 1)) ?>
			</div>
		</div>

		<div class="control-group">
			<label for="subject_field" class="control-label">
				<?php echo JText::_('COM_ADMINTOOLS_WAFTEMPLATES_FIELD_SUBJECT_LBL'); ?>
			</label>

			<div class="controls">
				<input type="text" class="input-xxlarge" id="subject_field" name="subject"
					   value="<?php echo $this->escape($this->item->subject) ?>"/>
				<span class="help-block"><?php echo JText::_('COM_ADMINTOOLS_WAFTEMPLATES_FIELD_SUBJECT_DESC') ?></span>
			</div>
		</div>

		<div class="control-group">
			<label for="enabled" class="control-label">
				<?php echo JText::_('JPUBLISHED'); ?>
			</label>

			<div class="controls">
				<?php echo JHTML::_('select.booleanlist', 'enabled', null, $this->item->enabled); ?>
			</div>
		</div>

		<div class="control-group">
			<label for="language" class="control-label">
				<?php echo JText::_('COM_ADMINTOOLS_WAFTEMPLATES_FIELD_LANGUAGE_LBL'); ?>
			</label>

			<div class="controls">
				<?php echo AdmintoolsHelperSelect::languages($this->item->language, 'language') ?>
				<span
					class="help-block"><?php echo JText::_('COM_ADMINTOOLS_WAFTEMPLATES_FIELD_LANGUAGE_DESC') ?></span>
			</div>
		</div>

		<div class="control-group">
			<label for="language" class="control-label">
				<?php echo JText::_('COM_ADMINTOOLS_WAFTEMPLATES_FIELD_SENDLIMIT_LBL'); ?>
			</label>

			<div class="controls">
				<input class="input-mini" type="text" size="5" name="email_num"
					   value="<?php echo $this->item->email_num ?>"/>
				<span><?php echo JText::_('COM_ADMINTOOLS_WAFTEMPLATES_NUMFREQ') ?></span>
				<input class="input-mini" type="text" size="5" name="email_numfreq"
					   value="<?php echo $this->item->email_numfreq ?>"/>
				<?php echo AdmintoolsHelperSelect::trsfreqlist('email_freq', array('class' => 'input-small'), $this->item->email_freq) ?>
				<span
					class="help-block"><?php echo JText::_('COM_ADMINTOOLS_WAFTEMPLATES_FIELD_SENDLIMIT_DESC') ?></span>
			</div>
		</div>

		<div class="control-group">
			<label for="template" class="control-label">
				<?php echo JText::_('COM_ADMINTOOLS_WAFTEMPLATES_FIELD_TEMPLATE_LBL'); ?>
			</label>

			<div class="controls">
				<?php echo $editor->display('template', $this->item->template, '97%', '391', '50', '20', false); ?>
				<span class="help-block"><?php echo JText::_('COM_ADMINTOOLS_WAFTEMPLATES_FIELD_TEMPLATE_DESC') ?></span>
                <span class="help-block"><?php echo JText::_('COM_ADMINTOOLS_WAFTEMPLATES_FIELD_TEMPLATE_DESC_2') ?></span>
			</div>
		</div>

	</form>
</div>