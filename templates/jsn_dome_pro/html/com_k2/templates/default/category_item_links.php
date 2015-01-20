<?php
/**
 * @version		$Id: category_item_links.php 14941 2012-08-10 08:34:20Z quocanhd $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2011 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Define default image size (do not change)
K2HelperUtilities::setDefaultImage($this->item, 'itemlist', $this->params);

?>
<?php if($this->item->params->get('catItemImage') && !empty($this->item->image)): ?>
<!-- Item Image --> 
<span class="catItemImage"> <a href="<?php echo $this->item->link; ?>" title="<?php if(!empty($this->item->image_caption)) echo K2HelperUtilities::cleanHtml($this->item->image_caption); else echo K2HelperUtilities::cleanHtml($this->item->title); ?>"> <img src="<?php echo $this->item->image; ?>" alt="<?php if(!empty($this->item->image_caption)) echo K2HelperUtilities::cleanHtml($this->item->image_caption); else echo K2HelperUtilities::cleanHtml($this->item->title); ?>" style="width:<?php echo $this->item->imageWidth; ?>px; height:auto;" /> </a> </span>
<?php endif; ?>
<!-- Start K2 Item Layout (links) -->
<?php if($this->item->params->get('catItemTitle')): ?>
<!-- Item title -->
<?php if ($this->item->params->get('catItemTitleLinked')): ?>
<a href="<?php echo $this->item->link; ?>"> <?php echo $this->item->title; ?> </a>
<?php else: ?>
<?php echo $this->item->title; ?>
<?php endif; ?>
<?php endif; ?>
<!-- End K2 Item Layout (links) --> 