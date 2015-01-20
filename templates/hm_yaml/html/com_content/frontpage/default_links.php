<?php 
/**
 * "YAML for Joomla Template" - http://www.jyaml.de  
 *
 * @version         $Id: default_links.php 467 2008-07-27 16:52:23Z hieblmedia $
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

<h2>
  <?php echo JText::_('Read more...'); ?>
</h2>

<ul>
  <?php foreach ($this->links as $link) : ?>
  <li>
    <a class="blogsection" href="<?php echo JRoute::_('index.php?view=article&id='.$link->slug); ?>">
      <?php echo $link->title; ?>
    </a>
  </li>
  <?php endforeach; ?>
</ul>
