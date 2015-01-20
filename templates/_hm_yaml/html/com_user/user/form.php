<?php 
/**
 * "YAML for Joomla Template" - http://www.jyaml.de  
 *
 * @version         $Id: form.php 467 2008-07-27 16:52:23Z hieblmedia $
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

<script language="javascript" type="text/javascript">
function submitbutton( pressbutton ) {
  var form = document.userform;
  var r = new RegExp("[\<|\>|\"|\'|\%|\;|\(|\)|\&|\+|\-]", "i");

  if (pressbutton == 'cancel') {
    form.task.value = 'cancel';
    form.submit();
    return;
  }

  // do field validation
  if (form.name.value == "") {
    alert( "<?php echo JText::_( 'Please enter your name.', true );?>" );
  } else if (form.email.value == "") {
    alert( "<?php echo JText::_( 'Please enter a valid e-mail address.', true );?>" );
  } else if (((form.password.value != "") || (form.password2.value != "")) && (form.password.value != form.password2.value)){
    alert( "<?php echo JText::_( 'REGWARN_VPASS2', true );?>" );
  } else if (r.exec(form.password.value)) {
    alert( "<?php printf( JText::_( 'VALID_AZ09', true ), JText::_( 'Password', true ), 4 );?>" );
  } else {
    form.submit();
  }
}
</script>

<h1 class="componentheading">
  <?php echo JText::_( 'Edit Your Details' ); ?>
</h1>

<form action="index.php" method="post" name="userform" autocomplete="off">
  <p>
    <label for="username"><?php echo JText::_( 'User Name' ); ?>:</label><br />
    <?php echo $this->user->get('username');?>
  </p>
  <p>
    <label for="name"><?php echo JText::_( 'Your Name' ); ?>:</label><br />
    <input class="inputbox" type="text" id="name" name="name" value="<?php echo $this->user->get('name');?>" size="40" />
  </p>
  <p>
    <label for="email"><?php echo JText::_( 'email' ); ?>:</label><br />
    <input class="inputbox" type="text" id="email" name="email" value="<?php echo $this->user->get('email');?>" size="40" />
  </p>
  <?php if($this->user->get('password')) : ?>
  <p>
    <label for="password"><?php echo JText::_( 'Password' ); ?>:</label><br />
    <input class="inputbox" type="password" id="password" name="password" value="" size="40" />
  </p>
  <p>  
    <label for="password2"><?php echo JText::_( 'Verify Password' ); ?>:</label><br />
    <input class="inputbox" type="password" id="password2" name="password2" size="40" />
  </p>
  <?php endif; ?>

  <?php if(isset($this->params)) :  echo $this->params->render( 'params' ); endif; ?>
  <button class="button" type="submit" onclick="submitbutton( this.form );return false;"><?php echo JText::_('Save'); ?></button>

  <input type="hidden" name="username" value="<?php echo $this->user->get('username');?>" />
  <input type="hidden" name="id" value="<?php echo $this->user->get('id');?>" />
  <input type="hidden" name="gid" value="<?php echo $this->user->get('gid');?>" />
  <input type="hidden" name="option" value="com_user" />
  <input type="hidden" name="task" value="save" />
  <?php echo JHTML::_( 'form.token' ); ?>
</form>