<?php
/**
 * @version		$Id: default.php 14941 2012-08-10 08:34:20Z quocanhd $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2011 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
?>

<div id="k2ModuleBox<?php echo $module->id; ?>" class="k2ItemsBlock">
	<?php if($params->get('itemPreText')): ?>
	<p class="modulePretext"><?php echo $params->get('itemPreText'); ?></p>
	<?php endif; ?>
	<?php if(count($items)): ?>
	<ul>
		<?php foreach ($items as $key=>$item):	?>
		<li class="<?php echo ($key%2) ? "odd" : "even"; if(count($items)==$key+1) echo ' lastItem'; ?>"> 
			<!-- Plugins: BeforeDisplay --> 
			<?php echo $item->event->BeforeDisplay; ?> 
			<!-- K2 Plugins: K2BeforeDisplay --> 
			<?php echo $item->event->K2BeforeDisplay; ?>
			<?php if($params->get('itemTitle')): ?>
			<div class="moduleItemTitle"><a href="<?php echo $item->link; ?>"><?php echo $item->title; ?></a></div>
			<?php endif; ?>
			<?php if($params->get('itemAuthorAvatar')): ?>
			<a class="k2Avatar moduleItemAuthorAvatar" rel="author" href="<?php echo $item->authorLink; ?>"> <img src="<?php echo $item->authorAvatar; ?>" alt="<?php echo K2HelperUtilities::cleanHtml($item->author); ?>" style="width: 12px;" /> </a>
			<?php endif; ?>
			<?php if($params->get('itemAuthor')): ?>
			<div class="moduleItemAuthor"> <?php echo K2HelperUtilities::writtenBy($item->authorGender); ?>
				<?php if(isset($item->authorLink)): ?>
				<a rel="author" title="<?php echo K2HelperUtilities::cleanHtml($item->author); ?>" href="<?php echo $item->authorLink; ?>"><?php echo $item->author; ?></a>
				<?php else: ?>
				<?php echo $item->author; ?>
				<?php endif; ?>
			</div>
			<?php endif; ?>
			<?php if($params->get('itemDateCreated')): ?>
			<p class="createdate"><span class="moduleItemDateCreated"><?php echo JHTML::_('date', $item->created, JText::_('K2_DATE_FORMAT_LC')); ?></span></p>
			<?php endif; ?>
			<!-- Plugins: AfterDisplayTitle --> 
			<?php echo $item->event->AfterDisplayTitle; ?> 
			<!-- K2 Plugins: K2AfterDisplayTitle --> 
			<?php echo $item->event->K2AfterDisplayTitle; ?> 
			<!-- Plugins: BeforeDisplayContent --> 
			<?php echo $item->event->BeforeDisplayContent; ?> 
			<!-- K2 Plugins: K2BeforeDisplayContent --> 
			<?php echo $item->event->K2BeforeDisplayContent; ?>
			<?php if($params->get('itemImage') || $params->get('itemIntroText')): ?>
			<div class="moduleItemIntrotext">
				<?php if($params->get('itemImage') && isset($item->image)): ?>
				<a class="moduleItemImage" href="<?php echo $item->link; ?>" title="<?php echo JText::_('K2_CONTINUE_READING'); ?> &quot;<?php echo K2HelperUtilities::cleanHtml($item->title); ?>&quot;"> <img src="<?php echo $item->image; ?>" alt="<?php echo K2HelperUtilities::cleanHtml($item->title); ?>"/> </a>
				<?php endif; ?>
				<?php if($params->get('itemIntroText')): ?>
				<?php echo $item->introtext; ?>
				<?php endif; ?>
			</div>
			<?php endif; ?>
			<?php if($params->get('itemReadMore') && $item->fulltext): ?>
			<a class="moduleItemReadMore" href="<?php echo $item->link; ?>"> <?php echo JText::_('K2_READ_MORE'); ?> </a>
			<?php endif; ?>
			<?php if($params->get('itemExtraFields') && count($item->extra_fields)): ?>
			<div class="moduleItemExtraFields"> <b><?php echo JText::_('K2_ADDITIONAL_INFO'); ?></b>
				<ul>
					<?php foreach ($item->extra_fields as $extraField): ?>
					<?php if($extraField->value): ?>
					<li class="type<?php echo ucfirst($extraField->type); ?> group<?php echo $extraField->group; ?>"> <span class="moduleItemExtraFieldsLabel"><?php echo $extraField->name; ?></span> <span class="moduleItemExtraFieldsValue"><?php echo $extraField->value; ?></span>
						<div class="clr"></div>
					</li>
					<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php endif; ?>
			<div class="clr"></div>
			<?php if($params->get('itemVideo')): ?>
			<div class="moduleItemVideo"> <?php echo $item->video ; ?> <span class="moduleItemVideoCaption"><?php echo $item->video_caption ; ?></span> <span class="moduleItemVideoCredits"><?php echo $item->video_credits ; ?></span> </div>
			<?php endif; ?>
			<div class="clr"></div>
			<!-- Plugins: AfterDisplayContent --> 
			<?php echo $item->event->AfterDisplayContent; ?> 
			<!-- K2 Plugins: K2AfterDisplayContent --> 
			<?php echo $item->event->K2AfterDisplayContent; ?>
			<ul class="jsn-module-footer">
				<?php if($params->get('itemHits')): ?>
				<li><span class="moduleItemHits"> <?php echo JText::_('K2_READ'); ?> <?php echo $item->hits; ?> <?php echo JText::_('K2_TIMES'); ?> </span>
				<?php endif; ?>
				<?php if($params->get('itemCommentsCounter') && $componentParams->get('comments')): ?>
				<?php if(!empty($item->event->K2CommentsCounter)): ?>
				<!-- K2 Plugins: K2CommentsCounter --> 
				<?php echo $item->event->K2CommentsCounter; ?>
				<?php else: ?>
				<?php if($item->numOfComments>0): ?>
				<a class="moduleItemComments" href="<?php echo $item->link.'#itemCommentsAnchor'; ?>"> <?php echo $item->numOfComments; ?>
				<?php if($item->numOfComments>1) echo JText::_('K2_COMMENTS'); else echo JText::_('K2_COMMENT'); ?>
				</a>
				<?php else: ?>
				<a class="moduleItemComments" href="<?php echo $item->link.'#itemCommentsAnchor'; ?>"> <?php echo JText::_('K2_BE_THE_FIRST_TO_COMMENT'); ?> </a></li>
				<?php endif; ?>
				<?php endif; ?>
				<?php endif; ?>
				<?php if($params->get('itemTags') && count($item->tags)>0): ?>
				<li class="moduleItemTags"> <?php echo JText::_('K2_TAGS'); ?>:
					<?php foreach ($item->tags as $tag): ?>
					<a href="<?php echo $tag->link; ?>"><?php echo $tag->name; ?></a>
					<?php endforeach; ?>
				</li>
				<?php endif; ?>
				<?php if($params->get('itemCategory')): ?>
				<li class="moduleItemCategory"><?php echo JText::_('K2_PUBLISHED_IN') ; ?>: <a href="<?php echo $item->categoryLink; ?>"><?php echo $item->categoryname; ?></a></li>
				<?php endif; ?>
				<?php if($params->get('itemAttachments') && count($item->attachments)): ?>
				<li class="moduleAttachments"> <strong><?php echo JText::_('K2_DOWNLOAD_ATTACHMENTS'); ?></strong><br />
					<?php foreach ($item->attachments as $attachment): ?>
					<a title="<?php echo K2HelperUtilities::cleanHtml($attachment->titleAttribute); ?>" href="<?php echo $attachment->link; ?>"><?php echo $attachment->title; ?></a><br />
					<?php endforeach; ?>
				</li>
				<?php endif; ?>

			</ul>
			<!-- Plugins: AfterDisplay --> 
			<?php echo $item->event->AfterDisplay; ?> 
			<!-- K2 Plugins: K2AfterDisplay --> 
			<?php echo $item->event->K2AfterDisplay; ?>
			<div class="clr"></div>
		</li>
		<?php endforeach; ?>
		<li class="clearList"></li>
	</ul>
	<?php endif; ?>
	<?php if($params->get('itemCustomLink')): ?>
	<a class="moduleCustomLink" href="<?php echo $params->get('itemCustomLinkURL'); ?>" title="<?php echo K2HelperUtilities::cleanHtml($itemCustomLinkTitle); ?>"><?php echo $itemCustomLinkTitle; ?></a>
	<?php endif; ?>
	<?php if($params->get('feed')): ?>
	<div class="k2FeedIcon"> <a href="<?php echo JRoute::_('index.php?option=com_k2&view=itemlist&format=feed&moduleID='.$module->id); ?>" title="<?php echo JText::_('K2_SUBSCRIBE_TO_THIS_RSS_FEED'); ?>"> <span><?php echo JText::_('K2_SUBSCRIBE_TO_THIS_RSS_FEED'); ?></span> </a>
		<div class="clr"></div>
	</div>
	<?php endif; ?>
</div>
