<?php
/**
 * "YAML for Joomla Template" - http://www.jyaml.de
 *
 * (en) Internet Explorer min/max width Fix with JS-Expressions
 * (de) Internet Explorer min/max width Fix mit JS-Expressions
 *
 * @version         $Id: ie_min_max_expressions.php 423 2008-07-01 11:44:05Z hieblmedia $
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

class ie_min_max_expressions extends JYAML {
//var $JYAML = array(); // can overwrite share $jyaml object
//var $JYAMLc = array(); // can overwrite share of $jyaml->config object

  function ie_min_max_expressions($params, $jyaml) {
    $document=& JFactory::getDocument();    
    if($document->getType() != 'html') {
      return;
    } 
  
    $selector = $params->get( 'selector', '#page_margins' );    
    if (strpos($selector, ',') !== false) {
      $selector = str_replace(',', ', * html ', $selector);
      $selector = str_replace('  ', ' ', $selector);
    }
    
    $width_fallback   = $params->get( 'width_fallback', '' );
    if ($width_fallback)
    {
     $width_fallback = 'width: '.$width_fallback.'; /* Fallback if Javascript not enabled  */'."\n";
    }
    
    $min_width  = $params->get( 'min_width', '' );
    $max_width  = $params->get( 'max_width', '' );
    
    $width_style = $this->js_expression_width($min_width, $max_width); 

    if ($width_style)
    {
      $width_style_output = (
        '  * html '.$selector.' {'."\n"
        .'    '.$width_fallback.''."\n"
        .'    /* JS-Expression for min-/max-width simulation */'."\n"
        .'    '.$width_style."\n"
        .'    );'."\n"
        .'  }'
      );
    }

    if (isset($width_style_output))
    {
      //$width_style_output = $jyaml->trimmer($width_style_output);
      
      $jyaml->addStyleDeclaration( '    '.$width_style_output, 'msie 5.5' );
      $jyaml->addStyleDeclaration( '    '.$width_style_output, 'msie 6' );  
    }
    
  }
  
  function js_expression_width($min_width='', $max_width='')
  {
    $l1 = ''; 
    $l2 = '';
    $l3 = '';
    $l4 = '';
    $l5 = '';
    $style = '';
    
    if (!$min_width && !$max_width) return '';
    
    $l1 = 'width: expression((document.documentElement && document.documentElement.clientHeight) ?';
    
    /* PX */
    if ($this->stripExt($min_width)=='px') {
      $l2 = ' ((document.documentElement.clientWidth < '.$this->stripNum($min_width).') ? "'.$min_width.'" :';
      $l4 = ' ((document.body.clientWidth < '.$this->stripNum($min_width).') ? "'.$min_width.'" :';
    }
    if ($this->stripExt($max_width)=='px') {
      $l3 = ' ((document.documentElement.clientWidth > '.$this->stripNum($max_width).') ? "'.$max_width.'" : "auto" )) :';
      $l5 = ' ((document.body.clientWidth > '.$this->stripNum($max_width).') ? "'.$max_width.'" : "auto" )));';
    }
    
    /* EM */
    if ($this->stripExt($min_width)=='em') {
      $l2 = ' ((document.documentElement.clientWidth < ('.$this->stripNum($min_width).' * parseInt(document.documentElement.currentStyle.fontSize))) ? "'.$min_width.'" :';
      $l4 = ' ((document.body.clientWidth < ('.$this->stripNum($min_width).' * parseInt(document.body.currentStyle.fontSize))) ? "'.$min_width.'" :';
    }
    if ($this->stripExt($max_width)=='em') {
      $l3 = ' ((document.documentElement.clientWidth > ('.$this->stripNum($max_width).' * parseInt(document.documentElement.currentStyle.fontSize))) ? "'.$max_width.'" : "auto" )) :';
      $l5 = ' ((document.body.clientWidth > ('.$this->stripNum($max_width).' * parseInt(document.body.currentStyle.fontSize))) ? "'.$max_width.'" : "auto" )));';
    }
    
    /* % - (Percent) */
    if ($this->stripExt($min_width)=='%') {
      $l2 = ' ((document.documentElement.clientWidth < ('.$this->stripNum($min_width).'/100 * document.documentElement.clientWidth)) ? "'.$min_width.'" :';
      $l4 = ' ((document.body.clientWidth < ('.$this->stripNum($min_width).'/100 * document.body.clientWidth)) ? "'.$min_width.'" :';
    }
    if ($this->stripExt($max_width)=='%') {
      $l3 = ' ((document.documentElement.clientWidth > ('.$this->stripNum($max_width).'/100 * document.documentElement.clientWidth)) ? "'.$max_width.'" : "auto" )) :';
      $l5 = ' ((document.body.clientWidth > ('.$this->stripNum($max_width).'/100 * document.body.clientWidth)) ? "'.$max_width.'" : "auto" )));';
    }
    
    /* empty values */
    if (!$min_width) {
      $l2 = ' ((document.documentElement.clientWidth < (1/100 * document.documentElement.clientWidth)) ? "1%" :';
      $l4 = ' ((document.body.clientWidth < (1/100 * document.body.clientWidth)) ? "1%" :';
    }
    if (!$max_width) {
      $l3 = ' ((document.documentElement.clientWidth > (100/100 * document.documentElement.clientWidth)) ? "100%" : "auto" )) :';
      $l5 = ' ((document.body.clientWidth > (100/100 * document.body.clientWidth)) ? "100%" : "auto" )));';
    }
    
    return $l1.$l2.$l3.$l4.$l5;
  }
  
  function stripNum($n)
  {  
    if (substr($n, -1)=='%') return substr($n, 0, (strlen($n)-1));  
    return substr($n, 0, (strlen($n)-2));
  }
  
  function stripExt($n)
  {    
    if (substr($n, -1)=='%') return '%';  
    return substr($n, -2);
  }

}

?>