<?php
/**
 * "YAML for Joomla Template" - http://www.jyaml.de
 *
 * (en) CSS-Tidy Plugin to reduce filesize of stylsheets
 * (en) CSS-Tidy Plugin zum reduzieren der Dateigröße der Stylesheets
 *
 * @version         $Id: css_optimizer.php 423 2008-07-01 11:44:05Z hieblmedia $
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

class css_optimizer extends JYAML {
var $JYAML = array(); // can overwrite share $jyaml object
var $JYAMLc = array(); // can overwrite share of $jyaml->config object

var $jyaml;

  function css_optimizer($params, $jyaml) {
    global $mainframe;
    
    $this->jyaml = $jyaml;
    
    /// !!!!!!!!!! Check with jigsaw css validator response for valid stylesheets before process tidy
    /* not implemented yet */  
    
    $logs = array();
    
    // setting/params
    $output_path = 'slim';
    
    //$exclude_files = $params->get( 'exclude_files', '' );
    //$exclude_folders = $params->get( 'exclude_folders', '' );    
    $exclude['folders'] = array('patches');
    
    $compression = $params->get( 'compression', 'default' );
    $merge_files = $params->get( 'merge_files', false );    
    
    $css = '';
    $hash_array = array();
    $stylesheets = array();
    $old_size = 0;
    $new_site = 0;
    
    // Set Paths
    $path = JPATH_SITE.DS.'templates'.DS.$jyaml->template.DS.'css'.DS.$jyaml->config->design;
    $path_new = JPATH_SITE.DS.'templates'.DS.$jyaml->template.DS.'css'.DS.$jyaml->config->design.'.'.$output_path;
    
    // set new folder for css in template configuration
    $this->JYAMLc['css_folder'] = $jyaml->config->css_folder.'.'.$output_path;
    
    // If tidy dir exists then return or otherwise start tidy with url request
    if (!$merge_files && !JRequest::getVar('doOptimizeCSS', false) ) {
      return true;
    }
    
    // Delete tidy folder to reduce non used files - only if merge_files is disabled
    if (!$merge_files && JRequest::getVar('doOptimizeCSS', false)) if ( JFolder::exists($path_new) ) JFolder::delete($path_new);    
    
    /**
     * Merge
    **/
    if ($merge_files) {      
      // Get current basic layout file and files set in configuration
      $basic_file = $path.DS.'layout_'.$jyaml->config->layout.'.css';
      $basic_patch_ext = 'patches'.DS.'patch_';
      $hash_array[] = $basic_file;
      $css = JFile::read($basic_file);
      
      // Get files in configuration
      foreach ($jyaml->config->addStylesheets as $file => $attribs) {        
        if ($attribs['browser']) {
          $stylesheets[$file] = $attribs;        
          continue;
        }
        
        if (strpos($file, '{DesignCssFolder}')!==false) {
          $file = str_replace('/{DesignCssFolder}/', '', $file);
        } else {
          $file = "../..".$file;
        }        
        $css .= "\n@import url(".$file.");\n";
        $hash_array[] = $file;
      }
      
      // Remove stylesheets from configuration - else debug view errors
      $this->JYAMLc['addStylesheets'] = $stylesheets;    
      
      // Generate Hashname with Stylesheet names - faster as each site.
      $hash = $this->genHash($hash_array);
      
      if ($hash) {
        $file = $path_new.DS.'layout_'.$hash.'.css';        
        if (!JFile::exists($file) || JRequest::getVar('doTidy', false)) {
          $merged_css = $this->getImports($css);
          $old_size = $this->get_size(mb_strlen($merged_css));
          
          $merged_css = $this->optimize_css($merged_css);
          $new_size = $this->get_size(mb_strlen($merged_css));
          
          JFile::write($file, $merged_css);
          
          if (!JFolder::exists($path_new.DS.'patches')) JFolder::create($path_new.DS.'patches');
          JFile::copy($path.DS.'patches'.DS.'patch_'.$jyaml->config->layout.'.css', $path_new.DS.'patches'.DS.'patch_'.$hash.'.css');
          
          $this->JYAMLc['layout'] = $hash;        
        } else {    
          $this->JYAMLc['layout'] = $hash;      
          return true;
        }      
      } else {
        return false;
      }      
    }
    
    // Copy all Files for compatibility without excludes from settings NOT Tidy (1:1)    
    // Get files
    $files = $this->getCSSFiles($path, $jyaml, $exclude);
    
    if ( $fa=$files['all'] ) {  
      // Copy all Files
      foreach ($fa as $file) {
        $css_code = JFile::read($file);
        $path_file = dirname($file);
        $path_tidy = str_replace($path, $path_new, dirname($file));
        $file_tidy = $path_tidy.DS.basename($file);
        
        JFolder::create($path_tidy);
        JFile::write($file_tidy, $css_code);
      }  
    } 
    if ($fi=$files['included']) {
      // Copy optimized included files
      foreach ($fi as $file) {
        $css_code = JFile::read($file);
        $css_code = $this->optimize_css($css_code);
  
        $path_file = dirname($file);
        $path_tidy = str_replace($path, $path_new, dirname($file));
        $file_tidy = $path_tidy.DS.basename($file);
        
        JFolder::create($path_tidy);
        JFile::write($file_tidy, $css_code);
      }  
    } 
      
    // Debug Mode
    if ($jyaml->config->debug) {    
      $sizeinfo = 'Filesiez before: '.$old_size.' KB - Filesize after: '. $new_size.' KB<br />';
      $sizeinfo .= '<strong style="color:green;">Reduced overall size to: '.round(100-$new_size/($old_size/100), 2)."%</strong>";
      $this->JYAML['pgl_logs_onBefore'][] = '<div style="text-align:left; background:#000; border:1px solid #ccc; margin:0 .5em; padding:.5em 1em; color:#fff;" class="trans75"><span style="text-decoration:underline;">CSS Optimizer Log</span><br />';
      $this->JYAML['pgl_logs_onBefore'][] = $sizeinfo.'</div>';
    } else {
       //$mainframe->enqueueMessage( 'CSS Tidy has parsed CSS files' );
    }          
  }
  
  function get_size($size) {
    $size = $size / 1024;
    return round($size, 2);
  }
  
  function genHash($array=false) {
    if ($array) {
      return md5( implode($array, ''));
    } else {
      return false;
    }
  }
  
  function getImports($matches=false) {    
    if (is_array($matches)) {    
      $file = str_replace(array("(", ")", "\"", "'"), "", $matches[1]);
      $file = str_replace("/", DS, $file);
      
      $path = JPATH_SITE.DS.'templates'.DS.$this->jyaml->template.DS.'css'.DS.$this->jyaml->config->design;
      
      if (strpos($file, $path)===false) $file = $path.DS.$file;
      $file_tmp = $file;
      
      // Get content and replace recusive paths
      $content = false;
      if (JFile::exists($file)) {
        $matches = JFile::read($file);
        
        // Replace recurisve paths to can find file
        $matches = preg_replace("/@import\s+url\((.*)\);/", "@import url(".dirname($file_tmp)."/\\1);", $matches);        
      } else {
        $matches = $file;
      }  
      
      $matches = $this->replaceCssURLs($matches, dirname($file));    
    }
    
    return preg_replace_callback("/@import\s+url(.*);/", array( &$this, 'getImports' ), $matches);
  }
  
  function replaceCssURLs($code, $dir) {
    $dir = str_replace(JPATH_SITE.DS.'templates'.DS.$this->jyaml->template, '', $dir);
    $dir = '../..'.str_replace('\\', '/', $dir).'/';
    
    $code = str_replace(array("('", "(\""), "(", $code);
    $code = str_replace(array("')", "\")"), ")", $code);
    $code = preg_replace("/(.*:)\s+?url\((.*\))/", "\\1 url($dir\\2", $code);

    return $code;
  }
  
  function getCSSFiles($path, $jyaml, $exclude=array(), $recurse=true) {
    $files = array();  
    $files['all']      = JFolder::files($path, '.css$', $recurse, true);
    $files['included'] = JFolder::files($path, '.css$', $recurse, true, $exclude['folders']);
    
    return $files;    
  }
  
  function viewLog($css, $file, $file_tidy) {
    $ratio = $css->print->get_ratio();
    $diff = $css->print->get_diff();
    
    if ($ratio > 0) {
      $ratio = '<span style="color:green;">'.$ratio.'%</span> ('.$diff.' Bytes)';
    } else {
      $ratio = '<span style="color:red;">'.$ratio.'%</span> ('.$diff.' Bytes)';
    }
    
    $log = '<div class="trans75" style="text-align:left; background:#000; border:1px solid #ccc; margin:1em; padding:1em; color:#fff;">';
    
    $log .= '<fieldset style="border:1px solid #eee; background:transparent; padding:.5em;">';
    $log .= '<legend style="padding:0 .5em;"> Result: '.$ratio.'</legend>';
    $log .= 'Source File: '.str_replace(JPATH_SITE, '', $file).' --- Size: <span style="color:red;">'.$css->print->size('input').'</span>kb<br />';
    $log .= 'Destination File: '.str_replace(JPATH_SITE, '', $file_tidy).' --- Size: <span style="color:green; font-weight:bold;">'.$css->print->size('output').'</span>kb';

    if (count($css->log) > 0) {
      $log .= '<br /><br /><fieldset id="messages"><legend>Messages</legend>';
      $log .= '<div><dl>';
      foreach ($css->log as $line => $array) {
        $log .= '<dt>Line: '.$line.'</dt>';
        for ($i = 0; $i < count($array); $i ++) {
          $log .= '<dd class="'.$array[$i]['t'].'">'.$array[$i]['m'].'</dd>';
        }
      }      
      $log .= '</dl></div></fieldset>';
    }
    
    $log .= '</fieldset>';
    
    $log .= '</div>';
      
    return $log;

  }

  function optimize_css($css)
  {
    $css = preg_replace('/@charset\s+[\'"](\S*)\b[\'"];/i', '', $css);
    
    /* Replace Hacks */
    $css = preg_replace("#/\*\*/:/\*\*/#sU", "~~IE6HACK1~~", $css); // IE6.0 Hack /**/:/**/
    
    $replace = array(
      "#/\*.+\*/#sU" => "", // Strip comments
      "#\s\s+#"      => " "   // Strip whitespaces
    );
    $search = array_keys($replace);
    $css = preg_replace($search, $replace, $css);
  
    $replace = array(
      ": "  => ":",
      "; "  => ";",
      " {"  => "{",
      " }"  => "}",
      ", "  => ",",
      "{ "  => "{",
      ";}"  => "}", // Strip optional semicolons.
      "\n" => "", // Remove linebreaks
    );
    $search = array_keys($replace);
    $css = str_replace($search, $replace, $css);
    
    $csscomment = "@charset \"UTF-8\";\n";
    $csscomment .= "/**\n";
    $csscomment .= " * \"YAML for Joomla Template\" - http://www.jyaml.de\n";
    $csscomment .= " * @created by JYAML CSS Optimizer\n";
    $csscomment .= " * @package    jyaml\n";
    $csscomment .= "*/\n";
    
    /* Replace Hacks */
    $css = str_replace("~~IE6HACK1~~", "/**/:/**/", $css); // IE6.0 Hack /**/:/**/
    
    $css = $csscomment.trim($css);
    
    return $css;
  }

}
?>