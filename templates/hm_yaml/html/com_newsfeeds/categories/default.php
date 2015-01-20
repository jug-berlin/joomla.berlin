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

<?php if ($this->params->get('show_page_title')) : ?>
<h1 class="componentheading<?php echo $this->params->get('pageclass_sfx'); ?>">
  <?php echo $this->escape($this->params->get('page_title')); ?>
</h1>
<?php endif; ?>

<?php if ($this->params->def( 'show_comp_description', 1 ) || $this->params->get( 'image', -1 ) != -1) : ?>
<div class="contentdescription<?php echo $this->params->get( 'pageclass_sfx' ); ?>">

  <?php if ($this->params->get( 'image', -1 ) != -1) : ?>
  <img src="<?php echo $this->baseurl ?>/images/stories/<?php echo $this->params->get('image'); ?>" class="image_<?php echo $this->params->get( 'image_align' ); ?>" />
  <?php endif; ?>

  <?php echo $this->params->get( 'comp_description' ); ?>

  <?php if ($this->params->get( 'image', -1 ) != -1) : ?>
  <div class="wrap_image">&nbsp;</div>
  <?php endif; ?>

</div>
<?php endif; ?>

<?php if ( count( $this->categories ) ) : ?>
<ul class="newsfeed_categories">
  <?php foreach ( $this->categories as $category ) : ?>
  <li>
    <a href="<?php echo $category->link; ?>" class="category">
      <?php echo $category->title; ?>
    </a>
    <?php if ( $this->params->get( 'show_cat_items' ) ) : ?>
    &nbsp;<span class="small">(<?php echo $category->numlinks . ' ' . JText::_( 'items' ); ?>)</span>
    <?php endif; ?>
    <?php if ( $this->params->def( 'show_cat_description', 1 ) && $category->description) : ?>
    <br />
    <?php echo $category->description; ?>
    <?php endif; ?>
  </li>
  <?php endforeach; ?>
</ul>
<?php endif;
