<?php defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<jdoc:include type="message" />
<form method="POST" action="index.php?template=<?php echo $this->template; ?>&amp;tmpl=jsn_upgrade&amp;template_style_id=<?php echo $templateStyleId ?>" id="frm-login" name="frm_login" class="install-sample-data-form" autocomplete="off">
	<div id="jsn-wrap-installation-content">
		<div id="jsn-sampledata-login">
			<div class="jsn-install-admin-info">
				<h2><?php echo JText::_('JSN_UPGRADE_HEADING_STEP1'); ?></h2>
				<?php echo JText::_('JSN_UPGRADE_JOOMLA_LOGIN_MES'); ?>
				<p class="clearafter">
					<span><?php echo JText::_('JSN_UPGRADE_USERNAME'); ?><input name="username" id="username" type="text"  onchange="JSNTemplateUpgraderUtil.setNextButtonState(this.form);" onkeyup="JSNTemplateUpgraderUtil.setNextButtonState(this.form);" /></span>
					<span><?php echo JText::_('JSN_UPGRADE_PASSWORD'); ?><input name="password" id="password" type="password"  onchange="JSNTemplateUpgraderUtil.setNextButtonState(this.form);" onkeyup="JSNTemplateUpgraderUtil.setNextButtonState(this.form);" /></span>
				</p>
			</div>
			<hr class="jsn-horizontal-line" />
			<div class="jsn-install-admin-check">
				<div class="jsn-install-admin-navigation">
					<button class="action-submit" type="button" id="jsn-upgrade-next-step-button" name="next_step_button" disabled="disabled" onclick="JSNTemplateUpgraderUtil.disableNextButtonOnSubmit(this, '<?php echo JText::_('JSN_UPGRADE_SUBMITTING_BUTTON'); ?>'); this.form.submit();"><?php echo JText::_('JSN_UPGRADE_NEXT_BUTTON'); ?></button>
				</div>
			</div>
			<input type="hidden" name="task" value="joomla_login" />
			<?php echo JHTML::_('form.token'); ?>
		</div>
	</div>
</form>
<script type="text/javascript">
	window.addEvent("domready", function() {
		$("username").focus();
	});
</script>