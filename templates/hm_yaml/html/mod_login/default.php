<?php
/**
 * "YAML for Joomla Template" - http://www.jyaml.de  
 *
 * @version         $Id: default.php 467 2008-07-27 16:52:23Z hieblmedia $
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

<?php 
$return = base64_encode(base64_decode($return).'#content');

if ($type == 'logout') : ?>
<form action="index.php" method="post" name="login" class="log">
  <?php if ($params->get('greeting')) : ?>
  <p>
    <?php echo JText::sprintf('HINAME', $user->get('name')); ?>
  </p>
  <?php endif; ?>
  <p>
    <input type="submit" name="Submit" class="button" value="<?php echo JText::_('BUTTON_LOGOUT'); ?>" />
  </p>
  <input type="hidden" name="option" value="com_user" />
  <input type="hidden" name="task" value="logout" />
  <input type="hidden" name="return" value="<?php echo $return; ?>" />
</form>
<?php else : ?>
<form action="<?php echo JRoute::_( 'index.php', true, $params->get('usesecure')); ?>" method="post" name="login" class="form-login">
  <?php if ($params->get('pretext')) : ?>
  <p>
    <?php echo $params->get('pretext'); ?>
  </p>
  <?php endif; ?>
  <p>
    <label for="mod_login_username">
      <?php echo JText::_('Username'); ?>
    </label><br />
    <input name="username" id="mod_login_username" type="text" class="inputbox" alt="<?php echo JText::_('Username'); ?>" />
  </p>
  <p>
    <label for="mod_login_password">
      <?php echo JText::_('Password'); ?>
    </label><br />
    <input type="password" id="mod_login_password" name="passwd" class="inputbox"  alt="<?php echo JText::_('Password'); ?>" />
  </p>
  <p>
    <label for="mod_login_remember" class="remember">
      <?php echo JText::_('Remember me'); ?>
    </label>
    <input type="checkbox" name="remember" id="mod_login_remember" class="checkbox" value="yes" alt="<?php echo JText::_('Remember me'); ?>" />
  </p>
  <div class="floatbox mod_login_submitbutton"><input type="submit" name="Submit" class="button" value="<?php echo JText::_('BUTTON_LOGIN'); ?>" /></div>
  <p>
    <a href="<?php echo JRoute::_('index.php?option=com_user&view=reset#content'); ?>">
      <?php echo JText::_('FORGOT_YOUR_PASSWORD'); ?>
    </a>
  </p>
  <p>
    <!--a href="<?php echo JRoute::_('index.php?option=com_user&view=remind#content'); ?>">
      <?php echo JText::_('FORGOT_YOUR_USERNAME'); ?>
    </a-->
  </p>
  <?php $usersConfig =& JComponentHelper::getParams('com_users');
  if ($usersConfig->get('allowUserRegistration')) : ?>
  <p>
    <?php echo JText::_('No account yet?'); ?>
    <a href="<?php echo JRoute::_('index.php?option=com_user&task=register#content'); ?>">
      <?php echo JText::_('Register'); ?>
    </a>
  </p>
  <?php endif;
  echo $params->get('posttext'); ?>
  <input type="hidden" name="option" value="com_user" />
  <input type="hidden" name="task" value="login" />
  <input type="hidden" name="return" value="<?php echo $return; ?>" />
  <input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>
<?php endif;
