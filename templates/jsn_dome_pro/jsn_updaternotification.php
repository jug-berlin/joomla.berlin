<?php
/**
 * @author    JoomlaShine.com http://www.joomlashine.com
 * @copyright Copyright (C) 2008 - 2011 JoomlaShine.com. All rights reserved.
 * @license   GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 * @version   $Id: jsn_updaternotification.php 15592 2012-08-28 09:33:04Z giangnd $
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
include_once('includes'. DIRECTORY_SEPARATOR .'jsn_defines.php');
include_once('includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_utils.php');
include_once('includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_readxmlfile.php');
include_once('includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_updater_helper.php');
include_once('includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_httpsocket.php');
include_once('includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_downloadtemplatepackage.php');

$template_style_id	= JRequest::getInt('template_style_id', 0, 'GET');
$type				= JRequest::getVar('type', 'auto', 'GET');
$session			= JFactory::getSession();

$obj_read_xml_file 	= new JSNReadXMLFile();
$obj_utils			= new JSNUtils();
$obj_updater_helper	= new JSNUpdaterHelper();

$template_manifest	= $obj_read_xml_file->getTemplateManifestFileInformation();
if ($template_manifest['edition'] != '' && $template_manifest['edition'] != 'free')
{
	$edition = strtolower('pro_'.$template_manifest['edition']);
}
else
{
	$edition = 'free';
}
$manifest_cache		= $obj_utils->getTemplateManifestCache();
$manifest_cache		= json_decode($manifest_cache);
/* Form template identified_name */
$template_name					= strtolower($manifest_cache->name);
$template_version_identifier 	= md5('template_version_' . $template_name);
$customer_info_identifier 		= md5('state_update_customer_info_'.$template_name);
$login_identifier 				= md5('state_update_login_'.$template_name);

$version_from_session 			= $session->get($template_version_identifier, null, 'jsntemplatesession');
$customer_session 				= $session->get($customer_info_identifier, array(), 'jsntemplatesession');
$exploded_name 					= explode('_', strtolower($manifest_cache->name));
$identified_name				= 'tpl_' . $exploded_name[1];

if (!is_null($version_from_session))
{
	$latest_version = $version_from_session;
}
else
{
	$latest_version = $obj_utils->getLatestProductVersion($identified_name, 'template');
}

$task = JRequest::getWord('task', '', 'POST');
switch ($task)
{
	case 'download_modified_file':
		JRequest::checkToken() or jexit('Invalid Token');
		$file_name 	= JRequest::getCmd('modified_file_name', '', 'POST');
		if ($obj_updater_helper->downloadFile('zip', $file_name))
		{
			jexit();
		}
		else
		{
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('JSN_UPDATE_BACKUP_FILE_NOT_FOUND'));
		}
		break;
	case 'download_template_package':
		JRequest::checkToken() or jexit('Invalid Token');
		$post = JRequest::get('post');
		if (empty($post['package_name']))
		{
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('JSN_UPDATE_NOTIFICATION_FILE_NOT_FOUND'));
		}
		else
		{
			$tmp_path = JPATH_ROOT. DIRECTORY_SEPARATOR .'tmp';
			if ($type == 'auto')
			{
				$new_name		= 'jsn_'.$exploded_name[1].'_'.$edition.'_j'.$obj_utils->getJoomlaVersion(true).'_'.$latest_version.'_install.zip';
				$old_name 		= $post['package_name'];
				$file_old_path  = $tmp_path. DIRECTORY_SEPARATOR .$old_name;
				$file_new_path  = $tmp_path. DIRECTORY_SEPARATOR .$new_name;
			}
			else
			{
				$old_name 		= $post['package_name'];
			}

			if (is_file($file_old_path))
			{
				if ($type == 'auto')
				{
					@rename($file_old_path, $file_new_path);
				}

				if ($obj_updater_helper->downloadFile('zip', $new_name, $tmp_path, true))
				{
					jexit();
				}
				else
				{
					JError::raiseWarning('SOME_ERROR_CODE', JText::_('JSN_UPDATE_NOTIFICATION_FILE_NOT_FOUND'));
				}
			}
			else
			{
				JError::raiseWarning('SOME_ERROR_CODE', JText::_('JSN_UPDATE_NOTIFICATION_FILE_NOT_FOUND'));
			}
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
		<?php echo JSN_TEMPLATE_JAVASCRIPT_MOOTOOL_CORE; ?>
		<?php echo JSN_TEMPLATE_JAVASCRIPT_MOOTOOL_MORE; ?>
		<script type="text/javascript" src="<?php echo JURI::base(true); ?>/templates/<?php echo $this->template; ?>/admin/js/jsn_updater.js"></script>
	</head>
	<body id="jsn-updater">
		<div id="jsn-container">
			<div id="jsn-wrapper">
				<h1><?php echo JText::sprintf('JSN_UPDATE_MAIN_TITLE', strtoupper($exploded_name[0]).' '. ucfirst($exploded_name[1])); ?> <span id="jsn-from-version-to-version"><?php echo $manifest_cache->version; ?> to <?php echo $latest_version; ?></span>
					<span class="action-cancel"><a id="jsn-updater-cancel" class="link-action" href="javascript: void(0);" onclick="window.top.setTimeout('SqueezeBox.close();', 100);"><?php echo JText::_('JSN_UPDATE_CANCEL_TEXT'); ?></a></span>
				</h1>
				<div>
					<p><?php echo JText::_('JSN_UPDATE_NOTIFICATION_BASIC_INFO'); ?>:</p>
					<jdoc:include type="message" />
					<ul>
						<li><?php echo JText::_('JSN_UPDATE_NOTIFICATION_DOWNLOAD_ALL_MODIFIED_FILES'); ?>. <button class="action-submit" type="button" onclick="document.frm_download_backup_file.submit();"><?php echo JText::_('DOWNLOAD'); ?></button></li>
						<li><?php echo JText::_('JSN_UPDATE_NOTIFICATION_DOWNLOAD_THE_LATEST_TEMPLATE_VERSION'); ?>. <button class="action-submit" type="button" onclick="document.frm_download_template_package.submit();"><?php echo JText::_('DOWNLOAD'); ?></button></li>
						<li><?php echo JText::_('JSN_UPDATE_NOTIFICATION_UNINSTALL_CURRENT_TEMPLATE'); ?>.</li>
						<li><?php echo JText::_('JSN_UPDATE_NOTIFICATION_INSTALL_THE_LATEST_TEMPLATE_DOWNLOADED_IN_STEP_2'); ?>.</li>
						<li><?php echo JText::_('JSN_UPDATE_NOTIFICATION_APPLY_MODIFIED_FILES_DOWNLOADED_IN_STEP_1_TO_THE_NEW_TEMPLATE'); ?>. <a href="http://www.joomlashine.com/docs/joomla-templates/how-to-update-template.html" target="_blank" class="link-action"><?php echo JText::_('JSN_UPDATE_NOTIFICATION_READ_MORE'); ?></a></li>
					</ul>
					<div class="jsn-button">
						<button class="action-submit" type="button" onclick="window.top.setTimeout('SqueezeBox.close();', 100);"><?php echo JText::_('CLOSE'); ?></button>
					</div>
				</div>
			</div>
		</div>
		<form method="post" action="index.php?template=<?php echo $this->template; ?>&amp;tmpl=jsn_updaternotification&amp;template_style_id=<?php echo $template_style_id ?>" id="frm_download_backup_file" name="frm_download_backup_file" autocomplete="off">
			<input type="hidden" name="task" value="download_modified_file" />
			<input type="hidden" name="modified_file_name" value="<?php echo JRequest::getCmd('backup_file', '', 'GET')?>" />
			<?php echo JHTML::_( 'form.token' ); ?>
		</form>
		<form method="post" action="index.php?template=<?php echo $this->template; ?>&amp;tmpl=jsn_updaternotification&amp;template_style_id=<?php echo $template_style_id ?>" id="frm_download_template_package" name="frm_download_template_package" autocomplete="off">
			<input type="hidden" name="task" value="download_template_package" />
			<input type="hidden" name="package_name" value="<?php echo JRequest::getCmd('package_name', '', 'GET')?>" />
			<?php echo JHTML::_( 'form.token' ); ?>
		</form>
	</body>
</html>
<?php
$obj_updater_helper->destroySession();
?>