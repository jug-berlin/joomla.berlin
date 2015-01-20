<?php
/**
* @version		$Id: Embed Google Map v2.0.0 2014-06-01 17:30 $
* @package		Joomla 1.6
* @copyright	Copyright (C) 2014 Petteri Kivim�ki. All rights reserved.
* @author		Petteri Kivim�ki
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*/

  class Parser {
	
	public function parse($string, &$params) {
      $arr = explode('|',$string);
      $params->setAddress($arr[0]);

      foreach ( $arr as $phrase ) { 
        if ( strstr(strtolower($phrase), 'version:') )	
        {         
          $tpm1 = explode(':',$phrase);
		  $tmp1 = trim($tpm1[1], '"');
		  if(strcmp(strtolower($tmp1),'new') == 0) {
		    $params->setVersion("new");
		  } else if(strcmp(strtolower($tmp1),'classic') == 0) {
		    $params->setVersion("classic");
		  } else if(strcmp(strtolower($tmp1),'embed') == 0) {
		    $params->setVersion("embed");
		  }        
        }
		
        if ( strstr(strtolower($phrase), 'zoom:') )	
        {         
          $tpm1 = explode(':',$phrase);
          $params->setZoomLevel(trim($tpm1[1], '"'));
        }
        
        if ( strstr(strtolower($phrase), 'height:') )	
        {         
          $tpm1 = explode(':',$phrase);
          $params->setHeight(trim($tpm1[1], '"'));
        }
            
        if ( strstr(strtolower($phrase), 'width:') )
        {
          $tpm1 = explode(':',$phrase);
          $params->setWidth(trim($tpm1[1], '"'));
        }
        
        if ( strstr(strtolower($phrase), 'border:') )	
        {         
          $tpm1 = explode(':',$phrase);
          $params->setBorder(trim($tpm1[1], '"'));
        }	
        
        if ( strstr(strtolower($phrase), 'border_style:') )	
        {         
          $tpm1 = explode(':',$phrase);        	
          $border_style = trim($tpm1[1], '"');
          $border_style = ( preg_match('/^(none|hidden|dotted|dashed|solid|double)$/i', $border_style) ? $border_style : 'solid' );
          $params->setBorderStyle($border_style);
        }	
        
        if ( strstr(strtolower($phrase), 'border_color:') )	
        {         
          $tpm1 = explode(':',$phrase);
          $params->setBorderColor(trim($tpm1[1], '"'));        	
        }	
        
        if ( strstr(strtolower($phrase), 'lang:') )	
        {         
          $tpm1 = explode(':',$phrase);
          $params->setLanguage(trim($tpm1[1], '"'));
        }        	
        
        if ( strstr(strtolower($phrase), 'link:') )
        {
          $tpm1 = explode(':',$phrase);
          $tmp1 = trim($tpm1[1], '"');
          if(strcmp(strtolower($tmp1),'yes') == 0) {
            $params->setAddLink(0);
          } else {
            $params->setAddLink(1);
          }
        }
        
        if ( strstr(strtolower($phrase), 'link_label:') )	
        {         
          $tpm1 = explode(':',$phrase);
          $params->setLinkLabel(trim($tpm1[1], '"'));
        }
		
        if ( strstr(strtolower($phrase), 'https:') )
        {
          $tpm1 = explode(':',$phrase);
          $tmp1 = trim($tpm1[1], '"');
          if(strcmp(strtolower($tmp1),'yes') == 0) {
            $params->setHttps(0);
          } else {
            $params->setHttps(1);
          }
        }		

		if ( strstr(strtolower($phrase), 'type:') )
		{
		  $tpm1 = explode(':',$phrase);
          $tmp1 = trim($tpm1[1], '"');
		  if(strcmp(strtolower($tmp1),'normal') == 0) {
		    $params->setMapType("m");
		  } else if(strcmp(strtolower($tmp1),'satellite') == 0) {
		    $params->setMapType("k");
		  } else if(strcmp(strtolower($tmp1),'hybrid') == 0) {
		    $params->setMapType("h");
		  } else if(strcmp(strtolower($tmp1),'terrain') == 0) {
		    $params->setMapType("p");
		  } 						
        }           
        
        if ( strstr(strtolower($phrase), 'link_full:') )
        {
          $tpm1 = explode(':',$phrase);
          $tmp1 = trim($tpm1[1], '"');
          if(strcmp(strtolower($tmp1),'yes') == 0) {
            $params->setLinkFull(0);
          } else {
            $params->setLinkFull(1);
          }
        }
      
        if ( strstr(strtolower($phrase), 'show_info:') )
        {
          $tpm1 = explode(':',$phrase);
          $tmp1 = trim($tpm1[1], '"');
          if(strcmp(strtolower($tmp1),'yes') == 0) {
            $params->setShowInfo(0);
          } else {
            $params->setShowInfo(1);
          }
        }
        
        if ( strstr(strtolower($phrase), 'info_label:') )	
        {         
          $tpm1 = explode(':',$phrase);
          $params->setInfoLabel(trim($tpm1[1], '"'));
        }		
      }
    }
  }

?>
