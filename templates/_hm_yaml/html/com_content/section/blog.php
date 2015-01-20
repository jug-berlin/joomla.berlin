<?php 
/**
 * "YAML for Joomla Template" - http://www.jyaml.de  
 *
 * @version         $Id: blog.php 467 2008-07-27 16:52:23Z hieblmedia $
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

<div class="blog<?php echo $this->params->get('pageclass_sfx'); ?>">

  <?php if ($this->params->def('show_description', 1) || $this->params->def('show_description_image', 1)) : ?>
  <div class="contentdescription<?php echo $this->params->get('pageclass_sfx'); ?>">

    <?php if ($this->params->get('show_description_image') && $this->section->image) : ?>
    <img src="<?php echo $this->baseurl ?>/images/stories/<?php echo $this->section->image; ?>" class="image_<?php echo $this->section->image_position; ?>" />
    <?php endif; ?>

    <?php if ($this->params->get('show_description') && $this->section->description) :
      echo $this->section->description;
    endif; ?>

    <?php if ($this->params->get('show_description_image') && $this->section->image) : ?>
    <div class="wrap_image">&nbsp;</div>
    <?php endif; ?>

  </div>
  <?php endif; ?>

  <?php $i = $this->pagination->limitstart;
  $rowcount = $this->params->def('num_leading_articles', 1);
  for ($y = 0; $y < $rowcount && $i < $this->total; $y++, $i++) : ?>
    <div class="leading<?php echo $this->params->get('pageclass_sfx'); ?>">
      <?php $this->item =& $this->getItem($i, $this->params);
      echo $this->loadTemplate('item'); ?>
    </div>
    <span class="leading_separator<?php echo $this->params->get('pageclass_sfx'); ?>">&nbsp;</span>
  <?php endfor; ?>

  <?php $introcount = $this->params->def('num_intro_articles', 4);
  if ($introcount) :
    $colcount = $this->params->def('num_columns', 1);
    if ($colcount == 0) :
      $colcount = 1;
    endif;
    $rowcount = (int) $introcount / $colcount;
    $ii = 0;
    
    // YAML Subtemplates (1-4 Columns)
    $subcolint = round(100/$colcount);
    $colalign = 'l';
    $subalign = 'l';
    
    for ($y = 0; $y < $rowcount && $i < $this->total; $y++) : ?>
      <div class="subcolumns <?php echo $this->params->get('pageclass_sfx'); ?>">
        <?php for ($z = 0; $z < $colcount && $ii < $introcount && $i < $this->total; $z++, $i++, $ii++) : ?>
        
           <?php
           // Subcolum alignment dedect
             if ( $z==0 ) {
               $colalign = 'l';
               $subalign = 'l';
             } elseif ( $z == ($colcount-1) ) {
               $colalign = 'r';    
               $subalign = 'r';     
             } else {
               $colalign = 'l';  
               $subalign = '';         
             }
           ?>
           
           <div class="c<?php echo $subcolint.$colalign; ?> article_column column<?php echo $z + 1; ?> cols<?php echo $colcount; ?>">
            <div class="subc<?php echo $subalign; ?> article_column_content">
              <div class="article_column_inner">
                <?php $this->item =& $this->getItem($i, $this->params);
                echo $this->loadTemplate('item'); ?>
              </div>
              <span class="article_separator">&nbsp;</span>
            </div>
          </div>
          
        <?php endfor; ?>
      </div>
      <span class="row_separator<?php echo $this->params->get('pageclass_sfx'); ?>">&nbsp;</span>
    <?php endfor;
  //////////////////////////////////
  endif; ?>
  
  <?php $numlinks = $this->params->def('num_links', 4);
  if ($numlinks && $i < $this->total) : ?>
  <div class="blog_more<?php echo $this->params->get('pageclass_sfx'); ?>">
    <?php $this->links = array_slice($this->items, $i - $this->pagination->limitstart, $i - $this->pagination->limitstart + $numlinks);
    echo $this->loadTemplate('links'); ?>
  </div>
  <?php endif; ?>

  <?php if ($this->params->def('show_pagination_results', 1)) : ?>
  <p class="counter">
    <?php echo $this->pagination->getPagesCounter(); ?>
  </p>
  <?php endif; ?>

  <?php if ($this->params->def('show_pagination', 2)) :
    echo $this->pagination->getPagesLinks();
  endif; ?>

</div>
