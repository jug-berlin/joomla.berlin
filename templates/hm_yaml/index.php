<?php
/**
 * "YAML for Joomla Template" - http://www.jyaml.de  
 *
 * (en) Basic index/HTML file to include the design file
 * (de) Basis index/HTML Datei zum einbinden der Design Datei
 *
 * @version         $Id: index.php 467 2008-07-27 16:52:23Z hieblmedia $
 * @copyright       Copyright 2005-2008, Reinhard Hiebl
 * @license         CC-A 2.0/JYAML-C(all media,html,css,js,...) and GNU/GPL(php), 
                    - see http://www.jyaml.de/en/license-conditions.html
 * @link            http://www.jyaml.de
 * @package         yamljoomla
 * @revision        $Revision: 467 $
 * @lastmodified    $Date: 2008-07-27 18:52:23 +0200 (So, 27. Jul 2008) $
*/

/* No direct access to this file | Kein direkter Zugriff zu dieser Datei */
defined('_JEXEC') or die('Restricted access');

/**
* (en) Get global yaml object for using in template 
* (de) Beziehe Globales yaml Object zur Benutzung im Template 
*/
global $jyaml;

/* 
* (en) Get full path for design index/HTML file
* (de) Hole vollen Pfad für die Design index/HTML Datei
*/
if ($jyaml) {
	$html_file = JYAML_PATH_ABS.DS.'html'.DS.'index'.DS.$jyaml->config->design.DS.$jyaml->config->html_file.'.php';
	jimport('joomla.filesystem.file');
}

/* Include index/HTML File | Füge index/HTML Datei ein */
if ( $jyaml && JFile::exists($html_file)  ) :  
  /* View design based HTML file | Zeige Design basierte HTML Datei */
  require_once ( $html_file );
else :
  /* View error file | Zeige Fehler Datei an */
  require_once ( 'jyaml_error.php' );  
endif;
?>