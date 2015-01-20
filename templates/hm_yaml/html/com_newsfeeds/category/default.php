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

<?php if ( $this->params->get( 'show_page_title' ) ) : ?>
<h1 class="componentheading<?php echo $this->params->get('pageclass_sfx'); ?>">
  <?php echo $this->category->title; ?>
</h1>
<?php endif; ?>

<div class="newsfeed<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
  <?php if ( $this->category->image || $this->category->description ) : ?>
  <div class="contentdescription<?php echo $this->params->get('pageclass_sfx'); ?>">

    <?php if ( $this->category->image ) : ?>
    <img src="<?php echo $this->baseurl ?>/images/stories/<?php echo $this->category->image; ?>" class="image_<?php echo $this->category->image_position; ?>" />
    <?php endif; ?>

    <?php if ( $this->params->get( 'description' ) ) :
      echo $this->category->description;
    endif; ?>

    <?php if ( $this->category->image ) : ?>
    <div class="wrap_image">&nbsp;</div>
    <?php endif; ?>

  </div>
  <?php endif; ?>

  <?php echo $this->loadTemplate('items'); ?>
</div>
