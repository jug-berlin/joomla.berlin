<?php 
/**
 * "YAML for Joomla Template" - http://www.jyaml.de  
 *
 * @version         $Id: default_logout.php 467 2008-07-27 16:52:23Z hieblmedia $
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

<form action="index.php" method="post" name="login" id="login" class="logout_form<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
  <?php if ( $this->params->get( 'page_title' ) ) : ?>
  <h1 class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
    <?php echo $this->params->get( 'header_logout' ); ?>
  </h1>
  <?php endif; ?>

  <?php if ( $this->params->get( 'description_logout' ) || isset( $this->image ) ) : ?>
  <div class="contentdescription<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
    <?php if (isset ($this->image)) :
      echo $this->image;
    endif;
    if ( $this->params->get( 'description_logout' ) ) : ?>
    <p>
      <?php echo $this->params->get('description_logout_text'); ?>
    </p>
    <?php endif;
    if (isset ($this->image)) : ?>
    <div class="wrap_image">&nbsp;</div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <p><input type="submit" name="Submit" class="button" value="<?php echo JText::_( 'Logout' ); ?>" /></p>
  <input type="hidden" name="option" value="com_user" />
  <input type="hidden" name="task" value="logout" />
  <input type="hidden" name="return" value="<?php echo $this->return; ?>" />
</form>
