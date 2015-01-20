<!DOCTYPE html>
<html lang="en">
	<head>
	    <meta charset="utf-8" />
	    <title>JoomlaShine Template Upgrade Wizard</title>
		<link rel="stylesheet" href="<?php echo JURI::base(true); ?>/templates/<?php echo $this->template; ?>/admin/css/jsn_admin.css" type="text/css" />
		<link rel="stylesheet" href="<?php echo JURI::base(true); ?>/templates/system/css/system.css" type="text/css" />
		<?php echo JSN_TEMPLATE_JAVASCRIPT_MOOTOOL_CORE; ?>
		<?php echo JSN_TEMPLATE_JAVASCRIPT_MOOTOOL_MORE; ?>
		<script type="text/javascript">
			var joomlaBaseUrl = '<?php echo JUri::base(true) ?>';
			var joomlaTemplateUrl = joomlaBaseUrl + '/templates/<?php echo $this->template?>';
		</script>
		<script type="text/javascript" src="<?php echo JURI::base(true); ?>/templates/<?php echo $this->template; ?>/admin/js/jsn_downloader.js"></script>
		<script type="text/javascript" src="<?php echo JURI::base(true); ?>/templates/<?php echo $this->template; ?>/admin/js/jsn_updater.js"></script>
		<script type="text/javascript" src="<?php echo JURI::base(true); ?>/templates/<?php echo $this->template; ?>/admin/js/jsn_upgrader.js"></script>
	</head>
	<!-- Use same id with install sample data form to reuse CSS styles -->
	<body id="jsn-sampledata">
		<div id="jsn-page">
			<div id="jsn-page_inner1">
				<div id="jsn-page_inner2">
					<h1><?php echo JText::sprintf('JSN_UPGRADE_MAIN_HEADING', $templateManifest['name_uppercase'], 'PRO UNLIMITED'); ?>
						<span class="action-cancel"><a id="jsn-upgrade-cancel" class="link-action" href="javascript: void(0);"><?php echo JText::_('JSN_UPGRADE_CANCEL_TEXT'); ?></a></span>
					</h1>