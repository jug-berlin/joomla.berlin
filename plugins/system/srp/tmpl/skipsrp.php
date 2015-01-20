<?php
defined('_JEXEC') or die;

if (version_compare(JVERSION, '3.2.0', 'ge'))
{
	$showJedAndWebInstaller = JComponentHelper::getParams('com_installer')->get('show_jed_info', 1);

	JPluginHelper::importPlugin('installer');

	$dispatcher = JDispatcher::getInstance();
	$dispatcher->trigger('onInstallerBeforeDisplay', array(&$showJedAndWebInstaller, $this));
}
?>
<?php if(version_compare(JVERSION, '3.0', 'gt')): ?>
	<p class="well">
		<em>
			<?php echo JText::_('INSTALLER_ENHANCEDBY_DISABLED');?>
		</em>
		&bull;
		<a href="index.php?option=com_installer&skipsrp=0"><?php echo JText::_('INSTALLER_SWITCHTOENHANCED') ?></a>
	</p>

	<div class="alert alert-info" style="text-align: left;">
		<h4><?php echo JText::_('INSTALLER_WHATSTHIS') ?></h4>
		<?php echo JText::_('INSTALLER_WHATSTHIS_DISABLED_TEXT') ?>
	</div>
	<?php if ($showJedAndWebInstaller): ?>
		<div class="alert alert-info j-jed-message" style="margin-bottom: 40px; line-height: 2em; color:#333333;">
			<a href="index.php?option=com_config&view=component&component=com_installer&path=&return=<?php echo urlencode(base64_encode(JUri::getInstance())); ?>" class="close hasTooltip" data-dismiss="alert" title="<?php echo str_replace('"', '&quot;', JText::_('COM_INSTALLER_SHOW_JED_INFORMATION_TOOLTIP')); ?>">&times;</a>
			<p><?php echo JText::_('COM_INSTALLER_INSTALL_FROM_WEB_INFO'); ?>&nbsp;&nbsp;<?php echo JText::_('COM_INSTALLER_INSTALL_FROM_WEB_TOS'); ?></p>
			<input class="btn" type="button" value="<?php echo JText::_('COM_INSTALLER_INSTALL_FROM_WEB_ADD_TAB'); ?>" onclick="Joomla.submitbuttonInstallWebInstaller()" />
		</div>
	<?php endif; ?>

<?php else: ?>
	<div class="width-70 fltlft">
		<p>
			<em>
				<?php echo JText::_('INSTALLER_ENHANCEDBY_DISABLED');?>
			</em>
			&bull;
			<a href="index.php?option=com_installer&skipsrp=0"><?php echo JText::_('INSTALLER_SWITCHTOENHANCED') ?></a>
		</p>

		<fieldset id="whatsthis">
			<legend><?php echo JText::_('INSTALLER_WHATSTHIS') ?></legend>
			<?php echo JText::_('INSTALLER_WHATSTHIS_DISABLED_TEXT') ?>
		</fieldset>

	</div>
<?php endif; ?>