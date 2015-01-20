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

<script type="text/javascript">
  function tableOrdering(order, dir, task) {
    var form = document.adminForm;

    form.filter_order.value = order;
    form.filter_order_Dir.value = dir;
    document.adminForm.submit(task);
  }
</script>

<form action="<?php echo htmlspecialchars($this->action); ?>" method="post" name="adminForm">
  <br />
  <p>
    <label for="limit"><?php echo JText :: _('Display Num'); ?></label>&nbsp;
    <?php echo $this->pagination->getLimitBox(); ?>
  </p>
  <input type="hidden" name="filter_order" value="<?php echo $this->lists['order'] ?>" />
  <input type="hidden" name="filter_order_Dir" value="" />
</form>

<table class="weblinks">
  <?php if ($this->params->def('show_headings', 1)) : ?>
  <tr>

    <th class="sectiontableheader<?php echo $this->params->get('pageclass_sfx'); ?>" width="5" id="num">
      <?php echo JText::_('Num'); ?>
    </th>

    <th width="90%" class="sectiontableheader<?php echo $this->params->get('pageclass_sfx'); ?>" id="title">
      <?php echo JHTML::_('grid.sort', 'Web Link', 'title', $this->lists['order_Dir'], $this->lists['order']); ?>
    </th>

    <?php if ($this->params->get('show_link_hits')) : ?>
    <th width="10%" class="sectiontableheader<?php echo $this->params->get('pageclass_sfx'); ?>" nowrap="nowrap" id="hits">
      <?php echo JHTML::_('grid.sort', 'Hits', 'hits', $this->lists['order_Dir'], $this->lists['order']); ?>
    </th>
    <?php endif; ?>

  </tr>
  <?php endif; ?>

  <?php foreach ($this->items as $item) : ?>
  <tr class="sectiontableentry<?php echo $item->odd + 1; ?>">

    <td align="center" headers="num">
      <?php echo $this->pagination->getRowOffset($item->count); ?>
    </td>

    <td headers="title">
      <?php if ($item->image) :
        echo $item->image;
      endif;
      echo $item->link;
      if ($this->params->get('show_link_description')) : ?>
      <br />
      <?php echo nl2br($item->description);
      endif; ?>
    </td>

    <?php if ($this->params->get('show_link_hits')) : ?>
    <td headers="hits">
      <?php echo $item->hits; ?>
    </td>
    <?php endif; ?>

  </tr>
  <?php endforeach; ?>
</table>

<p class="counter">
  <?php echo $this->pagination->getPagesCounter(); ?>
</p>
<?php echo $this->pagination->getPagesLinks(); ?>