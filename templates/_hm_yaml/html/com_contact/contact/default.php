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

<?php if ($this->contact->name && $this->contact->params->get('show_name')) : ?>
<h1 class="componentheading<?php echo $this->params->get('pageclass_sfx'); ?>">
  <?php echo $this->contact->name; ?>
</h1>
<?php elseif ($this->params->get('show_page_title')) : ?>
<h1 class="componentheading<?php echo $this->params->get('pageclass_sfx'); ?>">
  <?php echo $this->escape($this->params->get('page_title')); ?>
</h1>
<?php endif; ?>

<div class="contact<?php echo $this->params->get('pageclass_sfx'); ?>">
  <?php if ($this->params->get('show_contact_list') && count($this->contacts) > 1) : ?>
  <form method="post" name="selectForm" id="selectForm">
    <?php echo JText::_('Select Contact'); ?>
    <br />
    <p><?php echo JHTML::_('select.genericlist', $this->contacts, 'contact_id', 'class="inputbox" onchange="this.form.submit()"', 'id', 'name', $this->contact->id); ?></p>
    <input type="hidden" name="option" value="com_contact" />
  </form>
  <?php endif; ?>
  
  <?php if ($this->contact->image && $this->contact->params->get('show_image')) : ?>
  <div class="float_right">
    <?php echo JHTML::_('image', 'images/stories/'.$this->contact->image, JText::_( 'Contact' ), array('align' => 'middel')); ?>
  </div>
  <?php endif; ?>

  <?php if ($this->contact->con_position && $this->contact->params->get('show_position')) : ?>
  <p>
    <?php echo $this->contact->con_position; ?>
  </p>
  <?php endif; ?>

  <?php echo $this->loadTemplate('address'); ?>

  <?php if ( $this->contact->params->get('allow_vcard')) : ?>
  <p>
    <?php echo JText::_('Download information as a'); ?>
    <a href="index.php?option=com_contact&amp;task=vcard&amp;contact_id=<?php echo $this->contact->id; ?>&amp;format=raw">
      <?php echo JText::_('VCard'); ?>
    </a>
  </p>
  <?php endif; ?>

  <?php if ($this->contact->params->get('show_email_form')) :
    echo $this->loadTemplate('form');
  endif; ?>
</div>