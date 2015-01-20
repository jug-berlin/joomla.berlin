<?php
/**
 * @author    JoomlaShine.com http://www.joomlashine.com
 * @copyright Copyright (C) 2008 - 2011 JoomlaShine.com. All rights reserved.
 * @license   GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 * @version   $Id: jsn_autoupdater.php 17175 2012-10-17 10:52:05Z tuyetvt $
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once('includes'. DIRECTORY_SEPARATOR .'jsn_defines.php');
require_once('includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_utils.php');
require_once('includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_readxmlfile.php');
require_once('includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_auto_updater_helper.php');

$session			= JFactory::getSession();
$obj_read_xml_file 	= new JSNReadXMLFile();
$obj_utils			= new JSNUtils();
$obj_updater_helper	= new JSNAutoUpdaterHelper();
$template_manifest	= $obj_read_xml_file->getTemplateManifestFileInformation();
$manifest_cache		= $obj_utils->getTemplateManifestCache();
$manifest_cache		= json_decode($manifest_cache);

/* Determine front-end index path, including lang sef if available */
$frontIndexPath = JURI::root() . $obj_utils->determineFrontendIndex();

/* Form template identified_name */
$explodedName = explode('_', $template_manifest['name']);
$templateIdName = 'tpl_' . $explodedName[1];

/* Get template latest version from session if possible */
$templateVersionSesId = md5('template_version_' . strtolower($template_manifest['full_name']));
$versionFromSes = $session->get($templateVersionSesId, null, 'jsntemplatesession');
if (!is_null($versionFromSes))
{
	$latest_version = $versionFromSes;
}
else
{
	$latest_version = $obj_utils->getLatestProductVersion($templateIdName, 'template');
}

if ($template_manifest['edition'] != '' && $template_manifest['edition'] != 'free')
{
	$edition = $template_manifest['edition'];
}
else
{
	$edition = 'free';
}
$template_style_id			= JRequest::getInt('template_style_id', 0, 'GET');
$task 						= JRequest::getWord('task', '', 'POST');
$login_identifier 			= md5('state_update_login_'.strtolower($template_manifest['full_name']));
$customer_info_identifier 	= md5('state_update_customer_info_'.strtolower($template_manifest['full_name']));
$authentication				= false;
$login						= false;
switch ($task)
{
	case 'login':
		JRequest::checkToken() or jexit('Invalid Token');
		$post 		= JRequest::get('post');
		$username 	= JRequest::getVar('username', '', 'post', 'username');
		$password 	= JRequest::getString('password', '', 'post', JREQUEST_ALLOWRAW);
		$login		= $obj_updater_helper->login($username, $password);
		$canDo		= $obj_updater_helper->getUserActions();
		if ($login && $canDo->get('core.manage'))
		{
			if ($edition != 'free')
			{
				$authentication = $obj_updater_helper->authenticateCustomerInfo();
				if ($authentication)
				{
					$customer_password 	= JRequest::getString('customer_password', '', 'post', JREQUEST_ALLOWRAW);
					$customer_info		= array('username'=>$post['customer_username'], 'password'=>$customer_password);
					$session->set($login_identifier, true, 'jsntemplatesession');
					$session->set($customer_info_identifier, $customer_info, 'jsntemplatesession');
				}
			}
			else
			{
				$customer_info		= array('username'=>'', 'password'=>'');
				$session->set($login_identifier, true, 'jsntemplatesession');
				$session->set($customer_info_identifier, $customer_info, 'jsntemplatesession');
				$authentication		= true;
			}
		}
		break;
	case 'download_modified_file':
		$file_name 		= JRequest::getCmd('modified_file_name', '', 'POST');
		if ($obj_updater_helper->downloadFile('zip', $file_name))
		{
			jexit();
		}
		else
		{
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('JSN_UPDATE_BACKUP_FILE_NOT_FOUND'));
		}
		break;
	default:
	break;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
	<head>
		<title><?php echo JText::_('JSN_UPDATE_PAGE_TITLE'); ?></title>
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
		<script type="text/javascript" src="<?php echo JURI::base(true); ?>/templates/<?php echo $this->template; ?>/admin/js/jsn_updater.js"></script>
	</head>
	<body id="jsn-auto-updater">
		<div id="jsn-container">
			<div id="jsn-wrapper">
				<h1><?php echo JText::sprintf('JSN_UPDATE_MAIN_TITLE', $template_manifest['name_uppercase']); ?> <span id="jsn-from-version-to-version"><?php echo $manifest_cache->version; ?> to <?php echo $latest_version; ?></span>
					<span class="action-cancel"><a id="jsn-updater-cancel" class="link-action" href="javascript: void(0);" onclick="window.top.setTimeout('SqueezeBox.close();', 100);"><?php echo JText::_('JSN_UPDATE_CANCEL_TEXT'); ?></a></span>
				</h1>
				<?php if (!$authentication || !$login) { ?>
				<?php
					$session->set($login_identifier, false, 'jsntemplatesession');
					$session->set($customer_info_identifier, array(), 'jsntemplatesession');
				?>
				<div id="page-1">
					<p><?php echo JText::_('JSN_UPDATE_BASIC_INFO'); ?></p>
					<div class="text-info"><?php echo JText::_('JSN_UPDATE_IMPORTANT_INFO'); ?></div>
					<jdoc:include type="message" />
					<form method="post" action="index.php?template=<?php echo $this->template; ?>&amp;tmpl=jsn_autoupdater&amp;template_style_id=<?php echo $template_style_id ?>" id="frm_update" name="frm_update" autocomplete="off" enctype="multipart/form-data">
					<div class="step-1">
						<?php if  ($edition != 'free') { ?>
						<h2><?php echo JText::_('JSN_UPDATE_HEADING_STEP1'); ?></h2>
						<?php } ?>
						<p><?php echo JText::_('JSN_UPDATE_DESC_STEP1'); ?></p>
						<div class="jsn-login-form">
							<p class="clearafter">
								<span><?php echo JText::_('JSN_UPDATE_USERNAME'); ?><input name="username" id="username" type="text" /></span>
								<span><?php echo JText::_('JSN_UPDATE_PASSWORD'); ?><input name="password" id="password" type="password" /></span>
							</p>
						</div>
					</div>
					<hr class="jsn-horizontal-line" />
					<?php if  ($edition != 'free') { ?>
					<div class="step-2">
						<h2><?php echo JText::_('JSN_UPDATE_HEADING_STEP2'); ?></h2>
						<p><?php echo JText::_('JSN_UPDATE_DESC_STEP2'); ?></p>
						<div class="jsn-login-form">
							<p class="clearafter">
								<span><?php echo JText::_('JSN_UPDATE_USERNAME'); ?><input name="customer_username" id="customer-username" type="text" /></span>
								<span><?php echo JText::_('JSN_UPDATE_PASSWORD'); ?><input name="customer_password" id="customer-password" type="password" /></span>
							</p>
						</div>
					</div>
					<hr class="jsn-horizontal-line" />
					<?php } ?>
					<div class="jsn-button">
						<button class="action-submit" type="submit" id="jsn-update-button" name="installation_button"><?php echo JText::_('JSN_UPDATE_UPDATE_BUTTON'); ?></button>
					</div>
					<input type="hidden" name="task" value="login" />
					<?php echo JHTML::_('form.token'); ?>
					</form>
				</div>
				<?php } else { ?>
				<?php
					$customer_info = $session->get($customer_info_identifier, array(), 'jsntemplatesession');
					$product_info  = array('customer_username'=>$customer_info['username'], 'customer_password'=>$customer_info['password']);
				?>
					<script type="text/javascript">
						var options = <?php echo json_encode($product_info); ?>;
						var objJSNUpdater = new JSNUpdater(options);
						window.addEvent('domready', function() {
							objJSNUpdater.toggleCancelButton(false);
							objJSNUpdater.dowloadTemplatePackage('<?php echo strtolower($template_manifest['full_name']);?>', '<?php echo $frontIndexPath;?>', <?php echo $template_style_id; ?>);
						});
					</script>
					<div id="page-2">
						<p><?php echo JText::_('JSN_UPDATE_SEVERAL_STEP'); ?></p>
						<ul>
							<li id="jsn-download-package-li">
								<span id="jsn-download-package-subtitle"><?php echo JText::_('JSN_UPDATE_DOWNLOAD_PACKAGE'); ?></span>
								<span id="jsn-download-package"></span>
								
								<div id="jsn-download-package-progress" class="download-progress">
									<div class="progress">
										<div class="bar" style="width: 0%;"></div>
									</div>
									<span class="percentage"></span>
								</div>

								<span id="jsn-download-package-message" class="jsn-message"></span>
							</li>
							<li id="jsn-create-modified-list-li" class="jsn-updater-display-none">
								<span id="jsn-create-modified-list-subtitle"><?php echo JText::_('JSN_UPDATE_CREATE_BACKUP'); ?></span>
								<span id="jsn-create-modified-list"></span>
							</li>
							<li id="jsn-update-template-li" class="jsn-updater-display-none">
								<span id="jsn-update-template-subtitle"><?php echo JText::_('JSN_UPDATE_INSTALL_FILES'); ?></span>
								<span id="jsn-update-template"></span>
								<span id="jsn-update-template-message" class="jsn-message"></span>
							</li>
						</ul>
						<div id="jsn-download-package-manual-update" class="jsn-updater-display-none">
							<form method="post" action="index.php?template=<?php echo $this->template; ?>&amp;tmpl=jsn_manualupdater&amp;template_style_id=<?php echo $template_style_id ?>" id="frm_manual_update" name="frm_manual_update" autocomplete="off" enctype="multipart/form-data">
								<ul>
									<li>
										<?php echo JText::_('JSN_UPDATE_MANUAL_SELECT_PACKAGE'); ?>
										<input type="file" name="package" id="package" size="35" />
									</li>
								</ul>
								<input name="username" type="hidden" value="<?php echo @$username; ?>" />
								<input name="password" type="hidden" value="<?php echo @$password; ?>" />
								<input type="hidden" name="task" value="login" />
								<?php echo JHTML::_('form.token'); ?>
								<hr class="jsn-horizontal-line" />
								<div class="jsn-button">
									<button class="action-submit" type="submit" id="jsn-update-button" name="installation_button"><?php echo JText::_('JSN_UPDATE_TRY_AGAIN_BUTTON'); ?></button>
								</div>
							</form>
						</div>
						<div id="jsn-update-succesfully-container" class="jsn-updater-display-none">
							<hr class="jsn-horizontal-line" />
							<div id="jsn-update-succesfully-wrapper" class="jsn-updater-display-none">
								<h3 class="jsn-green-heading"><?php echo JText::_('JSN_UPDATE_SUCCESS_TITLE'); ?></h3>
								<p><?php echo JText::_('JSN_UPDATE_SUCCESS_MES'); ?></p>
								<div class="text-alert-1 jsn-updater-display-none" id="text-alert-attention">
									<span class="title"><?php echo JText::_('JSN_UPDATE_CHANGES_ATTENTION'); ?></span>
									<p><?php echo JText::_('JSN_UPDATE_CHANGES_DESC'); ?></p>
									<ul>
										<li><a href="javascript: void(0);" onclick="document.frm_download.submit();" class="link-action"><?php echo JText::_('JSN_UPDATE_CHANGES_DOWNLOAD'); ?></a></li>
									</ul>
								</div>
							</div>
							<div class="jsn-button">
								<button class="action-submit" type="button" id="jsn-update-button" name="jsn_finish_button" onclick="window.top.location.reload(true);"><?php echo JText::_('JSN_UPDATE_FINISH_BUTTON'); ?></button>
							</div>
						</div>
					</div>
					<form method="post" action="index.php?template=<?php echo $this->template; ?>&amp;tmpl=jsn_autoupdater&amp;template_style_id=<?php echo $template_style_id ?>" id="frm_download" name="frm_download" autocomplete="off">
						<input type="hidden" name="task" value="download_modified_file" />
						<input type="hidden" name="modified_file_name" id="modified_file_name" value="" />
						<?php echo JHTML::_( 'form.token' ); ?>
					</form>
				<?php } ?>
			</div>
		</div>
	</body>
</html>