<?php
/**
 * @author    JoomlaShine.com http://www.joomlashine.com
 * @copyright Copyright (C) 2008 - 2011 JoomlaShine.com. All rights reserved.
 * @license   GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once 'jsn_document.php';
// Include JSN Utils
$jsnutils = JSNUtils::getInstance();

$app 		= JFactory::getApplication();
// Get Header URI
$header_stuff = $this->getHeadData();

// Get URI
$uri = JFactory::getURI();

/****************************************************************/
/* PUBLIC TEMPLATE PARAMETERS */
/****************************************************************/

// Path to logo image starting from the Joomla! root folder (! without preceding slash !)
$enable_colored_logo = ($this->params->get("enableColoredLogo", 0) == 1)?true:false;

// Logo Path
$logo_path = $this->params->get("logoPath", "");
if ($logo_path != "")
{
	$logo_path = $this->baseurl.'/'.htmlspecialchars($logo_path);
}

/* URL where logo image should link to (! without preceding slash !)
   Leave this box empty if you want your logo to be clickable. */
$logo_link = $this->params->get("logoLink", "");
if (strpos($logo_link, "http")=== false && $logo_link != '')
{
	$logo_link = $jsnutils->trimPreceddingSlash($logo_link);
	$logo_link = $this->baseurl."/".$logo_link;
}

// Slogan text to be attached to the logo image ALT text for SEO purpose.
$logo_slogan = $this->params->get("logoSlogan", "");

// Overall template width.
$template_width = $this->params->get("templateWidth", "narrow");

// Define custom width for template in narrow mode
$narrow_width = intval($this->params->get("narrowWidth", "960"));

// Define custom width for template in wide mode
$wide_width = intval($this->params->get("wideWidth", "1150"));

// Define custom width for template in float mode
$float_width = intval($this->params->get("floatWidth", "90"));
$float_width = ($float_width > 100)?100:$float_width;

/* Promo left column width specified in percentage.
   Only whole number is allowed, for example 25% - correct, 25.5% - incorrect */
$promo_left_width = intval($this->params->get("promoLeftWidth", "23"));

/* Promo right column width specified in percentage.
   Only whole number is allowed, for example 25% - correct, 25.5% - incorrect */
$promo_right_width = intval($this->params->get("promoRightWidth", "23"));

/* Left column width specified in percentage.
   Only whole number is allowed, for example 25% - correct, 25.5% - incorrect */
$left_width = intval($this->params->get("leftWidth", "23"));

/* Right column width specified in percentage.
   Only whole number is allowed, for example 25% - correct, 25.5% - incorrect */
$right_width = intval($this->params->get("rightWidth", "23"));

/* InnerLeft column width specified in percentage.
   Only whole number is allowed, for example 25% - correct, 25.5% - incorrect */
$innerleft_width = intval($this->params->get("innerleftWidth", "28"));

/* InnerRight column width specified in percentage.
   Only whole number is allowed, for example 25% - correct, 25.5% - incorrect */
$innerright_width = intval($this->params->get("innerrightWidth", "28"));

// Definition whether to show mainbody on frontpage page or not
$show_frontpage = ($this->params->get("showFrontpage", 1) == 1)?true:false;

// Template color: orange | red | cyan | green | yellow | pink
$template_color = $this->params->get("templateColor", "red");

/* Template text style:
   1 - Business / Corporation
   2 - Personal / Blog
   3 - News / Magazines */
$template_textstyle = $this->params->get("templateTextStyle", "business");

// Template text size
$template_textsize = $this->params->get("templateTextSize", "medium");

// Template special font
$template_specialfont = ($this->params->get("templateSpecialFont", 1) == 1)?true:false;

// Definition whether to enable CSS3 effect or not
$enable_css3effect = ($this->params->get("enableCSS3Effect", 1) == 1)?true:false;

// Define custom width for subpanel of Main menu
$mm_width = intval($this->params->get("mmWidth", "185"));

// Define custom width for subpanel of Side menu
$sm_width = intval($this->params->get("smWidth", "185"));

// Definition how to present site tools panel
$sitetools_presentation = $this->params->get("sitetoolsPresentation", "menu");

// Definition whether to enable text size selector or not
$enable_textsizer = ($this->params->get("enableTextSizer", 1) == 1)?true:false;

// Definition whether to enable width selector or not
$enable_widthselector = ($this->params->get("enableWidthSelector", 1) == 1)?true:false;

// Definition whether to enable color selector or not
$enable_colorselector = ($this->params->get("enableColorSelector", 1) == 1)?true:false;

// Color icons to be shown in color selector
$color_icons = $this->params->get("colorIcons", "orange,red,cyan,green,yellow,pink");

// Definition whether to wrap website slogan to h1 tag
$enable_toph1 = ($this->params->get("enableTopH1", 1) == 1)?true:false;

// Definition whether to enable "Go to top" link or not
$enable_gotoplink = ($this->params->get("enableGotopLink", 1) == 1)?true:false;

/* Definition whether to enable auto icon link or not.
   Icons can still be assigned to links by class attribute even if this option is turned off */
$enable_iconlinks = ($this->params->get("enableIconLinks", 0) == 1)?true:false;

// Definition whether to optimize webpage content for printing or not
$enable_printingoptimization = ($this->params->get("enablePrintingOptimization", 0) == 1)?true:false;

// Definition of analytics code and it position
$analytics_code_position 	= (int)($this->params->get("analyticsCodePosition", 1));
$analytics_code 			= ($this->params->get("analyticsCode", ""));

// Definition of custom CSS files to be included
$custom_css_files 			= array();
$custom_css_files 			= preg_split('/\r\n|\r|\n/', $this->params->get("customCSS"));

// Definition whether to compress CSS/JS or not
$css_js_compression 		= (int)($this->params->get("cssJsCompression", 0));
$cache_folder				= $this->params->get('cacheFolder');
$cache_folder				= $jsnutils->trimSlash($cache_folder);

// Definition preload squeezebox or not
$enable_squeezebox			= (int)($this->params->get("enableSqueezebox", 0));

// Get mobile setting
$enable_mobile_support		= ($this->params->get("enableMobileSupport", 1) == 1)?true:false;
$enable_mobile_menu_sticky		= ($this->params->get("enableMobileMenuSticky", 1) == 1)?true:false;
$show_desktop_switcher	 	= ($this->params->get("showDesktopSwitcher", 1) == 1)?true:false;

// Mobile Logo Path
$mobile_logo_path = $this->params->get("mobileLogoPath", "");

/****************************************************************/
/* PRIVATE TEMPLATE PARAMETERS */
/****************************************************************/

// Get browser info
$brower_info 		= $jsnutils->getBrowserInfo(null);
$is_ie				= (@$brower_info['browser'] == 'msie');
$is_ie7				= (@$brower_info['browser'] == 'msie' && (int) @$brower_info['version'] == 7);
$ieoffset 			= ($is_ie7)?0.1:0;

// Get template details
$template_details   	= json_decode($jsnutils->getTemplateManifestCache());

	$template_prefix 		= $template_details->name . '-';
	$template_path 			= $this->baseurl.'/templates/'.$this->template;
	$template_abs_path 		= JPATH_THEMES. DIRECTORY_SEPARATOR .$this->template;
	$template_abs_path		= str_replace('/', DIRECTORY_SEPARATOR, $template_abs_path);
	$template_js_abs_path 	= $template_abs_path. DIRECTORY_SEPARATOR .'js';
	$template_css_abs_path 	= $template_abs_path. DIRECTORY_SEPARATOR .'css';
	$template_direction 	= $this->direction;
	$has_right				= $this->countModules('right');
	$has_left				= $this->countModules('left');
	$has_promo 				= $this->countModules('promo');
	$has_promoleft 			= $this->countModules('promo-left');
	$has_promoright 		= $this->countModules('promo-right');
	$has_innerleft 			= $this->countModules('innerleft');
	$has_innerright 		= $this->countModules('innerright');

	// Get system template details
	$template_sys_abs_path 		= str_replace('/', DIRECTORY_SEPARATOR, JPATH_THEMES). DIRECTORY_SEPARATOR .'system';
	$template_sys_css_abs_path 	= str_replace('/', DIRECTORY_SEPARATOR, JPATH_THEMES). DIRECTORY_SEPARATOR .'system'. DIRECTORY_SEPARATOR .'css';

$pageclass 		= '';
$not_homepage 	= true;
$menus 			= $app->getMenu();
$menu 			= $menus->getActive();
if (is_object($menu)) {
	// Set page class suffix
	$params = JMenu::getInstance('site')->getParams( $menu->id );
	$pageclass = $params->get( 'pageclass_sfx', '');

	// Set homepage flag
	$lang = JFactory::getLanguage();
	$default_menu = $menus->getDefault($lang->getTag());
	if (is_object($default_menu)) {
		$not_homepage = ($menu->id != $default_menu->id);
	}
}

// Define to show main body on homepage or not
if($show_frontpage == false) {
	$show_frontpage = $not_homepage;
}

// check System Cache - Plugin state
$systemcache_enabled = $jsnutils->checkSystemCache();
if ($systemcache_enabled) {
	//$show_desktop_switcher = $enable_mobile_support = false;
	$enable_textsizer = $enable_widthselector = $enable_colorselector = false;
}

// Define to enable mobile layout or not
$enable_mobile = $enable_mobile_support;

// Check template attributes to override settings
$tattrs = $jsnutils->getTemplateAttributes($jsn_template_attrs, $template_prefix, $pageclass);
if ($tattrs['width'] != null) $template_width = $tattrs['width'];
if ($tattrs['textstyle'] != null) $template_textstyle = $tattrs['textstyle'];
if ($tattrs['textsize'] != null) $template_textsize = $tattrs['textsize'];
if ($tattrs['color'] != null) $template_color = $tattrs['color'];
if ($tattrs['direction'] != null) $template_direction = $tattrs['direction'];
if ($tattrs['specialfont'] != null) $template_specialfont = ($tattrs['specialfont'] == 'yes')?true:false;
if ($tattrs['promoleftwidth'] != null) $promo_left_width = $tattrs['promoleftwidth'];
if ($tattrs['promorightwidth'] != null) $promo_right_width = $tattrs['promorightwidth'];
if ($tattrs['leftwidth'] != null) $left_width = $tattrs['leftwidth'];
if ($tattrs['rightwidth'] != null) $right_width = $tattrs['rightwidth'];
if ($tattrs['innerleftwidth'] != null) $innerleft_width = $tattrs['innerleftwidth'];
if ($tattrs['innerrightwidth'] != null) $innerright_width = $tattrs['innerrightwidth'];
if ($tattrs['mobile'] != null) $enable_mobile = ($tattrs['mobile'] == 'yes')?true:false;

/****************************************************************/
/* CSS Compression */
/****************************************************************/

// Definition whether to enable css compression or not
$cache_folder_path		= JPATH_ROOT.'/'.$cache_folder;
$cache_folder_writable	= $jsnutils->checkFolderWritable($cache_folder_path);

$css_compression 		= (($css_js_compression == 1 || $css_js_compression == 2) && $cache_folder_writable);
$css_compress_files 	= array();
$missing_css_files 		= array();

$css_compress_files[JURI::root(true).'/templates/system/css/system.css']['file_abs_path']	= $template_sys_css_abs_path;
$css_compress_files[JURI::root(true).'/templates/system/css/system.css']['file_name']		= 'system.css';

$css_compress_files[JURI::root(true).'/templates/system/css/general.css']['file_abs_path']	= $template_sys_css_abs_path;
$css_compress_files[JURI::root(true).'/templates/system/css/general.css']['file_name']		= 'general.css';

$css_compress_files[$template_path.'/css/template.css']['file_abs_path']	= $template_css_abs_path;
$css_compress_files[$template_path.'/css/template.css']['file_name']		= 'template.css';

$css_compress_files[$template_path.'/css/template_'.$template_color.'.css']['file_abs_path']	= $template_css_abs_path;
$css_compress_files[$template_path.'/css/template_'.$template_color.'.css']['file_name']		= 'template_'.$template_color.'.css';

if ($enable_iconlinks)
{
	$css_compress_files[$template_path.'/css/jsn_iconlinks.css']['file_abs_path']	= $template_css_abs_path;
	$css_compress_files[$template_path.'/css/jsn_iconlinks.css']['file_name']		= 'jsn_iconlinks.css';
}

if ($template_direction == "rtl")
{
	$css_compress_files[$template_path.'/css/jsn_rtl.css']['file_abs_path']	= $template_css_abs_path;
	$css_compress_files[$template_path.'/css/jsn_rtl.css']['file_name']		= 'jsn_rtl.css';
}

if($enable_mobile)
{
	$css_compress_files[$template_path.'/css/jsn_mobile.css']['file_abs_path']	= $template_css_abs_path;
	$css_compress_files[$template_path.'/css/jsn_mobile.css']['file_name']		= 'jsn_mobile.css';
}

if ($enable_css3effect)
{
	$css_compress_files[$template_path.'/css/jsn_css3.css']['file_abs_path']	= $template_css_abs_path;
	$css_compress_files[$template_path.'/css/jsn_css3.css']['file_name']		= 'jsn_css3.css';
}

if ($is_ie7)
{
	$css_compress_files[$template_path.'/css/jsn_fixie7.css']['file_abs_path']	= $template_css_abs_path;
	$css_compress_files[$template_path.'/css/jsn_fixie7.css']['file_name']		= 'jsn_fixie7.css';
}

	// load Squeezebox
	if ($enable_squeezebox)
	{
		$css_compress_files[JURI::root(true).'/media/system/css/modal.css']['file_abs_path']	= JPATH_BASE. DIRECTORY_SEPARATOR .'media'. DIRECTORY_SEPARATOR .'system'. DIRECTORY_SEPARATOR .'css';
		$css_compress_files[JURI::root(true).'/media/system/css/modal.css']['file_name']		= 'modal.css';
	}

// Include all CSS files in HEAD section
if ($css_compression)
{
	$jsnutils->getAllFileInHeadSection($header_stuff, 'css', $css_compress_files);
	// Apply new data
	//$this->setHeadData($header_stuff);
	@JDocumentHTML2::setHeadData($header_stuff);
}

	/* Include CSS override for third-party extensions */
	// apply K2 style
	if ($jsnutils->checkK2()) {
		$css_compress_files[$template_path.'/ext/k2/jsn_ext_k2.css']['file_abs_path']	= $template_abs_path. DIRECTORY_SEPARATOR .'ext'. DIRECTORY_SEPARATOR .'k2';
		$css_compress_files[$template_path.'/ext/k2/jsn_ext_k2.css']['file_name']		= 'jsn_ext_k2.css';
	}

// Include custom CSS files
if (!empty($custom_css_files))
{
	foreach ($custom_css_files as $css)
	{
		if (!empty($css))
		{
			$css_abs_path = $template_abs_path.'/css/'.$css;
			if (!file_exists($css_abs_path))
			{
				$missing_css_files[] = $css;
			}
			else
			{
				$css_compress_files[$template_path.'/css/'.$css]['file_abs_path'] 	= $template_css_abs_path;
				$css_compress_files[$template_path.'/css/'.$css]['file_name']	 	= $css;
			}
		}
	}
}

/****************************************************************/
/* Javascript Compression */
/****************************************************************/
$js_compression 	= (($css_js_compression == 1 || $css_js_compression == 3) && $cache_folder_writable);
$js_compress_files 	= array();

// Include all JS files in HEAD section
if ($js_compression)
{
	$jsnutils->getAllFileInHeadSection($header_stuff, 'js', $js_compress_files);
	// Apply new data
	@JDocumentHTML2::setHeadData($header_stuff);
}

	/* Make sure that mootools-more.js is included so that SmoothScroll (for GoToTop link) will work */
	$js_compress_files[JURI::root(true).'/media/system/js/mootools-more.js']['file_abs_path']	= JPATH_BASE. DIRECTORY_SEPARATOR .'media'. DIRECTORY_SEPARATOR .'system'. DIRECTORY_SEPARATOR .'js';
	$js_compress_files[JURI::root(true).'/media/system/js/mootools-more.js']['file_name']		= 'mootools-more.js';

/* Include this small script to prevent conflict with jQuery from un-well-written components */
$js_compress_files[$template_path.'/js/jsn_noconflict.js']['file_abs_path']	= $template_js_abs_path;
$js_compress_files[$template_path.'/js/jsn_noconflict.js']['file_name']		= 'jsn_noconflict.js';

$js_compress_files[$template_path.'/js/jsn_utils.js']['file_abs_path']	= $template_js_abs_path;
$js_compress_files[$template_path.'/js/jsn_utils.js']['file_name']		= 'jsn_utils.js';

$js_compress_files[$template_path.'/js/jsn_template.js']['file_abs_path']	= $template_js_abs_path;
$js_compress_files[$template_path.'/js/jsn_template.js']['file_name']		= 'jsn_template.js';

	// load Squeezebox
	$noModalScript = !isset($js_compress_files[JURI::root(true).'/media/system/js/modal.js']) && !isset($header_stuff['scripts'][JURI::root(true).'/media/system/js/modal.js']);
	if ($enable_squeezebox && $noModalScript === true)
	{
		$js_compress_files[JURI::root(true).'/media/system/js/modal.js']['file_abs_path']	= JPATH_BASE. DIRECTORY_SEPARATOR .'media'. DIRECTORY_SEPARATOR .'system'. DIRECTORY_SEPARATOR .'js';
		$js_compress_files[JURI::root(true).'/media/system/js/modal.js']['file_name']		= 'modal.js';
	}

$css_active_profile = '';
$js_active_profile 	= '';

if ($css_compression)
{
	foreach ($css_compress_files as $file_name => $file_attribute)
	{
		foreach ($css_compress_files as $file_name => $file_attribute)
		{
			$css_profile_elements[] = $file_attribute['file_abs_path']. DIRECTORY_SEPARATOR .$file_attribute['file_name'];
		}
		$css_active_profile = md5(implode(',', $css_profile_elements));
	}
	$css_active_profile = md5(implode(',', $css_profile_elements));
}

if ($js_compression)
{
	foreach ($js_compress_files as $file_name => $file_attribute)
	{
		foreach ($js_compress_files as $file_name => $file_attribute)
		{
			$js_profile_elements[] = $file_attribute['file_abs_path']. DIRECTORY_SEPARATOR .$file_attribute['file_name'];
		}
		$js_active_profile = md5(implode(',', $js_profile_elements));
	}
	$js_active_profile = md5(implode(',', $js_profile_elements));
}

// Custom template JS declarations
$javascript_params = '
		var templateParams					= {};
		templateParams.templatePrefix		= "'.$template_prefix.'";
		templateParams.templatePath			= "'.$template_path.'";
		templateParams.enableRTL			= '.(($template_direction == "rtl")?'true':'false').';
		templateParams.enableGotopLink		= '.($enable_gotoplink?'true':'false').';
		templateParams.enableMobile			= '.($enable_mobile?'true':'false').';
		templateParams.enableMobileMenuSticky	= '.($enable_mobile_menu_sticky?'true':'false').';

		JSNTemplate.initTemplate(templateParams);
';

// load Squeezebox
if ($enable_squeezebox)
{
	$javascript_params .= '
		window.addEvent("domready", function() {
			SqueezeBox.initialize({});
			SqueezeBox.assign($$("a.modal"), {
				parse: "rel"
			});
		});
	';
}

// Define compression options
$compress_options = array(
	'cache_path' 				=> $cache_folder_path,
	'template_path' 			=> $template_path,
	'template_name'				=> $this->template,
	'template_abs_path'			=> $template_abs_path,
	'css_active_profile'		=> $css_active_profile,
	'js_active_profile'			=> $js_active_profile
);

// Cache folder house-keeping
$jsnutils->cleanupCacheFolder($this->template, $css_js_compression, $cache_folder_path);

// Check joomla version
$joomlaVersion = new JVersion();
$currentVersion = $joomlaVersion->getShortVersion();
$versionParts = explode('.', $currentVersion);
$version = implode('', array_slice($versionParts, 0, 2));

$versionClassName = 'jsn-joomla-' . $version;
if (version_compare($currentVersion, '3.0', '>=')) {
	// Add JavaScript Frameworks
	JHtml::_('bootstrap.framework');

	// Add Css Frameworks
	JHtmlBootstrap::loadCss();

	// Load optional rtl Bootstrap css and Bootstrap bugfixes
	JHtmlBootstrap::loadCss($includeMaincss = true, $this->direction);
}

?>
