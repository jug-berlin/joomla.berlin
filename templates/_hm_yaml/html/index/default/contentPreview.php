<?php
/**
 * "YAML for Joomla Template" - http://www.jyaml.de  
 *
 * @version         $Id: contentPreview.php 467 2008-07-27 16:52:23Z hieblmedia $
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
  <jdoc:include type="head" />
</head>  
<body class="contentpane contentPreview" style="padding:10px;">  
  
  <div style="background:url(<?php echo JYAML_PATH_REL.'/yaml/icon-48-yaml.png';?>) no-repeat left; margin:0 0 10px 0; height:48px; padding:10px; text-align:right; padding-left:160px; color:#999; font-size:20px; font-weight:bold; border-bottom:4px double #ccc;"> 
    JYAML Content Preview 
  </div>

  <?php
  global $mainframe;

  $editor    =& JFactory::getEditor();
  $document  =& JFactory::getDocument();
  $document->setLink(JURI::root());
  JHTML::_('behavior.caption');
  ?>
  
  <script>
    var title = window.top.document.adminForm.title.value;
    var fulltext = window.top.<?php echo $editor->getContent('text') ?>;
    fulltext = fulltext.replace('<hr id=\"system-readmore\" \/>', '');
  </script>

  <h2><script type="text/javascript">document.write(title);</script></h2>
  <div id="contentPreview-content">
    <script type="text/javascript">document.write(fulltext);</script>
  </div>

</body>
</html>