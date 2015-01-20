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

use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;

JHtml::_('behavior.framework');
?>
<div class="alert alert-warning">
	<span class="icon icon-warning-circle"></span>
	<?php echo JText::_('COM_AKEEBA_DEPRECATED_FEATURE'); ?>
</div>

<form name="adminForm" id="adminForm" action="index.php" method="post" class="akeeba-formstyle-reset form-horizontal">
<input type="hidden" name="option" value="com_akeeba" />
<input type="hidden" name="view" value="backup" />
<input type="hidden" name="returnurl" value="<?php echo Factory::getConfiguration()->get('akeeba.stw.livesite','')?>/installation/index.php" />
<input type="hidden" name="autostart" value="1" />
<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken()?>" value="1" />

<img src="<?php echo Factory::getConfiguration()->get('akeeba.stw.livesite','')?>/akeeba_connection_test.png" onerror="incorrectDirectory()" style="display: none" />

<h3>
	<?php echo JText::_('STW_LBL_STEP3') ?>
</h3>

<p><?php echo JText::_('STW_LBL_STEP3_INTROA');?></p>
	
<p>
	<button class="btn" onclick="document.forms.adminForm.view.value='cpanel'; this.form.submit(); return false;">
		<i class="icon-wrench"></i>
		<?php echo JText::_('STW_LBL_STEP3_LBL_CONTROLPANEL') ?>
	</button>
</p>
	
<p><?php echo JText::_('STW_LBL_STEP3_INTROB');?></p>
	
<p>
	<button class="btn btn-large btn-primary" onclick="this.form.submit(); return false;">
		<i class="icon-road icon-white"></i>
		<?php echo JText::_('STW_LBL_STEP3_LBL_TRANSFER') ?>
	</button>
</p>

	
</form>

<script type="text/javascript" language="javascript">
function incorrectDirectory()
{
	alert('<?php echo JText::_('STW_LBL_CONNECTION_ERR_HOST') ?>');
	history.go(-1);
}
</script>