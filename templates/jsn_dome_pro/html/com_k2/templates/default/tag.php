<?php
/**
 * @version		$Id: tag.php 16851 2012-10-10 09:47:56Z tuyetvt $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2011 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

?>

<!-- Start K2 Tag Layout -->

<div id="k2Container" class="tagView<?php if($this->params->get('pageclass_sfx')) echo ' '.$this->params->get('pageclass_sfx'); ?>">
	<?php if($this->params->get('show_page_title')): ?>
	<!-- Page title -->
	<div class="componentheading<?php echo $this->params->get('pageclass_sfx')?>"> <?php echo $this->escape($this->params->get('page_title')); ?> </div>
	<?php endif; ?>
	<?php if(count($this->items)): ?>
	<div class="tagItemList">
		<?php foreach($this->items as $item): ?>
		
		<!-- Start K2 Item Layout -->
		<div class="tagItemView">
			<div class="tagItemHeader">
				<?php if($item->params->get('tagItemTitle',1)): ?>
				<!-- Item title -->
				<h2 class="tagItemTitle">
					<?php if ($item->params->get('tagItemTitleLinked',1)): ?>
					<a href="<?php echo $item->link; ?>"> <?php echo $item->title; ?> </a>
					<?php else: ?>
					<?php echo $item->title; ?>
					<?php endif; ?>
				</h2>
				<?php endif; ?>
				<?php if($item->params->get('tagItemDateCreated',1)): ?>
				<!-- Date created -->
				<div class="jsn-article-toolbar"><div class="jsn-article-info"><p class="createdate"><span class="tagItemDateCreated"><?php echo JHTML::_('date', $item->created , JText::_('K2_DATE_FORMAT_LC2')); ?></span></p></div></div>
				<?php endif; ?>
			</div>
			<div class="clr"></div>
			<div class="tagItemBody">
				<?php if($item->params->get('tagItemImage',1) && !empty($item->imageGeneric)): ?>
				<!-- Item Image -->
				<div class="tagItemImageBlock"> <span class="tagItemImage"> <a href="<?php echo $item->link; ?>" title="<?php if(!empty($item->image_caption)) echo K2HelperUtilities::cleanHtml($item->image_caption); else echo K2HelperUtilities::cleanHtml($item->title); ?>"> <img src="<?php echo $item->imageGeneric; ?>" alt="<?php if(!empty($item->image_caption)) echo K2HelperUtilities::cleanHtml($item->image_caption); else echo K2HelperUtilities::cleanHtml($item->title); ?>" style="width:<?php echo $item->params->get('itemImageGeneric'); ?>px; height:auto;" /> </a> </span>
					<div class="clr"></div>
				</div>
				<?php endif; ?>
				<?php if($item->params->get('tagItemIntroText',1)): ?>
				<!-- Item introtext -->
				<div class="tagItemIntroText"> <?php echo $item->introtext; ?> </div>
				<?php endif; ?>
			</div>
			<?php if($item->params->get('tagItemExtraFields',0) && count($item->extra_fields)): ?>
			<!-- Item extra fields -->
			<div class="tagItemExtraFields">
				<h4><?php echo JText::_('K2_ADDITIONAL_INFO'); ?></h4>
				<ul>
					<?php foreach ($item->extra_fields as $key=>$extraField): ?>
					<?php if($extraField->value): ?>
					<li class="<?php echo ($key%2) ? "odd" : "even"; ?> type<?php echo ucfirst($extraField->type); ?> group<?php echo $extraField->group; ?>"> <span class="tagItemExtraFieldsLabel"><?php echo $extraField->name; ?></span> <span class="tagItemExtraFieldsValue"><?php echo $extraField->value; ?></span> </li>
					<?php endif; ?>
					<?php endforeach; ?>
				</ul>
				<div class="clr"></div>
			</div>
			<?php endif; ?>
			<?php if ($item->params->get('tagItemReadMore')): ?>
			<!-- Item "read more..." link -->
			<div class="clr"></div>
			<div class="tagItemContentFooter"> <a class="k2ReadMore" href="<?php echo $item->link; ?>"> <?php echo JText::_('K2_READ_MORE'); ?> </a> </div>
			<?php endif; ?>
			<div class="clr"></div>
			<?php if($item->params->get('tagItemCategory')): ?>
			<!-- Item category name -->
			<div class="tagItemLinks">
				<div class="tagItemCategory"><span><?php echo JText::_('K2_PUBLISHED_IN'); ?>:</span> <a href="<?php echo $item->category->link; ?>"><?php echo $item->category->name; ?></a> </div>
			</div>
			<?php endif; ?>
			<div class="clr"></div>
		</div>
		<!-- End K2 Item Layout -->
		
		<?php endforeach; ?>
	</div>
	
	<!-- Pagination -->
	<?php if($this->pagination->getPagesLinks()): ?>
	<div class="k2Pagination"> <?php echo $this->pagination->getPagesLinks(); ?>
		<div class="clr"></div>
		<?php echo $this->pagination->getPagesCounter(); ?> </div>
	<?php endif; ?>
	<?php endif; ?>
</div>
<!-- End K2 Tag Layout --> 
