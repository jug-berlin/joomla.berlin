<?php
/**
 * "YAML for Joomla Template" - http://www.jyaml.de
 *
 * (en) Internet Explorer PNG Alpha transarency fix
 * (de) Internet Explorer PNG Alpha Transparenz fix
 *
 * @version         $Id: ie_png_fix.php 423 2008-07-01 11:44:05Z hieblmedia $
 * @copyright       Copyright 2005-2008, Reinhard Hiebl
 * @license         CC-A 2.0/JYAML-C(all media,html,css,js,...) and GNU/GPL(php), 
                    - see http://www.jyaml.de/en/license-conditions.html
 * @link            http://www.jyaml.de
 * @package         yamljoomla
 * @revision        $Revision: 423 $
 * @lastmodified    $Date: 2008-07-01 13:44:05 +0200 (Di, 01. Jul 2008) $
*/

/* No direct access to this file | Kein direkter Zugriff zu dieser Datei */
defined( '_JEXEC' ) or die( 'Restricted access' );

class ie_png_fix extends JYAML {
//var $JYAML = array(); // can overwrite share $jyaml object
//var $JYAMLc = array(); // can overwrite share of $jyaml->config object

  function ie_png_fix($params, $jyaml) {
    $document=& JFactory::getDocument();    
    if($document->getType() != 'html') {
      return;
    }  
  
    $selector = $params->get( 'selector', 'img, .pngTrans' );
    $blank_image = $params->get( 'blank_image', 1 );
    if ($blank_image) {
      $script_file = 'hm_iepngfix_pre.htc';
    } else {
      $script_file = 'hm_iepngfix.htc';
    }
    
    $url = JURI::base(false);
    $uri = new JURI( $url );    
    $site_path = $uri->getScheme().'://'.$uri->getHost().JURI::base(true);

    
    $style = $selector.' {behavior: url('.$site_path.'/templates/'.$jyaml->template.'/plugins/ie_png_fix/scripts/'.$script_file.');}';

    $jyaml->addStyleDeclaration( '    '.$style, 'msie 5.5' ); 
    $jyaml->addStyleDeclaration( '    '.$style, 'msie 6' );    
  }
}
?>