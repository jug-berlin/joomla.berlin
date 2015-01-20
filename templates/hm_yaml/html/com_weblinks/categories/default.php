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


<div class="weblinks<?php echo $this->params->get('pageclass_sfx'); ?>">
  <?php if ($this->params->def('show_comp_description', 1) || $this->params->def('image', -1) != -1) : ?>
  <div class="floatbox contentdescription<?php echo $this->params->get('pageclass_sfx'); ?>">

    <?php if ($this->params->def('image', -1) != -1) : ?>
    <img src="<?php echo $this->baseurl ?>/images/stories/<?php echo $this->params->get('image'); ?>" alt="" class="image_<?php echo $this->params->get('image_align'); ?>" />
    <?php endif; ?>

    <?php if ($this->params->get('show_comp_description')) :
      echo '<p>'.$this->params->get('comp_description').'</p>';
    endif; ?>
  </div>
  <?php endif; ?>
</div>

<?php if (count($this->categories)) : ?>
<ul class="weblink_categories">
  <?php foreach ($this->categories as $category) : ?>
  <li>
    <a href="<?php echo $category->link; ?>" class="category<?php echo $this->params->get('pageclass_sfx'); ?>">
      <?php echo $category->title; ?>
    </a>
    &nbsp;<span class="small">(<?php echo $category->numlinks ?>)</span>
  </li>
  <?php endforeach; ?>
</ul>
<?php endif; ?>