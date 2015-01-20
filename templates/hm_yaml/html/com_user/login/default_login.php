<?php 
/**
 * "YAML for Joomla Template" - http://www.jyaml.de  
 *
 * @version         $Id: default_login.php 467 2008-07-27 16:52:23Z hieblmedia $
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

if(JPluginHelper::isEnabled('authentication', 'openid')) :
  $lang = &JFactory::getLanguage();
  $lang->load( 'plg_authentication_openid', JPATH_ADMINISTRATOR );
  $langScript =   'var JLanguage = {};'.
          ' JLanguage.WHAT_IS_OPENID = \''.JText::_( 'WHAT_IS_OPENID' ).'\';'.
          ' JLanguage.LOGIN_WITH_OPENID = \''.JText::_( 'LOGIN_WITH_OPENID' ).'\';'.
          ' JLanguage.NORMAL_LOGIN = \''.JText::_( 'NORMAL_LOGIN' ).'\';'.
          ' var comlogin = 1;';
  $document = &JFactory::getDocument();
  $document->addScriptDeclaration( $langScript );
  JHTML::_('script', 'openid.js');
endif; 

if ( $this->params->get( 'show_login_title' ) ) : ?>
<h1 class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ); ?>"><?php echo $this->params->get( 'header_login' ); ?></h1>
<?php endif; ?>

<form action="<?php echo JRoute::_( 'index.php', true, $this->params->get('usesecure')); ?>" method="post" name="com-login" id="com-form-login">
  <p>
    <?php if ( $this->params->get( 'description_login' ) ) : ?>
    <?php echo $this->params->get( 'description_login_text' ); ?>
    <?php endif; ?>
  </p>

  <?php echo $this->image; ?>
  <p id="com-form-login-username">
    <label for="username"><?php echo JText::_('Username') ?></label><br />
    <input name="username" id="username" type="text" class="inputbox" alt="username" size="18" />
  </p>
  <p id="com-form-login-password">
    <label for="passwd"><?php echo JText::_('Password') ?></label><br />
    <input type="password" id="passwd" name="passwd" class="inputbox" size="18" alt="password" />
  </p>
  <?php if(JPluginHelper::isEnabled('system', 'remember')) : ?>
  <p id="com-form-login-remember">
    <label for="remember"><?php echo JText::_('Remember me') ?></label>
    <input type="checkbox" id="remember" name="remember" class="inputbox" value="yes" alt="Remember Me" />
  </p>
  <?php endif; ?>
  <p><input type="submit" name="Submit" class="button" value="<?php echo JText::_('LOGIN') ?>" /></p>

  <ul>
    <li>
      <a href="<?php echo JRoute::_( 'index.php?option=com_user&view=reset' ); ?>">
      <?php echo JText::_('FORGOT_YOUR_PASSWORD'); ?></a>
    </li>
    <li>
      <a href="<?php echo JRoute::_( 'index.php?option=com_user&view=remind' ); ?>">
      <?php echo JText::_('FORGOT_YOUR_USERNAME'); ?></a>
    </li>
    <?php
    $usersConfig = &JComponentHelper::getParams( 'com_users' );
    if ($usersConfig->get('allowUserRegistration')) : ?>
    <li>
      <a href="<?php echo JRoute::_( 'index.php?option=com_user&task=register' ); ?>">
        <?php echo JText::_('REGISTER'); ?></a>
    </li>
    <?php endif; ?>
  </ul>

  <input type="hidden" name="option" value="com_user" />
  <input type="hidden" name="task" value="login" />
  <input type="hidden" name="return" value="<?php echo $this->return; ?>" />
  <?php echo JHTML::_( 'form.token' ); ?>
</form>