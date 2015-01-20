<?php
/*------------------------------------------------------------------------
# JSN Template Framework
# ------------------------------------------------------------------------
# author    JoomlaShine.com Team
# copyright Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
# Websites: http://www.joomlashine.com
# Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
# @license - GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
# @version $Id: jsn_installsampledata.php 13872 2012-07-10 10:02:25Z ngocpm $
-------------------------------------------------------------------------*/

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once('includes'. DIRECTORY_SEPARATOR .'jsn_defines.php');
require_once('includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_utils.php');
require_once('includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_backup.php');
require_once('includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_readxmlfile.php');
require_once('includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_sampledata_helper.php');

global $error;
$obj_read_xml_file     = new JSNReadXMLFile();
$obj_utils             = new JSNUtils();
$obj_sampledata_helper = new JSNSampleDataHelper();
$obj_sampledata_helper->setSampleDataURL();
$backup_obj            = JSNBackup::getInstance();

$template_manifest = $obj_read_xml_file->getTemplateManifestFileInformation();
$joomla_version    = $obj_utils->getJoomlaVersion();
$folderWritable    = $obj_sampledata_helper->checkFolderPermission();

//////////////////////////////////////MAIN//////////////////////////////////////
$folderFailList	= '';
if (count($folderWritable) > 0)
{
	$folderFailList = '<div id="jsn-li-folder-perm-failed">'
		. JText::_('JSN_SAMPLE_DATA_FOLDER_PERMISSION_MES')
		. '<ul>';
	foreach ($folderWritable as $failed)
	{
		$folderFailList .= '<li>' . $failed . '</li>';
	}
	$folderFailList .= '</ul>'
		. JText::_('JSN_SAMPLE_DATA_FOLDER_PERMISSION_MES2')
		. '<div class="jsn-install-admin-navigation"><hr class="jsn-horizontal-line" />'
			. '<button class="action-submit" type="button" id="jsn-perm-try-again" onclick="JSNSampleData.checkFolderPermission(\''.strtolower($template_manifest['full_name']).'\',\''.JURI::root().'\');">'
		. JText::_('JSN_SAMPLE_DATA_FOLDER_PERMISSION_TRY_AGAIN_BUTTON')
		. '</button></div></div>';
}

$frontIndexPath = JURI::root() . $obj_utils->determineFrontendIndex();

$strLiveDemoLink  = 'http://demo.joomlashine.com/joomla-templates/'.$template_manifest['name'].'/index.php';
$identifier       = md5('state_installation_'.strtolower($template_manifest['full_name']));
$login_identifier = md5('state_login_'.strtolower($template_manifest['full_name']));
$session          = JFactory::getSession();

$processList     = $session->get('jsn-download-process-list', array(), 'jsntemplatesession');
if (!empty($processList)) {
	foreach ($processList as $process) {
		$keyFile = JPATH_ROOT . DIRECTORY_SEPARATOR . 'tmp/' . $process . '.key';
		$zipFile = JPATH_ROOT . DIRECTORY_SEPARATOR . 'tmp/' . $process . '.zip';

		if (is_file($keyFile) && is_writable($keyFile)) {
			@unlink($keyFile);
			@unlink($zipFile);
		}
	}
}

$session->set('jsn-download-process-list', array(), 'jsntemplatesession');

$template_style_id = JRequest::getInt('template_style_id', 0, 'GET');
$task              = JRequest::getWord('task', '', 'POST');
$type              = JRequest::getWord('type', '', 'POST');
$backup_file_name  = JRequest::getCmd('backup_file_name', '', 'POST');
$jsn_layout        = JRequest::getCmd('jsn_layout', '', 'GET');

$login					= false;
$result_install		    = false;
$manual 				= false;
if (!$obj_utils->cURLCheckFunctions() && !$obj_utils->fOPENCheck() && !$obj_utils->fsocketopenCheck())
{
	$manual	= true;
}

if ($jsn_layout != '' && $jsn_layout == 'manual') $manual = true;

switch ($task)
{
	case 'login':
		JRequest::checkToken() or jexit('Invalid Token');
		$username 	= JRequest::getVar('username', '', 'post', 'username');
		$password 	= JRequest::getString('password', '', 'post', JREQUEST_ALLOWRAW);
		$login		= $obj_sampledata_helper->login($username, $password);
		$canDo		= $obj_sampledata_helper->getUserActions();
		if ($login && $canDo->get('core.manage'))
		{
			if ($type == 'auto' && !$manual)
			{
				$session->set($login_identifier, true, 'jsntemplatesession');
			}
			else
			{
				$result_install = $obj_sampledata_helper->installSampleDataManually();
			}
		}
		break;
	case 'manual':
		JRequest::checkToken() or jexit('Invalid Token');
		if ($session->get($login_identifier, false, 'jsntemplatesession'))
		{
			$session->set($login_identifier, false, 'jsntemplatesession');
			$result_install = $obj_sampledata_helper->installSampleDataManually();
		}
		break;
	default:
	break;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
	<head>
		<title><?php echo JText::_('JSN_SAMPLE_DATA_PAGE_TITLE') ?></title>
		<link rel="stylesheet" href="<?php echo JURI::base(true); ?>/templates/<?php echo $this->template; ?>/admin/css/jsn_admin.css" type="text/css" />
		<link rel="stylesheet" href="<?php echo JURI::base(true); ?>/templates/system/css/system.css" type="text/css" />
		<link rel="stylesheet" href="<?php echo JURI::base(true); ?>/media/jui/css/bootstrap.min.css" type="text/css" />
		<?php echo JSN_TEMPLATE_JAVASCRIPT_MOOTOOL_CORE; ?>
		<?php echo JSN_TEMPLATE_JAVASCRIPT_MOOTOOL_MORE; ?>

		<script type="text/javascript">
			var joomlaBaseUrl = '<?php echo JUri::base(true) ?>';
			var joomlaTemplateUrl = joomlaBaseUrl + '/templates/<?php echo $this->template?>';
		</script>
		<script type="text/javascript" src="<?php echo JURI::base(true); ?>/templates/<?php echo $this->template; ?>/admin/js/jsn_downloader.js"></script>
		<script type="text/javascript" src="<?php echo JURI::base(true); ?>/templates/<?php echo $this->template; ?>/admin/js/jsn_sampledata.js"></script>
	</head>
	<body id="jsn-sampledata">
		<div id="jsn-page">
			<div id="jsn-page_inner1">
				<div id="jsn-page_inner2">
					<h1><?php echo JText::sprintf('JSN_SAMPLE_DATA_MAIN_TITLE', $template_manifest['name_uppercase']); ?>
						<span class="action-cancel"><a id="jsn-install-cancel" class="link-action" href="javascript: void(0);" onclick="JSNSampleData.closeModalWindow();"><?php echo JText::_('JSN_SAMPLE_DATA_CANCEL_TEXT'); ?></a></span>
					</h1>
					<?php if (JVERSION < '2.5.4') {
					/**
					 * Our sample data is built on 2.5.4 version of Joomla,
					 * which has a minor db change. This change will affect the
					 * sample data installation on sites of prior versions so
					 * we need to tell the administrator to update Joomla core.
					 */
							echo '<div id="jsn-auto-install">';
							echo '<p>' . JText::sprintf('JSN_SAMPLE_DATA_BASIC_INFO', $strLiveDemoLink, $template_manifest['name_uppercase']) . '</p>';
							echo JText::_('SAMPLE_DATA_JOOMLA_OUTDATED');
							echo '<hr class="jsn-horizontal-line" />';
							echo '<div style="text-align: center" class="jsn-install-admin-navigation">';
								echo '<button class="action-submit" type="submit" id="jsn-go-update-button">Update Joomla</button>';
							echo '</div>';
							echo '</div>';
					?>
					<script type="text/javascript">
						window.addEvent("domready", function() {
							$("jsn-go-update-button").addEvent('click', function(e) {
								e.preventDefault();
								window.top.location = "<?php echo JURI::base() ?>" + "administrator/index.php?option=com_installer&view=update";
							});
						});
					</script>
					<?php
						} else {
							if (!$manual) {
					?>
					<div id="jsn-auto-install">
						<?php if (!$session->get($identifier, false, 'jsntemplatesession')) { ?>
								<?php if (!$login) { ?>
								<?php $session->set($login_identifier, false, 'jsntemplatesession'); ?>
								<p><?php echo JText::sprintf('JSN_SAMPLE_DATA_BASIC_INFO', $strLiveDemoLink, $template_manifest['name_uppercase']); ?></p>
								<div class="text-alert"><?php echo JText::_('JSN_SAMPLE_DATA_IMPORTANT_INFO'); ?></div>
								<?php echo $folderFailList; ?>
								<div id="jsn-auto-install-login-form" class="<?php echo (count($folderWritable) > 0) ? 'jsn-installsample-non-display' : ''; ?>">
									<jdoc:include type="message" />
									<h2><?php echo JText::_('JSN_SAMPLE_DATA_LOGIN_HEADING'); ?></h2>
									<?php echo JText::_('JSN_SAMPLE_DATA_LOGIN_DESC'); ?>
									<form method="post" action="index.php?template=<?php echo $this->template; ?>&amp;tmpl=jsn_installsampledata&amp;template_style_id=<?php echo $template_style_id ?>&amp;jsn_layout=<?php echo $jsn_layout; ?>" id="frm-login" name="frm_login" class="install-sample-data-form" autocomplete="off">
										<div id="jsn-wrap-installation-content">
											<div id="jsn-sampledata-login">
												<div class="jsn-install-admin-info">
													<p class="clearafter">
													<span><?php echo JText::_('JSN_SAMPLE_DATA_USERNAME'); ?><input name="username" id="username" type="text" onchange="JSNSampleData.setInstallationButtonState(this.form);" onkeyup="JSNSampleData.setInstallationButtonState(this.form);" /></span>
													<span><?php echo JText::_('JSN_SAMPLE_DATA_PASSWORD'); ?><input name="password" id="password" type="password" onchange="JSNSampleData.setInstallationButtonState(this.form);" onkeyup="JSNSampleData.setInstallationButtonState(this.form);" /></span>
													</p>
												</div>
												<hr class="jsn-horizontal-line" />
												<div class="jsn-install-admin-check">
													<p>
														<label for="local_rules_agree" class="input-label">
															<input type="checkbox" value="1" id="local_rules_agree" name="agree" onclick="JSNSampleData.setInstallationButtonState(this.form);" />
															<?php echo JText::_('JSN_SAMPLE_DATA_AGREEMENT'); ?>
														</label>
													</p>
													<div class="jsn-install-admin-navigation">
														<button class="action-submit" type="submit" id="jsn-install-button" name="installation_button" disabled="disabled"><?php echo JText::_('JSN_SAMPLE_DATA_INSTALL_BUTTON'); ?></button>
													</div>
												</div>
												<input type="hidden" name="task" value="login" />
												<input type="hidden" name="type" value="auto" />
												<?php echo JHTML::_('form.token'); ?>
											</div>
										</div>
										<?php if (count($folderWritable) > 0) { ?>
										<script type="text/javascript">
											window.addEvent('domready', function() {
												JSNSampleData.disableFormInputs($("frm-login"), true);
											});
										</script>
										<?php } ?>
									</form>
								</div>
								<?php } else { ?>
									<div id="jsn-sampledata-installation">
										<div class="jsn-install-admin-info">
											<p><?php echo JText::_('JSN_SAMPLE_DATA_SEVERAL_STEP'); ?></p>
										</div>
										<script type="text/javascript">
											window.addEvent('domready', function() {
												JSNSampleData.start('<?php echo strtolower($template_manifest['full_name']);?>', '<?php echo $frontIndexPath;?>', <?php echo $template_style_id; ?>);
											});
										</script>
										<div id="jsn-sampledata-start-updating-process">
											<div id="jsn-installing-sampledata">
												<ul>
													<li id="jsn-download-sample-data-package-title">
														<span class="jsn-step-subtitle"><?php echo JText::_('JSN_SAMPLE_DATA_DOWNLOAD_PACKAGE'); ?></span>
														<span class="jsn-step-state-indicator"></span>
														<div id="jsn-download-sampledata-progress" class="download-progress">
															<div class="progress">
																<div class="bar" style="width: 0%;"></div>
															</div>
															<span class="percentage"></span>
														</div>
													</li>
													<!-- Reserved space for listing extensions for user to choose -->
													<li id="jsn-extension-selection" class="jsn-installsample-non-display">
															<span><?php echo JText::_('JSN_SAMPLE_DATA_LISTING_AVAILABLE_EXTS') ?></span>
															<div id="jsn-extension-list"></div>
															<hr class="jsn-horizontal-line" />
															<div class="jsn-install-admin-navigation">
																<button class="action-submit" type="submit" id="jsn-install-continue-button" name="installation_button"><?php echo JText::_('JSN_SAMPLE_DATA_INSTALL_BUTTON'); ?></button>
															</div>
													</li>
													<li id="jsn-install-extensions-title">
														<span class="jsn-step-subtitle"><?php echo JText::_('JSN_SAMPLE_DATA_INSTALL_EXTENSIONS'); ?></span>
														<!-- Reserved space for listing extensions to be installed -->
														<div id="jsn-install-extension-sublist"></div>
														<span class="jsn-step-message"></span>
														<div id="jsn-skip-install-ext-wrapper">
															<hr class="jsn-horizontal-line" />
															<div class="jsn-install-admin-navigation">
																<button class="action-submit" type="button" id="jsn_skip_install_ext" ><?php echo JText::_('JSN_SAMPLE_DATA_SKIP_INSTALL_EXT_BUTTON'); ?></button>
															</div>
														</div>
													</li>
													<li id="jsn-install-sample-data-package-title">
														<span class="jsn-step-subtitle"><?php echo JText::_('JSN_SAMPLE_DATA_INSTALL'); ?></span>
														<span class="jsn-step-state-indicator"></span>
														<span class="jsn-step-message"></span>
													</li>
												</ul>
												<div id="jsn-install-sample-data-manually-inline">
													<form method="post" action="index.php?template=<?php echo $this->template; ?>&amp;tmpl=jsn_installsampledata&amp;template_style_id=<?php echo $template_style_id ?>&amp;jsn_layout=manual" id="frm-inline-install-manual" name="frm-inline-install-manual" class="installSampleDataFrom" autocomplete="off" enctype="multipart/form-data">
														<ol>
															<li><?php echo JText::_('JSN_SAMPLE_DATA_MANUAL_INLINE_MES1'); ?><a class="link-button button-small" href="<?php echo JSN_SAMPLE_DATA_FILE_URL; ?>"><?php echo JText::_('JSN_SAMPLE_DATA_MANUAL_INLINE_MES_BUTTON'); ?></a></li>
															<li><?php echo JText::_('JSN_SAMPLE_DATA_MANUAL_MES2'); ?><input type="file" name="install_package" id="jsn-inline-install-manual-package" size="35" onchange="return JSNSampleData.setInlineInstallationButtonState(this.form);"/></li>
														</ol>
														<input type="hidden" name="task" value="manual" />
														<input type="hidden" name="type" value="manual" />
														<?php echo JHTML::_('form.token'); ?>
														<hr class="jsn-horizontal-line" />
														<div class="jsn-install-admin-navigation">
															<button class="action-submit" type="submit" id="jsn_inline_install_manual_button" name="jsn_inline_install_manual_button" disabled="disabled"><?php echo JText::_('JSN_SAMPLE_DATA_INSTALL_BUTTON'); ?></button>
														</div>
													</form>
												</div>
											</div>
											<div id="jsn-installing-sampledata-successfully">
												<hr />
												<div>
													<h3 class="jsnis-element-green-heading"><span id="jsn-sampledata-success-message"><?php echo JText::_('JSN_SAMPLE_DATA_SUCCESS_TITLE'); ?></span></h3>
													<p><?php echo JText::sprintf('JSN_SAMPLE_DATA_SUCCESS_DESC', $template_manifest['name_uppercase']); ?></p>
												</div>
												<div class="text-alert-1" id="jsn-warnings"><strong style="color: #cc0000"><?php echo JText::_('JSN_SAMPLE_DATA_WARNING_ATTENTION'); ?></strong>
													<p><?php echo JText::_('JSN_SAMPLE_DATA_WARNING_DESC') ?></p>
													<ul id="jsn-ul-warnings">
													</ul>
												</div>
												<div class="jsn-install-admin-navigation jsn-content-installing-sampledata-successfully">
													<button class="action-submit" type="button" id="jsn-finish-button" name="finish_button" onclick="window.top.location.reload(true);"><?php echo JText::_('JSN_SAMPLE_DATA_FINISH_BUTTON'); ?></button>
												</div>
											</div>
										</div>
									</div>
								<?php } ?>
						<?php } ?>
					</div>
					<?php } else { ?>
					<div id="jsn-manual-install">
					<?php if (!$result_install) { ?>
						<p><?php echo JText::sprintf('JSN_SAMPLE_DATA_BASIC_INFO', $strLiveDemoLink, $template_manifest['name_uppercase']); ?></p>
						<div class="text-alert"><?php echo JText::_('JSN_SAMPLE_DATA_IMPORTANT_INFO'); ?></div>
						<jdoc:include type="message" />
						<form method="post" action="index.php?template=<?php echo $this->template; ?>&amp;tmpl=jsn_installsampledata&amp;template_style_id=<?php echo $template_style_id ?>&amp;jsn_layout=<?php echo $jsn_layout; ?>" id="frm-login" name="frm_login" class="install-sample-data-form" autocomplete="off" enctype="multipart/form-data">
							<div class="jsn-manual-install-select-file">
								<h2><?php echo JText::_('JSN_SAMPLE_DATA_MANUAL_HEADING_STEP1'); ?></h2>
								<ol>
									<li><?php echo JText::_('JSN_SAMPLE_DATA_MANUAL_INLINE_MES1'); ?><a class="link-button button-small" href="<?php echo JSN_SAMPLE_DATA_FILE_URL; ?>"><?php echo JText::_('JSN_SAMPLE_DATA_MANUAL_INLINE_MES_BUTTON'); ?></a></li>
									<li><?php echo JText::_('JSN_SAMPLE_DATA_MANUAL_MES2'); ?><input type="file" name="install_package" id="jsn-inline-install-manual-package" size="35" onchange="return JSNSampleData.setInstallationButtonState(this.form);"/></li>
								</ol>
							</div>
							<hr class="jsn-horizontal-line" />
							<div class="jsn-manual-install-login">
								<h2><?php echo JText::_('JSN_SAMPLE_DATA_MANUAL_HEADING_STEP2'); ?></h2>
								<?php echo JText::_('JSN_SAMPLE_DATA_LOGIN_DESC'); ?>
								<div id="jsn-wrap-installation-content">
									<div id="jsn-sampledata-login">
										<div class="jsn-install-admin-info">
											<p class="clearafter">
											<span><?php echo JText::_('JSN_SAMPLE_DATA_USERNAME'); ?><input name="username" id="username" type="text" onchange="JSNSampleData.setInstallationButtonState(this.form);" onkeyup="JSNSampleData.setInstallationButtonState(this.form);" /></span>
											<span><?php echo JText::_('JSN_SAMPLE_DATA_PASSWORD'); ?><input name="password" id="password" type="password" onchange="JSNSampleData.setInstallationButtonState(this.form);" onkeyup="JSNSampleData.setInstallationButtonState(this.form);" /></span>
											</p>
										</div>
										<hr class="jsn-horizontal-line" />
										<div class="jsn-install-admin-check">
											<p>
												<label for="local_rules_agree" class="input-label"><input type="checkbox" value="1" id="local_rules_agree" name="agree" onclick="JSNSampleData.setInstallationButtonState(this.form);" />
												<?php echo JText::_('JSN_SAMPLE_DATA_AGREEMENT'); ?></label>
											</p>
											<div class="jsn-install-admin-navigation">
												<button class="action-submit" type="submit" id="jsn-install-button" name="installation_button" disabled="disabled"><?php echo JText::_('JSN_SAMPLE_DATA_INSTALL_BUTTON'); ?></button>
											</div>
										</div>
										<input type="hidden" name="task" value="login" />
										<input type="hidden" name="type" value="manual" />
										<?php echo JHTML::_('form.token'); ?>
									</div>
								</div>
							</div>
						</form>
						<?php } else { ?>
							<div id="jsn-manual-install-successfully">
								<hr />
								<div>
									<h3 class="jsnis-element-green-heading"><span id="jsn-sampledata-success-message"><?php echo JText::_('JSN_SAMPLE_DATA_SUCCESS_TITLE'); ?></span></h3>
									<p><?php echo JText::sprintf('JSN_SAMPLE_DATA_SUCCESS_DESC', $template_manifest['name_uppercase']); ?></p>
								</div>
								<?php if (count($error)) { ?>
								<div class="text-alert-1"><strong style="color: #cc0000"><?php echo JText::_('JSN_SAMPLE_DATA_WARNING_ATTENTION'); ?></strong>
									<p><?php echo JText::_('JSN_SAMPLE_DATA_WARNING_DESC') ?></p>
									<ul id="jsn-ul-warnings">
									<?php $obj_sample_data_helper = new JSNSampleDataHelper(); ?>
									<?php foreach ($error as $value) { ?>
										<li><?php echo $value; ?></li>
									<?php } ?>
									</ul>
								</div>
								<?php } ?>
								<div class="jsn-install-admin-navigation jsn-content-installing-sampledata-successfully">
									<button class="action-submit" type="button" id="jsn-finish-button" name="finish_button" onclick="window.top.location.reload(true);"><?php echo JText::_('JSN_SAMPLE_DATA_FINISH_BUTTON'); ?></button>
								</div>
							</div>
						<?php } ?>
					</div>
					<?php
							}
						}
					?>
				</div>
			</div>
		</div>
	<script type="text/javascript">
		window.addEvent('domready', function() {
			/* Disable modal window close button */
			JSNSampleData.disableModalCloseButton();
		});
	</script>
	</body>
</html>
