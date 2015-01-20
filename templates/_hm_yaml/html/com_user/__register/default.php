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

<script type="text/javascript">
  Window.onDomReady(function(){
    document.formvalidator.setHandler('passverify', function (value) { return ($('password').value == value); }  );
  });
</script>

<form action="<?php echo JRoute::_('index.php?option=com_user#content'); ?>" method="post" id="josForm" name="josForm" class="form-validate user">
  <h2 class="componentheading"><?php echo JText::_('Registration'); ?></h2>
  <?php if(isset($this->message)) :
    $this->display('message');
  endif; ?>

  <p><?php echo JText::_('REGISTER_REQUIRED'); ?></p>
  <p class="name">
    <label id="namemsg" for="name"><?php echo JText::_('Name'); ?>: *</label><br />
    <input type="text" name="name" id="name" value="<?php echo $this->user->get('name'); ?>" class="inputbox validate required none namemsg" maxlength="50" />
  </p>
  <p class="user">
    <label id="usernamemsg" for="username"><?php echo JText::_('Username'); ?>: *</label><br />
    <input type="text" id="username" name="username"  value="<?php echo $this->user->get('username'); ?>" class="inputbox validate required username usernamemsg" maxlength="25" />
  </p>
  <p class="email">
    <label id="emailmsg" for="email"><?php echo JText::_('Email'); ?>: *</label><br />
    <input type="text" id="email" name="email"  value="<?php echo $this->user->get('email'); ?>" class="inputbox validate required email emailmsg" maxlength="100" />
  </p>

  <p class="pass">
    <label id="pwmsg" for="password"><?php echo JText::_('Password'); ?>: *</label><br />
    <input type="password" id="password" name="password" value="" class="inputbox required validate-password" />
  </p>
  <p class="verify_pass">
    <label id="pw2msg" for="password2"><?php echo JText::_('Verify Password'); ?>: *</label><br />
    <input type="password" id="password2" name="password2" value="" class="inputbox required validate-passverify" />
  </p>

  <button class="button validate" type="submit"><?php echo JText::_('Register'); ?></button>
  <input type="hidden" name="task" value="register_save" />
  <input type="hidden" name="id" value="0" />
  <input type="hidden" name="gid" value="0" />
  <?php echo JHTML::_( 'form.token' ); ?>
</form>
