<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 * @since     1.3
 */

defined('_JEXEC') or die;

$disabled = ADMINTOOLS_PRO ? '' : 'disabled = "disabled"';

$script = <<<JS

;// This comment is intentionally put here to prevent badly written plugins from causing a Javascript error
// due to missing trailing semicolon and/or newline in their code.
(function($){
	$(document).ready(function(){
		$('#akeeba-postsetup-apply').click(function(e){
			$('#adminForm').submit();
		});
		$('#akeeba-postsetup-acceptandapply').click(function(e){
			$('#acceptlicense').attr('checked','checked');
			$('#acceptsupport').attr('checked','checked');
			$('#adminForm').submit();
		});
	});
})(akeeba.jQuery);

JS;
JFactory::getDocument()->addScriptDeclaration($script);

JHTML::_('behavior.framework', true);
?>
<form action="index.php" method="post" name="adminForm" id="adminForm" class="form">
	<input type="hidden" name="option" value="com_admintools"/>
	<input type="hidden" name="view" value="postsetup"/>
	<input type="hidden" name="task" id="task" value="save"/>
	<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken(); ?>" value="1"/>

	<p class="alert alert-info"><?php echo JText::_('COM_ADMINTOOLS_POSTSETUP_LBL_WHATTHIS'); ?></p>

	<label for="autojupdate" class="postsetup-main">
		<input type="checkbox" id="autojupdate" name="autojupdate"
			   <?php if ($this->enableautojupdate): ?>checked="checked"<?php endif; ?> <?php echo $disabled ?> />
		<?php echo JText::_('COM_ADMINTOOLS_POSTSETUP_LBL_AUTOJUPDATE') ?>
	</label>

	<?php if (ADMINTOOLS_PRO): ?>
		<div class="help-block"><?php echo JText::_('COM_ADMINTOOLS_POSTSETUP_DESC_AUTOJUPDATE2'); ?></div>
	<?php else: ?>
		<div class="help-block"><?php echo JText::_('COM_ADMINTOOLS_POSTSETUP_NOTAVAILABLEINCORE'); ?></div>
	<?php endif; ?>
	<br/>

	<h3><?php echo JText::_('COM_ADMINTOOLS_LBL_MANDATORYINFO') ?></h3>

	<label for="acceptlicense" class="postsetup-main">
		<input type="checkbox" id="acceptlicense" name="acceptlicense"
			   <?php if ($this->acceptlicense): ?>checked="checked"<?php endif; ?> />
		<?php echo JText::_('COM_ADMINTOOLS_POSTSETUP_LBL_ACCEPTLICENSE') ?>
	</label>

	<div class="help-block"><?php echo JText::_('COM_ADMINTOOLS_POSTSETUP_DESC_ACCEPTLICENSE'); ?></div>
	<br/>

	<label for="acceptsupport" class="postsetup-main">
		<input type="checkbox" id="acceptsupport" name="acceptsupport"
			   <?php if ($this->acceptsupport): ?>checked="checked"<?php endif; ?> />
		<?php echo JText::_('COM_ADMINTOOLS_POSTSETUP_LBL_ACCEPTSUPPORT') ?>
	</label>
	</br>
	<div class="help-block"><?php echo JText::_('COM_ADMINTOOLS_POSTSETUP_DESC_ACCEPTSUPPORT'); ?></div>
	<br/>

	<div class="form-actions">
		<button class="btn btn-large btn-primary" id="akeeba-postsetup-apply"
				onclick="return false;"><?php echo JText::_('COM_ADMINTOOLS_POSTSETUP_LBL_APPLY'); ?></button>
		<button id="akeeba-postsetup-acceptandapply" class="btn btn-warning" onclick="return false;">
			<span class="icon icon-white icon-check"></span>
			<?php echo JText::_('COM_ADMINTOOLS_LBL_ACCEPTANDAPPLY'); ?>
		</button>
	</div>
</form>