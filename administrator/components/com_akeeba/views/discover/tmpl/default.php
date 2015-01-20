<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 *
 * @since 3.2
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

JHtml::_('behavior.framework');
?>
<!-- jQuery & jQuery UI detection. Also shows a big, fat warning if they're missing -->
<div id="nojquerywarning" style="margin: 1em; padding: 1em; background: #ffff00; border: thick solid red; color: black; font-size: 14pt;">
	<h1 style="margin: 1em 0; color: red; font-size: 22pt;"><?php echo JText::_('AKEEBA_CPANEL_WARN_ERROR') ?></h1>
	<p><?php echo JText::_('AKEEBA_CPANEL_WARN_JQ_L1'); ?></p>
	<p><?php echo JText::_('AKEEBA_CPANEL_WARN_JQ_L2'); ?></p>
</div>
<script type="text/javascript" language="javascript">
	if(typeof akeeba.jQuery == 'function')
	{
		if(typeof akeeba.jQuery.ui == 'object')
		{
			akeeba.jQuery('#nojquerywarning').css('display','none');
		}
	}
</script>

<div id="dialog" title="<?php echo JText::_('CONFIG_UI_BROWSER_TITLE') ?>">
</div>

<div class="alert alert-info">
	<?php echo JText::sprintf('DISCOVER_LABEL_S3IMPORT','index.php?option=com_akeeba&view=s3import') ?>
	<br/>
	<a class="btn btn-small" href="index.php?option=com_akeeba&view=s3import">
		<i class="icon-globe"></i>
		<?php echo JText::_('S3IMPORT') ?>
	</a>
</div>

<form name="adminForm" id="adminForm" action="index.php" method="post" class="form-horizontal">
	<input type="hidden" name="option" value="com_akeeba" />
	<input type="hidden" name="view" value="discover" />
	<input type="hidden" name="task" value="discover" />
	<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken()?>" value="1" />
	
	<div class="control-group">
		<label class="control-label">
			<?php echo JText::_('DISCOVER_LABEL_DIRECTORY'); ?>
		</label>
		<div class="controls">
			<input type="text" name="directory" id="directory" value="<?php echo $this->directory ?>" />
			<button class="btn btn-inverse btn-mini" onclick="return false;" id="browserbutton">
				<i class="icon-folder-open icon-white"></i>
				<?php echo JText::_('CONFIG_UI_BROWSE') ?>
			</button>
			<p class="help-block">
				<?php echo JText::_('DISCOVER_LABEL_SELECTDIR') ?>
			</p>
		</div>
	</div>
	<div class="form-actions">
		<button class="btn btn-primary" onclick="this.form.submit(); return false;">
			<?php echo JText::_('DISCOVER_LABEL_SCAN') ?>
		</button>
	</div>
</form>

<script type="text/javascript" language="javascript">
	// Callback routine to close the browser dialog
	var akeeba_browser_callback = null;
	akeeba.jQuery(document).ready(function($){
		// Push some translations
		akeeba_translations['UI-BROWSE'] = '<?php echo AkeebaHelperEscape::escapeJS(JText::_('CONFIG_UI_BROWSE')) ?>';

		// Create the dialog
		$("#dialog").dialog({
			autoOpen: false,
			closeOnEscape: false,
			height: 400,
			width: 640,
			hide: 'slide',
			modal: true,
			position: 'center',
			show: 'slide'
		});

		$('#browserbutton').click(function(el){
			akeeba_browser_hook( $('#directory').val(), $('#directory') );
		});

		// Create the browser hook
		akeeba_browser_hook = function( folder, element )
		{
			var dialog_element = $("#dialog");
			dialog_element.html(''); // Clear the dialog's contents
			dialog_element.removeClass('ui-state-error');
			dialog_element.dialog('option', 'title', '<?php echo AkeebaHelperEscape::escapeJS(JText::_('CONFIG_UI_BROWSER_TITLE')) ?>');

			// URL to load the browser
			var browserSrc = 'index.php?option=com_akeeba&view=browser&processfolder=1&tmpl=component&folder=';
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
	});
</script>