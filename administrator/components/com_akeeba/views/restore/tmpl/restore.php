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
?>
<div class="alert">
	<i class="icon-warning-sign"></i>
	<?php echo JText::_('RESTORE_LABEL_DONOTCLOSE'); ?>
</div>

	
<div id="restoration-progress">
	<h3><?php echo JText::_('RESTORE_LABEL_INPROGRESS') ?></h3>
	
	<table class="table table-striped">
		<tr>
			<td width="25%">
				<?php echo JText::_('RESTORE_LABEL_BYTESREAD'); ?>
			</td>
			<td>
				<span id="extbytesin"></span>
			</td>
		</tr>
		<tr>
			<td width="25%">
				<?php echo JText::_('RESTORE_LABEL_BYTESEXTRACTED'); ?>
			</td>
			<td>
				<span id="extbytesout"></span>
			</td>
		</tr>
		<tr>
			<td width="25%">
				<?php echo JText::_('RESTORE_LABEL_FILESEXTRACTED'); ?>
			</td>
			<td>
				<span id="extfiles"></span>
			</td>
		</tr>
	</table>
	
	<div id="response-timer" class="ui-corner-all">
		<div class="color-overlay"></div>
		<div class="text"></div>
	</div>
</div>

<div id="restoration-error" style="display:none">
	<div class="alert alert-error">
		<h3 class="alert-heading"><?php echo JText::_('RESTORE_LABEL_FAILED'); ?></h3>
		<div id="errorframe">
			<p><?php echo JText::_('RESTORE_LABEL_FAILED_INFO'); ?></p>
			<p id="backup-error-message">
			</p>
		</div>
	</div>
</div>

<div id="restoration-extract-ok" style="display:none">
	<div class="alert alert-success">
		<h3 class="alert-heading"><?php echo JText::_('RESTORE_LABEL_SUCCESS'); ?></h3>
		<p>
			<?php echo JText::_('RESTORE_LABEL_SUCCESS_INFO2'); ?>
		</p>
		<p>
			<?php echo JText::_('RESTORE_LABEL_SUCCESS_INFO2B'); ?>
		</p>
	</div>
	<button class="btn btn-large btn-primary" id="restoration-runinstaller" onclick="return false;">
		<i class="icon-share-alt icon-white"></i>
		<?php echo JText::_('RESTORE_LABEL_RUNINSTALLER'); ?>
	</button>
	<button class="btn btn-danger" id="restoration-finalize" onclick="return false;">
		<i class="icon-off icon-white"></i>
		<?php echo JText::_('RESTORE_LABEL_FINALIZE'); ?>
	</button>
</div>

<script type="text/javascript" language="javascript">
	var akeeba_restoration_password = '<?php echo $this->password; ?>';
	var akeeba_restoration_ajax_url = '<?php echo JUri::base() ?>/components/com_akeeba/restore.php';

	(function($){
		$(document).ready(function(){
			pingRestoration();
		});

		$('#restoration-runinstaller').click(runInstaller);
		$('#restoration-finalize').click(finalizeRestoration);
	})(akeeba.jQuery);
</script>