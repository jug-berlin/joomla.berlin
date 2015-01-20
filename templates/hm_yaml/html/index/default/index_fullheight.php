<?php 
/**
 * "YAML for Joomla Template" - http://www.jyaml.de  
 *
 * @version         $Id: index_fullheight.php 467 2008-07-27 16:52:23Z hieblmedia $
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
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
<head>
<jdoc:include type="head" />
</head>
<body>

<div id="page_margins">
  <div id="page">    
    
    <!-- begin: #header -->  
    <div id="header">
      <div id="topnav">
        <!-- start: skip link navigation -->
        <a class="skip" href="#navigation"><?php echo JText::_('YAML SKIPLINK NAVIGATION'); ?></a>
        <a class="skip" href="#content"><?php echo JText::_('YAML SKIPLINK CONTENT'); ?></a>
        <!-- end: skip link navigation -->
        
        <?php
        /**
         * function() $jyaml->getPosition($type, $name, $style, $attributes)
         * @param type    [modules, module, component, message]
         * @param name    [@modules=modul position, @module=modul name]
         * @param style   [raw, xhtml, rounded, table, (modChrome_)@style ]
         * @param attribs [Example='id="myid" headerLevel="3"']
         * @doc    http://dev.joomla.org/component/option,com_jd-wiki/Itemid,/id,templates:jdoc_statements/
        **/
        $jyaml->getPosition('modules', 'topnav', 'raw', ''); 
        ?>
      </div>
      
      <h1>
         
          <img src="<?php echo $jyaml->imagePath; ?>/site_logo.gif" width="950" height="160" alt="<?php echo JApplication::getCfg('sitename') ?>" />
         
      </h1>
      <!--span>JYAML • A Joomla! Template with the YAML (X)HTML/CSS Framework</span-->
    </div>
    <!-- end: #header --> 
    
    <!-- begin: #breadcrumbs -->      
    <div id="breadcrumbs">
      <?php echo JText::_('YAML YOU ARE HERE'); ?>: 
      <?php
      /**
       * function() $jyaml->getPosition($type, $name, $style, $attributes)
       * @param type    [modules, module, component, message]
       * @param name    [@modules=modul position, @module=modul name]
       * @param style   [raw, xhtml, rounded, table, (modChrome_)@style ]
       * @param attribs [Example='id="myid" headerLevel="3"']
       * @doc    http://dev.joomla.org/component/option,com_jd-wiki/Itemid,/id,templates:jdoc_statements/
      **/
      $jyaml->getPosition('module', 'breadcrumbs', 'raw', ''); 
      ?>
    </div>
    <!-- end: #breadcrumbs -->  
    
    <?php if($this->countModules( 'nav_main' )) : ?>
    <!-- begin: main navigation #nav -->
    <div id="nav"> 
      <a id="navigation" name="navigation"></a>
      <!-- skip anchor: navigation -->
      <div id="nav_main">
        <?php
        /**
         * function() $jyaml->getPosition($type, $name, $style, $attributes)
         * @param type    [modules, module, component, message]
         * @param name    [@modules=modul position, @module=modul name]
         * @param style   [raw, xhtml, rounded, table, (modChrome_)@style ]
         * @param attribs [Example='id="myid" headerLevel="3"']
         * @doc    http://dev.joomla.org/component/option,com_jd-wiki/Itemid,/id,templates:jdoc_statements/
        **/
        $jyaml->getPosition('modules', 'nav_main', 'raw', ''); 
        ?>
      </div>
    </div>
    <!-- end: main navigation -->
    <?php endif; ?>
        
    <!-- begin: main content area #main -->
    <div id="main">        
      <?php if ( $jyaml->col1_enabled ) : ?>
      <!-- begin: #col1 - first float column -->
      <div id="col1">
        <div id="col1_content" class="clearfix">
          <?php $jyaml->getContent('col1_content'); ?>
        </div>
      </div>
      <!-- end: #col1 -->
      <?php endif; ?>
            
      <?php if ( $jyaml->col2_enabled ) : ?>
      <!-- begin: #col2 second float column -->
      <div id="col2">
        <div id="col2_content" class="clearfix">
          <?php $jyaml->getContent('col2_content'); ?>
        </div>
      </div>
      <!-- end: #col2 -->
       <?php endif; ?>
            
      <!-- begin: #col3 static column -->
      <div id="col3">
        <div id="col3_content" class="clearfix">
        <div id="col3_content_wrapper" class="floatbox">
          <a id="content" name="content"></a>
            <?php $jyaml->getContent('col3_content'); ?>
          </div>
        </div>
        
        <div id="ie_clearing"> </div>
        <!-- Ende: IE Column Clearing -->
      </div>
      <!-- end: #col3 -->
      
    </div>
    <!-- end: #main -->
    
  </div> <!-- end: #page -->
</div> <!-- end: #page_margins -->

<!-- begin: #footer -->
<div id="footer">
  <div id="footer_content" class="floatbox">
    <?php if($this->countModules( 'syndicate' )) : ?>
    <div class="syndicate float_right">
      <?php
      /**
       * function() $jyaml->getPosition($type, $name, $style, $attributes)
       * @param type    [modules, module, component, message]
       * @param name    [@modules=modul position, @module=modul name]
       * @param style   [raw, xhtml, rounded, table, (modChrome_)@style ]
       * @param attribs [Example='id="myid" headerLevel="3"']
       * @doc    http://dev.joomla.org/component/option,com_jd-wiki/Itemid,/id,templates:jdoc_statements/
      **/
      $jyaml->getPosition('modules', 'syndicate', 'raw', ''); 
      ?>
    </div> 
    <?php endif; ?>
    
    <!--
      (de)
      Folgende Rückverlinkungen dürfen nur entfernt werden, 
      wenn Sie eine JYAML und/oder eine YAML Lizenz besitzen.
      
      (en)
      Following backlinks maybe only removed,
      if you have purchase a JYAML and/or a YAML license.
      
      :: http://www.jyaml.de 
      :: http://www.yaml.de 
    --> 
    Footer with copyright notice and status information<br />  
    Layout based on <a href="http://www.jyaml.de/">YAML Joomla! Template (JYAML)</a> and <a href="http://www.yaml.de/">YAML</a>
  </div>
</div>
<!-- end: #footer -->

<jdoc:include type="modules" name="debug" />
</body>
</html>