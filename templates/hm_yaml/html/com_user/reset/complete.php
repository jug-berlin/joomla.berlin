<?php 
/**
 * "YAML for Joomla Template" - http://www.jyaml.de  
 *
 * @version         $Id: complete.php 467 2008-07-27 16:52:23Z hieblmedia $
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

<h1 class="componentheading"><?php echo JText::_('Reset your Password'); ?></h1>

<form action="index.php?option=com_user&amp;task=completereset" method="post" class="josForm form-validate">
  <p><?php echo JText::_('RESET_PASSWORD_COMPLETE_DESCRIPTION'); ?></p>
  
  <p>
    <label for="password1" class="hasTip" title="<?php echo JText::_('RESET_PASSWORD_PASSWORD1_TIP_TITLE'); ?>::<?php echo JText::_('RESET_PASSWORD_PASSWORD1_TIP_TEXT'); ?>"><?php echo JText::_('Password'); ?>:</label><br />
    <input id="password1" name="password1" type="password" class="required validate-password" />
  </p>
  <p>
    <label for="password2" class="hasTip" title="<?php echo JText::_('RESET_PASSWORD_PASSWORD2_TIP_TITLE'); ?>::<?php echo JText::_('RESET_PASSWORD_PASSWORD2_TIP_TEXT'); ?>"><?php echo JText::_('Verify Password'); ?>:</label><br />
    <input id="password2" name="password2" type="password" class="required validate-password" />
  </p>

  <button type="submit" class="validate"><?php echo JText::_('Submit'); ?></button>
  <?php echo JHTML::_( 'form.token' ); ?>
</form>