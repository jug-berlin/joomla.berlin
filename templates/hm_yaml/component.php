<?php
/**
 * "YAML for Joomla Template" - http://www.jyaml.de  
 *
 * (en) Basic compontent/HTML file to include the compontent output
 * (de) Basis compontent/HTML Datei zum einbinden der Kompontenen ausgabe
 *
 * @version         $Id: component.php 467 2008-07-27 16:52:23Z hieblmedia $
 * @copyright       Copyright 2005-2008, Reinhard Hiebl
 * @license         CC-A 2.0/JYAML-C(all media,html,css,js,...) and GNU/GPL(php), 
                    - see http://www.jyaml.de/en/license-conditions.html
 * @link            http://www.jyaml.de
 * @package         yamljoomla
 * @revision        $Revision: 467 $
 * @lastmodified    $Date: 2008-07-27 18:52:23 +0200 (So, 27. Jul 2008) $
*/

/* No direct access to this file | Kein direkter Zugriff zu dieser Datei */
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* (en) Get global yaml object for using in template 
* (de) Beziehe Globales yaml Object zur Benutzung im Template 
*/
global $jyaml;

/* 
* (en) Get full path for design index2(component)/HTML file
* (de) Hole vollen Pfad für die Design index2(component)/HTML Datei
*/
if ($jyaml) {
  $component_file = JYAML_PATH_ABS.DS.'html'.DS.'index'.DS.$jyaml->config->design.DS.'component.php';
  jimport('joomla.filesystem.file');
}

/* Include index2(component)/HTML File | Füge index2(component)/HTML Datei ein */
if ( $jyaml && JFile::exists($component_file) ) :
  require_once ( $component_file );  
else : 
/* Fallback if design file not exists */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
  <jdoc:include type="head" />
</head>  
<body class="contentpane">  
  <jdoc:include type="message" />
  <jdoc:include type="component" />    
</body>
</html>
<?php endif; ?>