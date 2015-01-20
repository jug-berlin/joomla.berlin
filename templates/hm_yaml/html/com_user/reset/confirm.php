<?php 
/**
 * "YAML for Joomla Template" - http://www.jyaml.de  
 *
 * @version         $Id: confirm.php 467 2008-07-27 16:52:23Z hieblmedia $
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

<h1 class="componentheading"><?php echo JText::_('Confirm your Account'); ?></h1>

<form action="index.php?option=com_user&amp;task=confirmreset" method="post" class="josForm form-validate">
  <p><?php echo JText::_('RESET_PASSWORD_CONFIRM_DESCRIPTION'); ?></p>
  
  <p>
    <label for="token" class="hasTip" title="<?php echo JText::_('RESET_PASSWORD_TOKEN_TIP_TITLE'); ?>::<?php echo JText::_('RESET_PASSWORD_TOKEN_TIP_TEXT'); ?>"><?php echo JText::_('Token'); ?>:</label><br />
    <input id="token" name="token" type="text" class="required" size="36" />
  </p>

  <button type="submit" class="validate"><?php echo JText::_('Submit'); ?></button>
  <?php echo JHTML::_( 'form.token' ); ?>
</form>