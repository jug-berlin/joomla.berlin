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

<?php foreach ($this->items as $item) : ?>
<tr class="sectiontableentry<?php echo $item->odd + 1; ?>">
  <td headers="Count">
    <?php echo $item->count + 1; ?>
  </td>

  <?php if ($this->params->get('show_position')) : ?>
  <td headers="Position">
    <?php echo $item->con_position; ?>
  </td>
  <?php endif; ?>

  <td height="20" headers="Name">
    <a href="<?php echo $item->link; ?>" class="category<?php echo $this->params->get('pageclass_sfx'); ?>">
      <?php echo $item->name; ?>
    </a>
  </td>

  <?php if ($this->params->get('show_email')) : ?>
  <td headers="Mail">
    <?php echo $item->email_to; ?>
  </td>
  <?php endif; ?>

  <?php if ($this->params->get('show_telephone')) : ?>
  <td headers="Phone">
    <?php echo $item->telephone; ?>
  </td>
  <?php endif; ?>

  <?php if ($this->params->get('show_mobile')) : ?>
  <td headers="Mobile">
    <?php echo $item->mobile; ?>
  </td>
  <?php endif; ?>

  <?php if ($this->params->get('show_fax')) : ?>
  <td headers="Fax">
    <?php echo $item->fax; ?>
  </td>
  <?php endif; ?>
</tr>
<?php endforeach; ?>