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
<div class="alert alert-warning">
	<span class="icon icon-warning-circle"></span>
	<?php echo JText::_('COM_AKEEBA_DEPRECATED_FEATURE'); ?>
</div>

<form name="adminForm" id="adminForm" action="index.php" method="get" class="form-horizontal">
<input type="hidden" name="option" value="com_akeeba" />
<input type="hidden" name="view" value="stw" />
<input type="hidden" name="task" value="step3" />
<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken()?>" value="1" />

<h3><?php echo JText::_('STW_LBL_STEP2') ?></h3>
	
<p class="help-text"><?php echo JText::_('STW_LBL_STEP2_INTRO');?></p>
	
<div class="control-group">
	<label class="control-label" for="method">
		<?php echo JText::_('STW_LBL_CONNECTION_TYPE')?>
	</label>
	<div class="controls">
		<select id="akeeba_stw_method" name="method" class="input-xlarge">
			<option value="ftp" <?php if($this->opts->method == 'ftp') echo 'selected="selected"' ?>><?php echo JText::_('STW_LBL_CONNECTION_TYPE_FTP') ?></option>
			<option value="ftps" <?php if($this->opts->method == 'ftps') echo 'selected="selected"' ?>><?php echo JText::_('STW_LBL_CONNECTION_TYPE_FTPS') ?></option>
			<option value="sftp" <?php if($this->opts->method == 'sftp') echo 'selected="selected"' ?>><?php echo JText::_('STW_LBL_CONNECTION_TYPE_SFTP') ?></option>
		</select>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="hostname">
		<?php echo JText::_('STW_LBL_CONNECTION_HOST')?>
	</label>
	<div class="controls">
		<input type="text" size="50" name="hostname" class="input-xlarge" value="<?php echo $this->opts->hostname ?>" />
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="port">
		<?php echo JText::_('STW_LBL_CONNECTION_PORT')?>
	</label>
	<div class="controls">
		<input type="text" size="5" id="akeeba-stw-port" name="port" class="input-mini" value="<?php echo $this->opts->port ?>" />
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="username">
		<?php echo JText::_('STW_LBL_CONNECTION_USERNAME')?>
	</label>
	<div class="controls">
		<input type="text" size="50" name="username" id="stwUsername" class="input-xlarge" value="<?php echo $this->opts->username ?>" />
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="password">
		<?php echo JText::_('STW_LBL_CONNECTION_PASSWORD')?>
	</label>
	<div class="controls">
		<input type="password" size="50" name="password" id="stwPassword" class="input-xlarge" value="<?php echo $this->opts->password ?>" />
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="directory">
		<?php echo JText::_('STW_LBL_CONNECTION_DIRECTORY')?>
	</label>
	<div class="controls">
		<input type="text" size="50" name="directory" class="input-xlarge" value="<?php echo $this->opts->directory ?>" />
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="passive">
		<?php echo JText::_('STW_LBL_CONNECTION_PASSIVE')?>
	</label>
	<div class="controls">
		<input type="checkbox" name="passive" <?php if($this->opts->passive) echo 'checked="checked"' ?> />
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="livesite">
		<?php echo JText::_('STW_LBL_CONNECTION_URL')?>
	</label>
	<div class="controls">
		<input type="text" size="50" name="livesite" class="input-xlarge" value="<?php echo $this->opts->livesite ?>" />
	</div>
</div>

<div class="form-actions">
	<button class="btn btn-primary" onclick="this.form.submit(); return false;"><?php echo JText::_('STW_LBL_NEXT') ?></button>
</div>
	
</form>

<script type="text/javascript">
	akeeba.jQuery(document).ready(function($){
		// Work around Safari which ignores autocomplete=off (FOR CRYING OUT LOUD!)
		setTimeout(function(){
			$('#stwUsername').val('<?php echo $this->opts->username ?>');
			$('#stwPassword').val('<?php echo $this->opts->password ?>');
		}, 500);

		// Change the (S)FTP port when switching between the two methods
		$('#akeeba_stw_method').change(function(e){
			var method = $('#akeeba_stw_method').val();
			var port = $('#akeeba-stw-port').val();

			if (method == 'sftp')
			{
				if (port == '21')
				{
					$('#akeeba-stw-port').val('22');
				}
			}
			else
			{
				if (port == '22')
				{
					$('#akeeba-stw-port').val('21');
				}
			}
		});
	});
</script>