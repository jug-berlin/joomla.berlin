<?php
/**
 * "YAML for Joomla Template" - http://www.jyaml.de  
 *
 * (en) Basic error/HTML file to include the error file
 * (de) Basis error/HTML Datei zum einbinden der Error Datei
 *
 * @version         $Id: error.php 467 2008-07-27 16:52:23Z hieblmedia $
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
	$error_file = JYAML_PATH_ABS.DS.'html'.DS.'index'.DS.$jyaml->config->design.DS.'error.php';
	jimport('joomla.filesystem.file');
}

/* Include index2(component)/HTML File | Füge index2(component)/HTML Datei ein */
if ($jyaml && JFile::exists($error_file) ) :
	require_once ( $error_file );  
else : 
/* Fallback */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
  <title><?php echo $this->error->code ?> - <?php echo $this->title; ?></title>
  <link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/system/css/error.css" type="text/css" />
</head>
<body>
  <div align="center">
    <div id="outline">
    <div id="errorboxoutline">
      <div id="errorboxheader"><?php echo $this->error->code ?> - <?php echo $this->error->message ?></div>
      <div id="errorboxbody">
      <p><strong><?php echo JText::_('You may not be able to visit this page because of:'); ?></strong></p>
        <ol>
          <li><?php echo JText::_('An out-of-date bookmark/favourite'); ?></li>
          <li><?php echo JText::_('A search engine that has an out-of-date listing for this site'); ?></li>
          <li><?php echo JText::_('A mis-typed address'); ?></li>
          <li><?php echo JText::_('You have no access to this page'); ?></li>
          <li><?php echo JText::_('The requested resource was not found'); ?></li>
          <li><?php echo JText::_('An error has occurred while processing your request.'); ?></li>
        </ol>
      <p><strong><?php echo JText::_('Please try one of the following pages:'); ?></strong></p>
      <p>
        <ul>
          <li><a href="<?php echo $this->baseurl; ?>/index.php" title="<?php echo JText::_('Go to the home page'); ?>"><?php echo JText::_('Home Page'); ?></a></li>
        </ul>
      </p>
      <p><?php echo JText::_('If difficulties persist, please contact the system administrator of this site.'); ?></p>
      <div id="techinfo">
      <p><?php echo $this->error->message; ?></p>
      <p>
        <?php if($this->debug) :
          echo $this->renderBacktrace();
        endif; ?>
      </p>
      </div>
      </div>
    </div>
    </div>
  </div>
</body>
</html>
<?php endif; ?>
