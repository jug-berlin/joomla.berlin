<?php defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<div id="jsn-wrap-installation-content">
	<div id="jsn-auto-updater">
		<h2><?php echo JText::sprintf('JSN_UPGRADE_HEADING_STEP3', 'PRO STANDARD', 'PRO UNLIMITED'); ?></h2>
		<!-- <p><?php echo JText::_('JSN_UPGRADE_SEVERAL_STEP'); ?></p> -->
		<ul>
			<li id="jsn-upgrade-template-li">
				<span id="jsn-upgrade-template-subtitle"><?php echo JText::_('JSN_UPGRADE_UPGRADE_FILES'); ?></span>
				<span id="jsn-upgrade-template"></span>
				<span class="jsn-message" id="jsn-upgrade-template-message"></span>
			</li>
		</ul>
	</div>
	<div id="jsn-upgrade-succesfully-container" class="jsn-updater-display-none">
		<hr class="jsn-horizontal-line" />
		<h3 class="jsn-green-heading"><?php echo JText::_('JSN_UPGRADE_SUCCESS_TITLE'); ?></h3>
		<p><?php echo JText::sprintf('JSN_UPGRADE_SUCCESS_MES', 'PRO UNLIMITED'); ?></p>
		<input type="hidden" id="jsn-pro-template-style-id" value="<?php echo $templateStyleId; ?>" />
	</div>
	<div class="jsn-updater-display-none" id="jsn-upgrade-finish-button-wrapper">
		<button class="action-submit" type="button" id="jsn-upgrade-finish-button" name="jsn_finish_button" onclick="JSNTemplateUpgraderUtil.disableNextButtonOnSubmit(this);"><?php echo JText::_('JSN_UPGRADE_FINISH_BUTTON'); ?></button>
	</div>
</div>

<script type="text/javascript">
	window.addEvent("domready", function() {
		var upgrader = new JSNTemplateUpgrader('<?php echo $this->template; ?>', '<?php echo $templateStyleId; ?>', '<?php echo $frontIndexPath; ?>');
		upgrader.installTemplate();

		$("jsn-upgrade-finish-button").addEvent('click', function() {
			window.top.location = "<?php echo JURI::base() . 'administrator/index.php?option=com_templates&task=style.edit&id=' ?>" + $("jsn-pro-template-style-id").value;
		});
	});
</script>
