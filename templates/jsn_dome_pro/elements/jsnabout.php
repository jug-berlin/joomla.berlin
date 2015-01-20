<?php
/**
 * @author    JoomlaShine.com http://www.joomlashine.com
 * @copyright Copyright (C) 2008 - 2011 JoomlaShine.com. All rights reserved.
 * @license   GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 * @version   $Id$
 */

defined('JPATH_BASE') or die;
jimport('joomla.form.formfield');

/**
 * Output JSN About section
 *
 * @package
 * @subpackage
 * @since		1.6
 */
class JFormFieldJSNAbout extends JFormField
{
	public $type = 'JSNAbout';

	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected function getInput()
	{
		require_once dirname(dirname(__FILE__)). DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_utils.php';
		require_once dirname(dirname(__FILE__)). DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_convert_mobile_position.php';

		$objConvertMobilePosition = new JSNConvertMobilePosition(JRequest::getInt('id'));
		$objConvertMobilePosition->convertPosition();

		$jsnUtils 	  		= JSNUtils::getInstance();
		$doc 				= JFactory::getDocument();
		$templateName		= $jsnUtils->getTemplateName();

		$copyright          = '';
		$html 				= '';
		$manifestCache		= $jsnUtils->getTemplateManifestCache();
		$manifestCache		= json_decode($manifestCache);
		$result 			= $jsnUtils->getTemplateDetails();

		// Load the modal behavior script.
		JHTML::_('behavior.modal', 'a.jsn-modal');

		$version = new JVersion();
		$currentVersion = $version->getShortVersion();
		$isJoomla30 = version_compare($currentVersion, '3.0', '>=');

		if (!$isJoomla30) {
			$doc->addScript(JURI::root().'templates/'.$templateName.'/admin/js/jsn_slider.js');
			$doc->addScript(JURI::root().'templates/'.$templateName.'/admin/js/jsn_admin.js');
			$doc->addStyleSheet(JURI::root().'templates/'.$templateName.'/admin/css/jsn_admin.css');
		}
		else {
			$doc->addScript(JURI::root().'templates/'.$templateName.'/admin/js/jsn_admin_layout.js');
			$doc->addStyleSheet(JURI::root().'templates/'.$templateName.'/admin/css/jsn_admin30.css');

			$language = array(
				'GETTING_STARED' 		=> strtoupper(JText::_('GETTING_STARED')),
				'LOGO_SETTINGS' 		=> JText::_('LOGO_SETTINGS'),
				'LAYOUT_SETTINGS' 		=> JText::_('LAYOUT_SETTINGS'),
				'MOBILE_SETTINGS' 		=> JText::_('MOBILE_SETTINGS'),
				'STYLE_SETTINGS' 		=> JText::_('STYLE_SETTINGS'),
				'ME_SETTINGS' 			=> JText::_('ME_SETTINGS'),
				'SEO_SYSTEM_SETTINGS' 	=> JText::_('SEO_SYSTEM_SETTINGS')
			);

			$doc->addScriptDeclaration('
				!function ($) {
					$(function () {
						new $.JSNAdminLayout({ lang: ' . json_encode($language) . ' });
					});
				}(jQuery)
			');
		}
		
		$doc->addScriptDeclaration('
			var templateLang					= {};
			templateLang.expand_all				= "'.JText::_('EXPAND_ALL').'";
			templateLang.collapse_all			= "'.JText::_('COLLAPSE_ALL').'";
		');

		$frontIndexPath = JURI::root() . $jsnUtils->determineFrontendIndex();

		$doc->addScriptDeclaration("
			function checkIntegrity()
			{
				$('jsn-check-integrity').set('html', '');
				$('jsn-check-integrity-result').set('html', '');
				var actionIntegrityUrl = '".$frontIndexPath."';

				var resultIntegrityMsg = new Element('span');
				resultIntegrityMsg.set('class','jsn-integrity-checking');
				resultIntegrityMsg.set('html','".JText::_('CHECKING', true)."');
				resultIntegrityMsg.inject($('jsn-check-integrity-result'));

				var jsonRequest = new Request.JSON({url: actionIntegrityUrl, onSuccess: function(jsonObj){
					if(jsonObj.integrity == '1') {
						resultIntegrityMsg.set('class','jsn-modification-exist');
						resultIntegrityMsg.set('html','<span>".JText::_('SOME_FILES_HAVE_BEEN_MODIFIED', true).". <a href=\"../index.php?template=".$templateName."&tmpl=jsn_listmodifiedfiles\" class=\"link-action\" id=\"see-file-details\" rel=\"{handler: \'iframe\', size: {x: 570, y:320}}\" onclick=\"SqueezeBox.fromElement($(\'see-file-details\'), {parse: \'rel\'}); return false;\">".JText::_('SEE_DETAILS_FILE', true)."</a></span>');
					} else if (jsonObj.integrity == '0') {
						resultIntegrityMsg.set('class','jsn-no-modification');
						resultIntegrityMsg.set('html','".JText::_('NO_FILES_MODIFICATION_FOUND', true)."');
					} else {
						resultIntegrityMsg.set('class','jsn-no-modification');
						resultIntegrityMsg.set('html','".JText::_('NO_CHECKSUM_FILE_FOUND', true)."');
					}
					resultIntegrityMsg.inject($('jsn-check-integrity-result'));
				}}).get({'template': '".$templateName."', 'tmpl': 'jsn_runajax', 'task': 'checkFilesIntegrity'});
			}

			window.addEvent('domready', function(){
			   $('jsn-check-version').set('html', '');
				var actionVersionUrl = '".$frontIndexPath."';
				var resultVersionMsg = new Element('span');
				resultVersionMsg.set('class','jsn-version-checking');
				resultVersionMsg.set('html',' - ".JText::_('CHECKING_VERSION', true)."');
				resultVersionMsg.inject($('jsn-check-version-result'));
				var jsonRequest = new Request.JSON({url: actionVersionUrl, onSuccess: function(jsonObj){
					if(jsonObj.connection && jsonObj.version != '') {
						if(jsonObj.version == '".$manifestCache->version."') {
							resultVersionMsg.set('class','jsn-latest-version');
							resultVersionMsg.set('html',' - ".JText::_('THE_LATEST_VERSION')."');
						} else {
							resultVersionMsg.set('class','jsn-outdated-version');
							resultVersionMsg.set('html',' - <a href=\"../index.php?template=".$templateName."&tmpl=jsn_autoupdater&template_style_id=".JRequest::getInt('id')."\" class=\"link-action\" id=\"jsn-auto-update\" rel=\"{handler: \'iframe\', size: {x: 750, y:680}}\" onclick=\"SqueezeBox.fromElement($(\'jsn-auto-update\'), {parse: \'rel\'}); return false;\">".JText::_('UPDATE_NOW', true)."'+jsonObj.version+'</a>');
						}
					} else {
						resultVersionMsg.set('class','jsn-connection-fail');
						resultVersionMsg.set('html',' <span class=\"grey\">-</span> ".JText::_('CONNECTION_FAILED', true)."');
					}
					resultVersionMsg.inject($('jsn-check-version-result'));
				}}).get({'template': '".$templateName."', 'tmpl': 'jsn_runajax', 'task': 'checkVersion'});
				$('jsn-check-integrity').addEvent('click', function() {checkIntegrity()});
			});
		");

		$explodedTemplateName = explode('_', $templateName);
		if(strstr($result->copyright, $result->author) === false)
		{
			$copyright = $result->copyright. ' (<a target="_blank" title="'.$result->author.'" href="'.$result->authorUrl.'">'.$result->author.'</a>)';
		}
		else
		{
			$copyright = str_replace($result->author, '<a target="_blank" title="'.$result->author.'" href="'.$result->authorUrl.'">'.$result->author.'</a>', $result->copyright);
		}
		$staticLink = $result->authorUrl.'/joomla-templates/'.@$explodedTemplateName[0].'-'.@$explodedTemplateName[1].'.html';

		if (!$isJoomla30)
		{
			$html = '<div class="jsn-about">';
			$html .= '<div class="jsn-product-intro">';
			$html .= '<table width="100%"><tbody><tr><td width="10">';
			$html .= '<div class="jsn-template-thumbnail">';
			$html .= '<img src ="../templates/'.$templateName.'/template_thumbnail.png" width="206" height="150" />';
			$html .= '</div>';
			$html .= '</td><td>';
			$html .= '<div class="jsn-template-details">';
			$html .= '<h2>'.str_replace('_', ' ', $result->name).' '. $result->edition .'</h2>';
			if($result->edition == 'STANDARD')
			{
				$html .= '<p>'.JText::_('UPGRADE_TO_UNLIMITED').' <a class="link-action jsn-modal" rel="{handler: \'iframe\', size: {x: 750, y: 650}, closable: false}" href="../index.php?template=' . strtolower($result->name) . '&tmpl=jsn_upgrade&template_style_id=' . JRequest::getInt('id') . '">' . JText::_('JSN_UPGRADE_TEMPLATE') . '</a></p>';
			}
			$html .= '<hr />';
			$html .= '<dl>';
			$html .= '<dt>'.JText::_('VERSION').':</dt><dd><strong class="jsn-current-version">'.$manifestCache->version.'</strong><a href="javascript:void(0);" class="link-action" id="jsn-check-version"></a><span id="jsn-check-version-result"></span></dd>';
			$html .= '<dt>'.JText::_('COPYRIGHT').':</dt><dd>'.$copyright.'</dd>';
			$html .= '<dt>'.JText::_('INTEGRITY').':</dt><dd><a href="javascript:void(0);" class="link-action" id="jsn-check-integrity">'.JText::_('CHECK_FOR_FILES_MODIFICATION').'</a><span id="jsn-check-integrity-result"></span></dd>';
			$html .= '</dl></td></tr></tbody></table></div><div class="jsn-product-cta">';
			$html .= '<div style="float: left; width: 60%;"><ul class="horizontal-list"><li><a rel="{handler: \'iframe\', size: {x: 640, y: 510}}" href="'.$result->authorUrl.'/'.@$explodedTemplateName[2].'-joomla-templates-promo.html" class="link-button jsn-modal"><span class="icon-gallery">'.JText::_('SEE_OTHER_TEMPLATES').'</span></a></li></ul></div>';
			$html .= '<div style="float: right; text-align: right;"><ul class="horizontal-list">';
			$html .= '<li><a target="_blank" title="Connect with us on Facebook" href="http://www.facebook.com/joomlashine"><img width="24" height="24" alt="Connect with us on Facebook" src="' . JURI::root() . 'templates/' . strtolower($result->name) . '/admin/images/icon-facebook.png"></a></li>';
			$html .= '<li><a target="_blank" title="Follow us on Twitter" href="http://www.twitter.com/joomlashine"><img width="24" height="24" alt="Follow us on Twitter" src="' . JURI::root() . 'templates/' . strtolower($result->name) . '/admin/images/icon-twitter.png"></a></li>';
			$html .= '<li><a target="_blank" title="Watch us on YouTube" href="http://www.youtube.com/joomlashine"><img width="24" height="24" alt="Watch us on YouTube" src="' . JURI::root() . 'templates/' . strtolower($result->name) . '/admin/images/icon-youtube.png"></a></li>';
			$html .= '</ul></div>';
			$html .= '</div>';
			$html .= '</div>';
		}
		else {
			$html = '<div class="jsn-about">';
			$html .= '<div class="jsn-product-intro">';
			$html .= '<table width="100%"><tbody><tr><td width="206">';
			$html .= '<div class="jsn-template-thumbnail">';
			$html .= '<img src ="../templates/'.$templateName.'/template_thumbnail.png" width="206" height="150" />';
			$html .= '</div>';
			$html .= '</td><td>';
			$html .= '<div class="jsn-template-details">';
			$html .= '<h2>'.str_replace('_', ' ', $result->name).' '. $result->edition .'</h2>';

			if(strtolower($result->edition) == 'standard')
			{
				$html .= '<p>'.JText::_('UPGRADE_TO_UNLIMITED').' <a class="link-action jsn-modal" rel="{handler: \'iframe\', size: {x: 750, y: 650}, closable: false}" href="../index.php?template=' . strtolower($result->name) . '&tmpl=jsn_upgrade&template_style_id=' . JRequest::getInt('id') . '">' . JText::_('JSN_UPGRADE_TEMPLATE') . '</a></p>';
			}
			$html .= '<hr />';
			$html .= '<dl>';
			$html .= '<dt>'.JText::_('VERSION').':</dt><dd><strong class="jsn-current-version">'.$manifestCache->version.'</strong><a href="javascript:void(0);" class="link-action" id="jsn-check-version"></a><span id="jsn-check-version-result"></span></dd>';
			$html .= '<dt>'.JText::_('COPYRIGHT').':</dt><dd>'.$copyright.'</dd>';
			$html .= '<dt>'.JText::_('INTEGRITY').':</dt><dd><a href="javascript:void(0);" class="link-action" id="jsn-check-integrity">'.JText::_('CHECK_FOR_FILES_MODIFICATION').'</a><span id="jsn-check-integrity-result"></span></dd>';
			$html .= '</dl></td></tr></tbody></table></div><div class="jsn-product-cta">';
			$html .= '	<div class="jsn-see-more-products"><a rel="{handler: \'iframe\', size: {x: 640, y: 510}}" href="'.$result->authorUrl.'/'.@$explodedTemplateName[2].'-joomla-templates-promo.html" class="btn jsn-modal"><span class="ico-gallery">'.JText::_('SEE_OTHER_TEMPLATES').'</span></a></div>';
			$html .= '	<div class="jsn-follow-links">';
			$html .= '		<a target="_blank" title="Connect with us on Facebook" href="http://www.facebook.com/joomlashine"><img width="24" height="24" alt="Connect with us on Facebook" src="' . JURI::root() . 'templates/' . strtolower($result->name) . '/admin/images/icon-facebook.png"></a>';
			$html .= '		<a target="_blank" title="Follow us on Twitter" href="http://www.twitter.com/joomlashine"><img width="24" height="24" alt="Follow us on Twitter" src="' . JURI::root() . 'templates/' . strtolower($result->name) . '/admin/images/icon-twitter.png"></a>';
			$html .= '		<a target="_blank" title="Watch us on YouTube" href="http://www.youtube.com/joomlashine"><img width="24" height="24" alt="Watch us on YouTube" src="' . JURI::root() . 'templates/' . strtolower($result->name) . '/admin/images/icon-youtube.png"></a>';
			$html .= '	</div>';
			$html .= '	<div class="clearfix"></div>';
			$html .= '</div>';
			$html .= JText::_('TEMPLATE_SUPPORT');
			$html .= '</div>';
		}

		return $html;
	}
}
