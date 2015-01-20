<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 *
 * @since 1.3
 */

defined('_JEXEC') or die();

JHtml::_('behavior.framework');

if(!class_exists('AkeebaHelperEscape')) JLoader::import('helpers.escape', JPATH_COMPONENT_ADMINISTRATOR);
?>
<div id="ak-editor" title="<?php echo JText::_('FILTER_EDITOR_TITLE') ?>">
<form>
	<table id="ak_editor_table">
		<tbody>
		<tr>
			<td><label for="ake_driver"><?php echo JText::_('UI-MULTIDB-DRIVER')?></label></td>
			<td>
				<select id="ake_driver" class="ui-widget-content ui-corner-all">
					<option value="mysqli">MySQLi</option>
					<option value="mysql">MySQL (old)</option>
					<option value="sqlsrv">SQL Server</option>
					<option value="sqlazure">Windows Azure SQL</option>
					<option value="postgresql">PostgreSQL</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><label for="ake_host"><?php echo JText::_('UI-MULTIDB-HOST')?></label></td>
			<td><input id="ake_host" type="text" size="40" class="ui-widget-content ui-corner-all" /></td>
		</tr>
		<tr>
			<td><label for="ake_port"><?php echo JText::_('UI-MULTIDB-PORT')?></label></td>
			<td><input id="ake_port" type="text" size="10" class="ui-widget-content ui-corner-all" /></td>
		</tr>
		<tr>
			<td><label for="ake_username"><?php echo JText::_('UI-MULTIDB-USERNAME')?></label></td>
			<td><input id="ake_username" type="text" size="40" class="ui-widget-content ui-corner-all" /></td>
		</tr>
		<tr>
			<td><label for="ake_password"><?php echo JText::_('UI-MULTIDB-PASSWORD')?></label></td>
			<td><input id="ake_password" type="password" size="40" class="ui-widget-content ui-corner-all" /></td>
		</tr>
		<tr>
			<td><label for="ake_database"><?php echo JText::_('UI-MULTIDB-DATABASE')?></label></td>
			<td><input id="ake_database" type="text" size="40" class="ui-widget-content ui-corner-all" /></td>
		</tr>
		<tr>
			<td><label for="ake_prefix"><?php echo JText::_('UI-MULTIDB-PREFIX')?></label></td>
			<td><input id="ake_prefix" type="text" size="10" class="ui-widget-content ui-corner-all" /></td>
		</tr>
		</tbody>
	</table>
</form>
</div>

<div id="dialog" title="<?php echo JText::_('CONFIG_UI_AJAXERRORDLG_TITLE') ?>"></div>

<div class="alert alert-info">
	<strong><?php echo JText::_('CPANEL_PROFILE_TITLE'); ?>: #<?php echo $this->profileid; ?></strong>
	<?php echo $this->profilename; ?>
</div>

<fieldset>
	<div id="ak_list_container">
		<table id="ak_list_table" class="table table-striped">
			<thead>
				<tr>
					<!-- Delete -->
					<td width="20px">&nbsp;</td>
					<!-- Edit -->
					<td width="20px">&nbsp;</td>
					<!-- Database host -->
					<td><?php echo JText::_('MULTIDB_LABEL_HOST') ?></td>
					<!-- Database -->
					<td><?php echo JText::_('MULTIDB_LABEL_DATABASE') ?></td>
				</tr>
			</thead>
			<tbody id="ak_list_contents">
			</tbody>
		</table>
	</div>
</fieldset>

<script type="text/javascript" language="javascript">

akeeba.jQuery(document).ready(function($){
	// Set the AJAX proxy URL
	akeeba_ajax_url = '<?php echo AkeebaHelperEscape::escapeJS(JUri::base().'index.php?option=com_akeeba&view=multidb&task=ajax') ?>';
	// Set the media root
	akeeba_ui_theme_root = '<?php echo $this->mediadir ?>';
	// Create the editor dialog
	$("#ak-editor").dialog({
		autoOpen: false,
		closeOnEscape: true,
		height: 330,
		width: 430,
		hide: 'slide',
		modal: false,
		position: 'center',
		show: 'slide'
	});
	// Create the error dialog
	$("#dialog").dialog({
		autoOpen: false,
		closeOnEscape: false,
		height: 200,
		width: 300,
		hide: 'slide',
		modal: true,
		position: 'center',
		show: 'slide'
	});
	// Create an AJAX error trap
	akeeba_error_callback = function( message ) {
		var dialog_element = $("#dialog");
		dialog_element.html(''); // Clear the dialog's contents
		dialog_element.dialog('option', 'title', '<?php echo AkeebaHelperEscape::escapeJS(JText::_('CONFIG_UI_AJAXERRORDLG_TITLE')) ?>');
		$(document.createElement('p')).html('<?php echo AkeebaHelperEscape::escapeJS(JText::_('CONFIG_UI_AJAXERRORDLG_TEXT')) ?>').appendTo(dialog_element);
		$(document.createElement('pre')).html( message ).appendTo(dialog_element);
		dialog_element.dialog('open');
	};
	// Push translations
	akeeba_translations['UI-ROOT'] = '<?php echo AkeebaHelperEscape::escapeJS(JText::_('FILTERS_LABEL_UIROOT')) ?>';
	akeeba_translations['UI-ERROR-FILTER'] = '<?php echo AkeebaHelperEscape::escapeJS(JText::_('FILTERS_LABEL_UIERRORFILTER')) ?>';
<?php
	$keys = array(
		'UI-MULTIDB-HOST', 'UI-MULTIDB-PORT', 'UI-MULTIDB-USERNAME', 'UI-MULTIDB-PASSWORD',
		'UI-MULTIDB-DATABASE', 'UI-MULTIDB-PREFIX', 'UI-MULTIDB-TEST', 'UI-MULTIDB-SAVE',
		'UI-MULTIDB-CANCEL', 'UI-MULTIDB-LOADING', 'UI-MULTIDB-CONNECTOK',
		'UI-MULTIDB-CONNECTFAIL', 'UI-MULTIDB-SAVEFAIL', 'UI-MULTIDB-DRIVER'
	);
	foreach($keys as $key)
	{
		echo "\takeeba_translations['".$key."'] = '".AkeebaHelperEscape::escapeJS(JText::_($key))."';\n";
	}
?>
	// Bootstrap the page display
	var data = JSON.parse('<?php echo AkeebaHelperEscape::escapeJS($this->json,"'"); ?>');
	multidb_render(data);
});
</script>