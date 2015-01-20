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

<?php JHTML::_('stylesheet', 'poll_bars.css', 'components/com_poll/assets/'); ?>

<?php if ($this->params->get('show_page_title')) : ?>
<h1 class="componentheading<?php echo $this->params->get('pageclass_sfx'); ?>">
  <?php echo $this->poll->title ? $this->escape($this->poll->title) : $this->escape($this->params->get('page_title')); ?>
</h1>
<?php endif; ?>

<div class="poll<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
  <form action="index.php" method="post" name="poll" id="poll">
    <p>
      <label for="id"><?php echo JText::_( 'Select Poll' ); ?></label>
      <?php echo $this->lists['polls']; ?>
    </p>
  </form>
  <?php if (count($this->votes)) :
    echo $this->loadTemplate( 'graph' );
  endif; ?>
</div>
