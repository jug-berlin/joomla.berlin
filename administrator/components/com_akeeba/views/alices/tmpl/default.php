<?php

defined('_JEXEC') or die;

if(empty($this->log)) $this->log = null;

JHtml::_('behavior.framework');
if (version_compare(JVERSION, '3.0.0', 'ge')) JHtml::_('formbehavior.chosen');

JText::script('AKEEBA_ALICE_SUCCESSS');
JText::script('AKEEBA_ALICE_WARNING');
JText::script('AKEEBA_ALICE_ERROR');

AkeebaStrapper::addJSfile('media://com_akeeba/js/stepper.js');
AkeebaStrapper::addJSfile('media://com_akeeba/js/alice.js');

?>
<?php if(count($this->logs)): ?>
	<form name="adminForm" id="adminForm" action="index.php" method="post" class="form-inline">
		<input name="option" value="com_akeeba" type="hidden" />
		<input name="view" value="alices" type="hidden" />
		<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken()?>" value="1" />

		<fieldset>
			<label for="tag"><?php echo JText::_('LOG_CHOOSE_FILE_TITLE'); ?></label>
			<?php echo JHTML::_('select.genericlist', $this->logs, 'log', 'class="advancedSelect"', 'value', 'text', $this->log) ?>

			<button class="btn btn-primary" id="analyze-log" style="display:none">
				<i class="icon-download-alt icon-white"></i>
				<?php echo JText::_('AKEEBA_ALICE_ANALYZE'); ?>
			</button>
		</fieldset>

		<div id="stepper-holder" style="margin-top: 15px">
			<div id="stepper-loading" style="text-align: center;display: none">
				<img src="<?php echo F0FTemplateUtils::parsePath('media://com_akeeba/icons/loading.gif')?>" />
			</div>
			<div id="stepper-progress-pane" style="display: none">
				<div class="alert">
					<i class="icon-warning-sign"></i>
					<?php echo JText::_('BACKUP_TEXT_BACKINGUP'); ?>
				</div>
				<fieldset>
					<legend><?php echo JText::_('ALICE_ANALYZE_LABEL_PROGRESS') ?></legend>
					<div id="stepper-progress-content">
						<div id="stepper-steps">
						</div>
						<div id="stepper-status" class="well">
							<div id="stepper-step"></div>
							<div id="stepper-substep"></div>
						</div>
						<div id="stepper-percentage" class="progress">
							<div class="bar" style="width: 0%"></div>
						</div>
						<div id="response-timer">
							<div class="color-overlay"></div>
							<div class="text"></div>
						</div>
					</div>
					<span id="ajax-worker"></span>
				</fieldset>
			</div>
			<div id="stepper-complete" style="display: none">
			</div>
		</div>
	</form>
<?php else: ?>
	<div class="alert alert-error alert-block">
		<?php echo JText::_('LOG_NONE_FOUND') ?>
	</div>
<?php endif; ?>