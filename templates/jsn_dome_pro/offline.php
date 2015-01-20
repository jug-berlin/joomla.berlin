<?php
/**
 * @author    JoomlaShine.com http://www.joomlashine.com
 * @copyright Copyright (C) 2008 - 2011 JoomlaShine.com. All rights reserved.
 * @license   GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 */

	// No direct access
	defined('_JEXEC') or die('Restricted index access');
	define('YOURBASEPATH', dirname(__FILE__));
	$app = JFactory::getApplication();

	require_once(YOURBASEPATH. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_utils.php');
	$jsnutils 				= JSNUtils::getInstance();
	// Get template details
	$template_details 		= $jsnutils->getTemplateDetails();
	// Parser template ini file
	$params					= $jsnutils->getTemplateParameters();

	$logo_slogan			= $params->get("logoSlogan", "");

	/* URL where logo image should link to (! without preceding slash !)
	   Leave this box empty if you want your logo to be clickable. */
	$logo_link = $params->get("logoLink", "");
	if (strpos($logo_link, "http")=== false && $logo_link != '')
	{
		$logo_link = $jsnutils->trimPreceddingSlash($logo_link);
		$logo_link = $this->baseurl."/".$logo_link;
	}

	// Template color: orange | red | cyan | green | yellow | pink
	$template_color = $params->get("templateColor", "red");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- <?php echo $template_details->name; ?> <?php echo $template_details->version; ?> -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
	<head>
		<jdoc:include type="head" />
		<title><?php echo $this->title; ?></title>
		<link rel="stylesheet" href="<?php echo $this->baseurl.'/templates/'.$this->template ;?>/css/offline.css" type="text/css" />
		<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/system.css" type="text/css" />
	</head>
	<body id="jsn-master" class="jsn-color-<?php echo $template_color; ?>">
		<jdoc:include type="message" />
		<div id="jsn-page">
			<div id="jsn-page_inner">
				<div id="jsn-header">
					<div id="jsn-logo">
						<a href="<?php echo $logo_link; ?>" title="<?php echo $logo_slogan; ?>">
							<?php
								$logo_path = $params->get("logoPath", "");
								if ($logo_path != "")
								{
									$logo_path = $this->baseurl.'/'.htmlspecialchars($logo_path);
								}
								else
								{
									$logo_path = $this->baseurl.'/templates/'.$this->template."/images/logo.png";
								}
							?>
							<img src="<?php echo $logo_path ;?>" alt="<?php echo $logo_slogan; ?>" />
						</a>
					</div>
				</div>
				<div id="jsn-body" class="clearafter">
					<div id="jsn-error-heading">
						<?php if ($app->getCfg('offline_image')) : ?>
							<img src="<?php echo $app->getCfg('offline_image'); ?>" alt="<?php echo htmlspecialchars($app->getCfg('sitename')); ?>" />
						<?php else : ?>
							<img src="<?php echo $this->baseurl.'/templates/'.$this->template ;?>/images/offline-banner.png" alt="Offline Banner" />
						<?php endif; ?>
					</div>
					<div id="jsn-error-content" class="jsn-offline-page">
						<div id="jsn-error-content_inner">
							<div id="frame" class="outline">
								<h3> <?php echo $app->getCfg('offline_message'); ?> </h3>
								<form action="<?php echo JRoute::_('index.php', true); ?>" method="post" id="form-login">
									<fieldset class="input">
										<p id="form-login-username">
											<label for="username"><?php echo JText::_('JGLOBAL_USERNAME') ?></label>
											<br />
											<input name="username" id="username" type="text" class="inputbox" alt="<?php echo JText::_('JGLOBAL_USERNAME') ?>" size="18" />
										</p>
										<p id="form-login-password">
											<label for="passwd"><?php echo JText::_('JGLOBAL_PASSWORD') ?></label>
											<br />
											<input type="password" name="password" class="inputbox" size="18" alt="<?php echo JText::_('JGLOBAL_PASSWORD') ?>" id="passwd" />
										</p>
										<p id="form-login-remember" class="clearafter">
											<label for="remember"><?php echo JText::_('JGLOBAL_REMEMBER_ME') ?>
												<input type="checkbox" name="remember" class="inputbox" value="yes" alt="<?php echo JText::_('JGLOBAL_REMEMBER_ME') ?>" id="remember" />
											</label>
											<input type="submit" name="Submit" class="button link-button" value="<?php echo JText::_('JLOGIN') ?>" />
										</p>
									</fieldset>
									<input type="hidden" name="option" value="com_users" />
									<input type="hidden" name="task" value="user.login" />
									<input type="hidden" name="return" value="<?php echo base64_encode(JURI::base()) ?>" />
									<?php echo JHtml::_('form.token'); ?>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>