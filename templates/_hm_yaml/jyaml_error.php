<?php
/**
 * "YAML for Joomla Template" - http://www.jyaml.de  
 *
 * @version         $Id: _error.php 423 2008-07-01 11:44:05Z hieblmedia $
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

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
  <title><?php echo JText::_('YAML _ERROR TITLE'); ?> - <?php echo $this->title; ?></title>
  <link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/system/css/error.css" type="text/css" />
</head>
<body>
  <div align="center">
    <div id="outline">
      <div id="errorboxoutline">
        <div id="errorboxheader"><?php echo JText::_('YAML _ERROR TITLE'); ?></div>
        <div id="errorboxbody">
          <p><strong><?php echo JText::_('You may not be able to visit this page because of:'); ?></strong></p>
          <ol>
            <li style="font-weight:bold;"><?php echo JText::_('YAML _ERROR E1'); ?></li>
            <li><?php echo JText::_('YAML _ERROR E2'); ?>  &quot;<?php echo isset($jyaml->config->design) ? $jyaml->config->design : '[DesignName?]'; ?>&quot;</li>
            <?php $htmlfile = isset($jyaml->config->html_file) ? $jyaml->config->html_file : (isset($jyaml->config->design) ? $jyaml->config->design : '[HtmlFile?]'); ?>  
            <li><?php echo JText::_('YAML _ERROR E3'); ?> \html\index\<?php echo $htmlfile ; ?>.php</li>
          </ol>
          <p><?php echo JText::_('If difficulties persist, please contact the system administrator of this site.'); ?></p>
        </div>
      </div>
    </div>
  </div>
</body>
</html>

<jdoc:include type="modules" name="debug" />

</body>
</html>
