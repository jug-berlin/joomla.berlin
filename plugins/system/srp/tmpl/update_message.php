<?php defined('_JEXEC') or die(); ?>
<?php if(version_compare(JVERSION, '3.0', 'gt')): ?>
<div id="akeebasrpwarning">
	<p class="well">
		<img src="../media/com_akeeba/icons/akeeba-16.png" align="bottom" />
		<em>
			<?php echo JText::_('INSTALLER_ENHANCEDBY');?> <a href="https://www.AkeebaBackup.com">Akeeba Backup</a>
		</em>
		&bull;
		<a href="index.php?option=com_installer&view=update&skipsrp=1"><?php echo JText::_('INSTALLER_SWITCHTOREGULAR') ?></a>
	</p>

	<div class="alert">
		<h4><?php echo JText::_('INSTALLER_WHATSTHIS') ?></h4>
		<?php echo JText::_('INSTALLER_WHATSTHIS_TEXT') ?>
	</div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function() {
		var myDiv = jQuery('#akeebasrpwarning');
		myDiv.insertAfter(jQuery('#filter-bar'));
	});
</script>
<?php else: ?>
<div id="akeebasrpwarning">
	<div class="width-70">
		<p>
			<img src="../media/com_akeeba/icons/akeeba-16.png" align="bottom" />
			<em>
				<?php echo JText::_('INSTALLER_ENHANCEDBY');?> <a href="https://www.AkeebaBackup.com">Akeeba Backup</a>
			</em>
			&bull;
			<a href="index.php?option=com_installer&view=update&skipsrp=1"><?php echo JText::_('INSTALLER_SWITCHTOREGULAR') ?></a>
		</p>

		<fieldset id="whatsthis">
			<legend><?php echo JText::_('INSTALLER_WHATSTHIS') ?></legend>
			<?php echo JText::_('INSTALLER_WHATSTHIS_TEXT') ?>
		</fieldset>
	</div>
</div>
<script type="text/javascript">
	window.addEvent('domready', function() {
		var myDiv = document.id('akeebasrpwarning');
		myDiv.inject(document.id('adminForm'), 'before');
	});
</script>
<?php endif; ?>
</div>