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
<div id="dialog" title="<?php echo JText::_('CONFIG_UI_AJAXERRORDLG_TITLE') ?>"></div>

<div id="browser" title="<?php echo JText::_('CONFIG_UI_BROWSER_TITLE') ?>"></div>

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
					<td width="50px">&nbsp;</td>
					<!-- Directory path -->
					<td rel="popover" data-original-title="<?php echo JText::_('EFF_LABEL_DIRECTORY') ?>"
						data-content="<?php echo JText::_('EFF_LABEL_DIRECTORY_HELP') ?>">
						<?php echo JText::_('EFF_LABEL_DIRECTORY') ?>
					</td>
					<!-- Directory path -->
					<td rel="popover" data-original-title="<?php echo JText::_('EFF_LABEL_VINCLUDEDIR') ?>"
						data-content="<?php echo JText::_('EFF_LABEL_VINCLUDEDIR_HELP') ?>">
						<?php echo JText::_('EFF_LABEL_VINCLUDEDIR') ?>
					</td>
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
	akeeba_ajax_url = '<?php echo AkeebaHelperEscape::escapeJS(JUri::base().'index.php?option=com_akeeba&view=eff&task=ajax') ?>';
	// Set the media root
	akeeba_ui_theme_root = '<?php echo $this->mediadir ?>';
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
	// Create the browser dialog
	$("#browser").dialog({
		autoOpen: false,
		closeOnEscape: false,
		height: 400,
		width: 640,
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

	// Create the browser hook
	akeeba_browser_hook = function( folder, element )
	{
		var dialog_element = $("#browser");
		dialog_element.html(''); // Clear the dialog's contents
		dialog_element.removeClass('ui-state-error');
		dialog_element.dialog('option', 'title', '<?php echo AkeebaHelperEscape::escapeJS(JText::_('CONFIG_UI_BROWSER_TITLE')) ?>');

		// URL to load the browser
		var browserSrc = '<?php echo AkeebaHelperEscape::escapeJS(JUri::base().'index.php?option=com_akeeba&view=browser&tmpl=component&processfolder=1&folder=') ?>';
		browserSrc = browserSrc + encodeURIComponent(folder);

		// IFrame holding the browser
		var akeeba_browser_iframe = $(document.createElement('iframe')).attr({
			'id':			'akeeba_browser_iframe',
			width:			'100%',
			height:			'98%',
			marginWidth		: 0,
			marginHeight	: 0,
			frameBorder		: 0,
			scrolling		: 'auto',
			src				: browserSrc
		});
		akeeba_browser_iframe.appendTo( dialog_element );

		// Close dialog callback (user confirmed the new folder)
		akeeba_browser_callback = function( myFolder ) {
			$(element).val( myFolder );
			dialog_element.dialog('close');
		};

		dialog_element.dialog('open');
	};

	// Enable popovers
	akeeba.jQuery('[rel="popover"]').popover({
		trigger: 'manual',
		animate: false,
		html: true,
		placement: 'bottom',
		template: '<div class="popover akeeba-bootstrap-popover" onmouseover="akeeba.jQuery(this).mouseleave(function() {akeeba.jQuery(this).hide(); });"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"><p></p></div></div></div>'
	})
	.click(function(e) {
		e.preventDefault();
	})
	.mouseenter(function(e) {
		akeeba.jQuery('div.popover').remove();
		akeeba.jQuery(this).popover('show');
	});

	// Bootstrap the page display
	var data = JSON.parse('<?php echo AkeebaHelperEscape::escapeJS($this->json); ?>');
	eff_render(data);
});
</script>