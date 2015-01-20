<?php
/**
 * "YAML for Joomla Template" - http://www.jyaml.de  
 *
 * @version         $Id: _error.php 467 2008-07-27 16:52:23Z hieblmedia $
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

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
  <jdoc:include type="head" />
    <style type="text/css">
    #error {padding:1em;margin:1em;border:1px solid #ccc; background:#eee; color:#F00;}
  </style>
</head>
<body>

<div id="error">
  <strong>Template Error</strong><br />
    <span><?php echo JText::_('YAML _ERROR TITLE'); ?></span>
    <ul>
      <li style="font-weight:bold;"><?php echo JText::_('YAML _ERROR E1'); ?></li>
      <li><?php echo JText::_('YAML _ERROR E2'); ?>  &quot;<?php echo isset($jyaml->config->design) ? $jyaml->config->design : '[DesignName?]'; ?>&quot;</li>
      <?php $htmlfile = isset($jyaml->config->html_file) ? $jyaml->config->html_file : (isset($jyaml->config->design) ? $jyaml->config->design : '[HtmlFile?]'); ?>  
      <li><?php echo JText::_('YAML _ERROR E3'); ?> \html\index\<?php echo $htmlfile ; ?>.php</li>
    </ul>
</div>

<jdoc:include type="modules" name="debug" />

</body>
</html>
