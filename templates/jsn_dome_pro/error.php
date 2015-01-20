<?php
/**
 * @author    JoomlaShine.com http://www.joomlashine.com
 * @copyright Copyright (C) 2008 - 2011 JoomlaShine.com. All rights reserved.
 * @license   GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 */

	// No direct access
	defined('_JEXEC') or die('Restricted index access');
	define('YOURBASEPATH', dirname(__FILE__));
	if (!isset($this->error))
	{
		$this->error = JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
		$this->debug = false;
	}

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
		<title><?php echo $this->error->getCode(); ?>-<?php echo $this->title; ?></title>
		<link rel="stylesheet" href="<?php echo $this->baseurl.'/templates/'.$this->template; ?>/css/error.css" type="text/css" />
	</head>
	<body id="jsn-master" class="jsn-color-<?php echo $template_color; ?>">
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
							<img src="<?php echo $logo_path; ?>" alt="<?php echo $logo_slogan; ?>" />
						</a>
					</div>
				</div>
				<div id="jsn-body" class="clearafter">
					<div id="jsn-error-heading">
						<h1><?php echo $this->error->getCode(); ?> <span class="heading-medium"><?php echo JText::_('JERROR_ERROR'); ?></span></h1>
					</div>
					<div id="jsn-error-content" class="jsn-error-page">
						<div id="jsn-error-content_inner">
							<h1><span class="heading-small"><?php echo $this->error->getMessage(); ?></span></h1>
							<hr />
							<h3><?php echo JText::_('JERROR_LAYOUT_NOT_ABLE_TO_VISIT'); ?></h3>
							<ul>
								<li><?php echo JText::_('JERROR_LAYOUT_AN_OUT_OF_DATE_BOOKMARK_FAVOURITE'); ?></li>
								<li><?php echo JText::_('JERROR_LAYOUT_SEARCH_ENGINE_OUT_OF_DATE_LISTING'); ?></li>
								<li><?php echo JText::_('JERROR_LAYOUT_MIS_TYPED_ADDRESS'); ?></li>
								<li><?php echo JText::_('JERROR_LAYOUT_YOU_HAVE_NO_ACCESS_TO_THIS_PAGE'); ?></li>
								<li><?php echo JText::_('JERROR_LAYOUT_REQUESTED_RESOURCE_WAS_NOT_FOUND'); ?></li>
								<li><?php echo JText::_('JERROR_LAYOUT_ERROR_HAS_OCCURRED_WHILE_PROCESSING_YOUR_REQUEST'); ?></li>
							</ul>
							<hr />
							<h3><?php echo JText::_('JERROR_LAYOUT_SEARCH_ON_THE_WEBSITE'); ?></h3>
							<form id="search-form" method="post" action="index.php">
								<div class="search">
									<input type="text" onfocus="if(this.value=='search...') this.value='';" onblur="if(this.value=='') this.value='search...';" value="" size="20" class="inputbox" alt="Search" maxlength="20" id="mod-search-searchword" name="searchword">
									<input type="submit" onclick="this.form.searchword.focus();" class="button link-button" value="Search">
								</div>
								<input type="hidden" value="search" name="task">
								<input type="hidden" value="com_search" name="option">
								<input type="hidden" value="435" name="Itemid">
							</form>
							<p id="link-goback">or <a href="<?php echo $this->baseurl; ?>/index.php" class="link-action" title="<?php echo JText::_('JERROR_LAYOUT_GO_TO_THE_HOME_PAGE'); ?>"><?php echo JText::_('JERROR_LAYOUT_GO_TO_THE_HOME_PAGE'); ?></a></p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>