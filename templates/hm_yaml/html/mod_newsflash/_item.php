<?php 
/**
 * "YAML for Joomla Template" - http://www.jyaml.de  
 *
 * @version         $Id: _item.php 467 2008-07-27 16:52:23Z hieblmedia $
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

<?php if ($params->get('item_title')) : ?>
<h4 class="newsflash_title">
  <?php if ($params->get('link_titles') && $linkOn != '') : ?>
  <a href="<?php echo $item->linkOn;?>" class="contentpagetitle<?php echo $params->get('moduleclass_sfx'); ?>">
    <?php echo $item->title; ?>
  </a>
  <?php else :
    echo $item->title;
  endif; ?>
</h4>
<?php endif; ?>

<?php if (!$params->get('intro_only')) :
  echo $item->afterDisplayTitle;
endif; ?>

<?php echo $item->beforeDisplayContent;
echo JFilterOutput::ampReplace($item->text);
if (isset($item->linkOn) && $item->readmore) : ?>
<a href="<?php echo $item->linkOn; ?>" class="readon">
  <?php echo JText::_('Read more text'); ?>
</a>
<?php endif; ?>
<span class="article_separator newsflash_seperator">&nbsp;</span>
