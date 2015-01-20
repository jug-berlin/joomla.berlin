<?php
/**
 * "YAML for Joomla Template" - http://www.jyaml.de  
 *
 * @version         $Id: offline.php 467 2008-07-27 16:52:23Z hieblmedia $
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

global $jyaml;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
  <jdoc:include type="head" />
  <link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $jyaml->template; ?>/css/<?php echo $jyaml->config->design; ?>/screen/offline.css" type="text/css" />
  <!-- <link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/offline.css" type="text/css" /> -->
  <!-- <link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/general.css" type="text/css" /> -->
</head>
<body>
<jdoc:include type="message" />

  <div id="frame" class="outline">
    <h1>
      <img src="<?php echo $jyaml->imagePath; ?>/site_logo.gif" alt="<?php echo $mainframe->getCfg('sitename'); ?>" align="middle" />
    </h1>
      
    <p class="offline_message">
      <?php echo $mainframe->getCfg('offline_message'); ?>
    </p>
    
    <?php
    if(JPluginHelper::isEnabled('authentication', 'openid')) {
      JHTML::_('script', 'openid.js');
    } 
    ?>
    
    <form action="index.php" method="post" name="login" id="form-login">
      
      <fieldset class="input floatbox">
        <legend><?php echo JText::_('LOGIN') ?></legend>
        <p id="form-login-username">
          <label for="username"><?php echo JText::_('Username') ?></label><br />
          <input name="username" id="username" type="text" class="inputbox" alt="username" size="18" />
        </p>
        <p id="form-login-password">
          <label for="passwd"><?php echo JText::_('Password') ?></label><br />
          <input type="password" name="passwd" class="inputbox" size="18" alt="password" />
        </p>
        
        <input type="submit" name="Submit" class="button float_right" value="<?php echo JText::_('LOGIN') ?>" />
        
        <p id="form-login-remember">
          <label for="remember"><?php echo JText::_('Remember me') ?></label>
          <input type="checkbox" name="remember" class="inputbox" value="yes" alt="Remember Me" />
        </p>
        
      </fieldset>
      
      <input type="hidden" name="option" value="com_user" />
      <input type="hidden" name="task" value="login" />
      <input type="hidden" name="return" value="<?php echo base64_encode(JURI::base()) ?>" />
      <?php echo JHTML::_( 'form.token' ); ?>
    </form>
  </div> 
</body>
</html>