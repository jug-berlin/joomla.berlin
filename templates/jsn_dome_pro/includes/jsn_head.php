<?php
/**
 * @author    JoomlaShine.com http://www.joomlashine.com
 * @copyright Copyright (C) 2008 - 2011 JoomlaShine.com. All rights reserved.
 * @license   GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 */

	// Include meta tags
	if($enable_mobile) {
		echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />';
	}

	// Include CSS files
	if ($css_compression)
	{
		require_once 'lib/jsn_compression.php';
		$objJSNCompression = new JSNCompression('css', $css_compress_files, $compress_options);
		$objJSNCompression->executeCompress();
		$this->addStylesheet(JURI::root(true).'/'.$cache_folder.'/'.$this->template.'_css_'.$compress_options['css_active_profile'].'.css');

		// remove modal.css from header
		$header_stuff = $this->getHeadData();
		unset($header_stuff['styleSheets'][JURI::root(true).'/media/system/css/modal.css']);
		$this->setHeadData($header_stuff);
	}
	else
	{
		foreach ($css_compress_files as $key => $value)
		{
			$this->addStyleSheet($key);
		}
	}

	if ($enable_printingoptimization)
	{
		$this->addStylesheet($template_path."/css/print.css", 'text/css', 'Print');
	}

	if (!empty($missing_css_files))
	{
		foreach ($missing_css_files as $css)
		{
			echo "<!-- Custom CSS file \"$template_path/css/$css\" does not exist -->\n";
		}
	}

	if ($template_specialfont)
	{
		$specialfont_url = 'https://fonts.googleapis.com/css?family=';
		$specialfont_url .= str_replace(' ','+',str_replace(array('"',"'"),'',$jsn_textstyles_config[$template_textstyle]['font-s']));
		$specialfont_url .= ($jsn_textstyles_config[$template_textstyle]['font-sw'] != '')?':'.$jsn_textstyles_config[$template_textstyle]['font-sw']:'';
		$this->addStylesheet($specialfont_url);
	}

	// Inline CSS declaration for template styling
	echo '<style type="text/css">';

	// Template desktop layout
	// Setup template width parameter
	$twidth = 0;
	switch ($template_width) {
		case 'narrow':
			$twidth = $narrow_width;
			break;
		case 'wide':
			$twidth = $wide_width;
			break;
		case 'float':
			$twidth = $float_width;
			break;
	}

	if ($twidth > 100) {
		echo '
	#jsn-page {
		width: '.$twidth.'px;
	}
		';
	} else {
		echo '
	#jsn-page {
		width: '.$twidth.'%;
	}
		';
	}

	// Setup width of promo area
	$tw = 100;
	echo '
	#jsn-pos-promo-left {
		float: left;
		width: '.$promo_left_width.'%;
		left: -'.($tw-$ieoffset).'%;
	}
	#jsn-pos-promo {
		width: '.($tw-$ieoffset).'%;
		left: '.(($has_promoleft)?$promo_left_width.'%':0).';
	}
	#jsn-pos-promo-right {
		float: right;
		width: '.$promo_right_width.'%;
	}
	';
	if ($has_promoright) {
		$tw -= $promo_right_width;
		echo '
	#jsn-pos-promo {
		float: left;
		width: '.($tw-$ieoffset).'%;
	}
		';
	}
	if ($has_promoleft) {
		$tw -= $promo_left_width;
		echo '
	#jsn-pos-promo {
		width: '.($tw-$ieoffset).'%;
		float: right;
		left: auto;
	}
	#jsn-pos-promo-left {
		left: auto;
	}
		';
	}
	if ($has_promoleft && $has_promoright) {
		$tw -= $promo_left_width;
		echo '
	#jsn-pos-promo {
		float: left;
		left: '.(($has_promoleft)?$promo_left_width.'%':0).';
	}
	#jsn-pos-promo-left {
		left: -'.($tw+$promo_left_width).'%;
	}
		';
	}
	if (!$has_promo) {
		echo '
	#jsn-pos-promo-left {
		left: auto;
		display: auto;
	}
		';
	}

	// Setup width of content area
	$tw = 100;
	if ($has_left) {
		$tw -= $left_width;
	}
	if ($has_right) {
		$tw -= $right_width;
	}

	echo '
	#jsn-leftsidecontent {
		float: left;
		width: '.$left_width.'%;
		left: -'.($tw-$ieoffset).'%;
	}
	#jsn-maincontent {
		float: left;
		width: '.($tw-$ieoffset).'%;
		left: '.(($has_left)?$left_width.'%':0).';
	}
	#jsn-rightsidecontent {
		float: right;
		width: '.$right_width.'%;
	}
	';

	$tw = 100;
	if ($has_innerleft) {
		$tw -= $innerleft_width;
		echo '
	#jsn-maincontent_inner1 {
		right: '.(100 - $innerleft_width).'%;
	}
	#jsn-maincontent_inner2 {
		left: '.(100 - $innerleft_width).'%;
	}
		';
	}
	if ($has_innerright) {
		$tw -= $innerright_width;
		echo '
	#jsn-maincontent_inner3 {
		left: '.(100 - $innerright_width).'%;
	}
	#jsn-maincontent_inner4 {
		right: '.(100 - $innerright_width).'%;
	}
		';
	}

	echo '
	#jsn-pos-innerleft {
		float: left;
		width: '.$innerleft_width.'%;
		left: -'.($tw-$ieoffset).'%;
	}
	#jsn-centercol {
		float: left;
		width: '.($tw-$ieoffset).'%;
		left: '.(($has_innerleft)?$innerleft_width.'%':0).';
	}
	#jsn-pos-innerright {
		float: right;
		width: '.$innerright_width.'%;
	}
	';
	// Setup font regular text
	echo '
		body.jsn-textstyle-'.$template_textstyle.' {
			font-family: '.$jsn_textstyles_config[$template_textstyle]['font-a'].';
		}
		';

	// Setup font heading and menu text
	$elements_length = count($jsn_font_b_elements);
	for($i=0;$i<$elements_length;$i++){
		echo '
		body.jsn-textstyle-'.$template_textstyle.' '.$jsn_font_b_elements[$i].(($i < $elements_length-1)?",":' {');
	}
	if($template_specialfont) {
		echo "
				font-family: '".str_replace('+', ' ', str_replace(array('"',"'"),'',$jsn_textstyles_config[$template_textstyle]['font-s']))."', ".$jsn_textstyles_config[$template_textstyle]['font-b'].";
			}
		";
	} else {
		echo "
				font-family: ".$jsn_textstyles_config[$template_textstyle]['font-b'].";
			}
		";
	}

	// Setup font size
	echo '
		body.jsn-textstyle-'.$template_textstyle.'.jsn-textsize-'.$template_textsize.' {
			font-size: '.$jsn_textstyles_config[$template_textstyle]["size-$template_textsize"].';
		}
	';

	// Setup main menu width parameter
	if($mm_width) {
		$mm_margin = $mm_width - 1;
		echo '
		div.jsn-modulecontainer ul.menu-mainmenu ul,
		div.jsn-modulecontainer ul.menu-mainmenu ul li {
			width: '.$mm_width.'px;
		}
		div.jsn-modulecontainer ul.menu-mainmenu ul ul {
		';
		if($template_direction == 'ltr'){
			echo '	margin-left: '.$mm_margin.'px;';
		}
		if($template_direction == 'rtl'){
			echo '	margin-right: '.$mm_margin.'px;';
		}
		echo '
		}
		';
	}

	// Setup slide menu width parameter
	if($sm_width) {
		$sm_margin = $sm_width + 2;
		echo '
		div.jsn-modulecontainer ul.menu-sidemenu ul,
		div.jsn-modulecontainer ul.menu-sidemenu ul li {
			width: '.$sm_width.'px;
		}
		div.jsn-modulecontainer ul.menu-sidemenu li ul {
			right: -'.$sm_margin.'px;
		}
		body.jsn-direction-rtl div.jsn-modulecontainer ul.menu-sidemenu li ul {
			left: -'.$sm_margin.'px;
			right: auto;
		}
		div.jsn-modulecontainer ul.menu-sidemenu ul ul {
		';
		if($template_direction == 'ltr'){
			echo '	margin-left: '.$sm_margin.'px;';
		}
		if($template_direction == 'rtl'){
			echo '	margin-right: '.$sm_margin.'px;';
		}
		echo '
		}
		';
	}

	// Include CSS3 support for IE browser
	if($is_ie) {
		echo '
		.text-box,
		.text-box-highlight,
		.text-box-highlight:hover {
			behavior: url('.JURI::root(true).'/templates/'.strtolower($this->template).'/includes/PIE.htc);
		}
		.link-button {
			zoom: 1;
			position: relative;
			behavior: url('.JURI::root(true).'/templates/'.strtolower($this->template).'/includes/PIE.htc);
		}
		';
	}

	echo '</style>';

	if ($js_compression)
	{
		$topScripts = array();

		// Compress all JS files collected
		require_once 'lib/jsn_compression.php';

		$objJSNCompression = new JSNCompression('js', $js_compress_files, $compress_options);
		$objJSNCompression->executeCompress();
		$compressedFileUrl = JURI::root(true).'/'.$cache_folder.'/'.$this->template.'_js_'.$compress_options['js_active_profile'].'.js';
		$this->addScript($compressedFileUrl);

		$topScripts[] = $compressedFileUrl;

		/**
		 * If there's any (remote, uncompressed) jQuery script, load it with noConflict()
		 * and push both 2 scripts to top of header script stack.
		 */
		foreach ($header_stuff['scripts'] as $script => $scriptDetails)
		{
			$scriptFileName = basename($script);
			if (preg_match('/^j(q|Q)uery(-[0-9]\.[0-9]\.[0-9])?\.(min\.)?js$/', $scriptFileName) === 1)
			{
				$this->addScript($template_path.'/js/jsn_noconflict.js');
				array_unshift($topScripts, $template_path.'/js/jsn_noconflict.js');
				array_unshift($topScripts, $script);
			}
		}

		/* Load header_stuff again as we probably inserted something above */
		$header_stuff = $this->getHeadData();

		$jsnutils->arrangeFileInHeadSection($header_stuff, $topScripts, $js_compress_files);
		$this->setHeadData($header_stuff);
	}
	else
	{
		foreach ($js_compress_files as $key => $value)
		{
			$this->addScript($key);
		}
	}

	$this->addScriptDeclaration($javascript_params);

	// Set analytics code
	if ($analytics_code_position == 0)
	{
		echo $analytics_code;
	}
?>
