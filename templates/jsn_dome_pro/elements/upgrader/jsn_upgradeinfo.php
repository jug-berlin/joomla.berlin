<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

echo JText::sprintf('JSN_UPGRADE_BASIC_INFO', 'PRO UNLIMITED');
echo JText::_('JSN_UPGRADE_PRO_IMPORTANT_INFO');
echo JText::_('JSN_UPGRADE_UNLIMITED_BENEFITS');
?>
<form method="POST" action="index.php?template=<?php echo $this->template; ?>&amp;tmpl=jsn_upgrade&amp;template_style_id=<?php echo $templateStyleId ?>" id="frm-upgradeinfo" name="frm_upgradeinfo" class="install-sample-data-form" autocomplete="off">
	<hr class="jsn-horizontal-line" />
	<div class="jsn-install-admin-check">
		<div class="jsn-install-admin-navigation">
			<a class="link-button" href="javascript: void(0);" onclick="document.frm_upgradeinfo.submit();" id="jsn-proceed-button"><?php echo JText::sprintf('JSN_UPGRADE_PROCEED_BUTTON', 'PRO UNLIMITED'); ?></a>
			<h4><a class="link-action" target="_blank" href="<?php echo $buyLink; ?>"><?php echo JText::sprintf('JSN_UPGRADE_BUY_LINK_TEXT', 'PRO UNLIMITED'); ?></a></h4>
		</div>
	</div>
	<input type="hidden" name="task" value="upgrade_proceeded" />
</form>