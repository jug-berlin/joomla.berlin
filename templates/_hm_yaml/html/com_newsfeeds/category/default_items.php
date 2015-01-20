<?php 
/**
 * "YAML for Joomla Template" - http://www.jyaml.de  
 *
 * @version         $Id: default_items.php 467 2008-07-27 16:52:23Z hieblmedia $
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

<?php if ( $this->params->get( 'show_limit' ) ) : ?>
<div class="display">
  <form action="index.php" method="post" name="adminForm">
    <label for="limit"><?php echo JText::_( 'Display Num' ); ?>&nbsp;</label>
    <?php echo $this->pagination->getLimitBox(); ?>
  </form>
</div>
<?php endif; ?>


<table class="newsfeeds">

  <?php if ( $this->params->get( 'show_headings' ) ) : ?>
  <tr>

    <th class="sectiontableheader<?php echo $this->params->get( 'pageclass_sfx' ); ?>" width="5" id="num">
      <?php echo JText::_( 'Num' ); ?>
    </th>

    <?php if ( $this->params->get( 'show_name' ) ) : ?>
    <th width="90%" class="sectiontableheader<?php echo $this->params->get( 'pageclass_sfx' ); ?>" id="name">
      <?php echo JText::_( 'Feed Name' ); ?>
    </th>
    <?php endif; ?>

    <?php if ( $this->params->get( 'show_articles' ) ) : ?>
    <th width="10%" class="sectiontableheader<?php echo $this->params->get( 'pageclass_sfx' ); ?>" nowrap="nowrap" id="num_a">
      <?php echo JText::_('Num Articles'); ?>
    </th>
    <?php endif; ?>

  </tr>
  <?php endif; ?>

  <?php foreach ( $this->items as $item ) : ?>
  <tr class="sectiontableentry<?php echo $item->odd + 1; ?>">

    <td align="center" width="5" headers="num">
      <?php echo $item->count + 1; ?>
    </td>
    
    <td width="90%" headers="name">
      <a href="<?php echo $item->link; ?>" class="category<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
        <?php echo $item->name; ?>
      </a>
    </td>
  
    <?php if ( $this->params->get( 'show_articles' ) ) : ?>
    <td width="10%" headers="num_a"><?php echo $item->numarticles; ?></td>
    <?php endif; ?>

  </tr>
  <?php endforeach; ?>

</table>

<p class="counter">
  <?php echo $this->pagination->getPagesCounter(); ?>
</p>
<?php echo $this->pagination->getPagesLinks(); ?>
