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

<h1 class="componentheading"><?php echo JText::_('FORGOT_YOUR_PASSWORD'); ?></h1>

<form action="index.php?option=com_user&amp;task=requestreset" method="post" class="josForm form-validate">
  <p><?php echo JText::_('RESET_PASSWORD_REQUEST_DESCRIPTION'); ?></p>

  <label for="email" class="hasTip" title="<?php echo JText::_('RESET_PASSWORD_EMAIL_TIP_TITLE'); ?>::<?php echo JText::_('RESET_PASSWORD_EMAIL_TIP_TEXT'); ?>"><?php echo JText::_('Email Address'); ?>:</label><br />
  <input id="email" name="email" type="text" class="required validate-email" />

  <input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
  <button type="submit" class="validate"><?php echo JText::_('Submit'); ?></button>
  <?php echo JHTML::_( 'form.token' ); ?>
</form>