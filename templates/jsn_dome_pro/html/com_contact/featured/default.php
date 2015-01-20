<?php
/**
 * @version		$Id: default.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$app 		= JFactory::getApplication();
$template 	= $app->getTemplate();
require_once JPATH_THEMES.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'jsn_utils.php';
$jsnUtils   = JSNUtils::getInstance();
JHtml::addIncludePath(JPATH_COMPONENT. DIRECTORY_SEPARATOR .'helpers');

// If the page class is defined, add to class as suffix.
// It will be a separate class if the user starts it with a space
?>
<?php if ($jsnUtils->isJoomla3()): ?>
<div class="blog-featured<?php echo $this->pageclass_sfx;?>">
<?php if ($this->params->get('show_page_heading') != 0 ): ?>
	<h1>
	<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
<?php endif; ?>

<?php echo $this->loadTemplate('items'); ?>
<?php if ($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2 && $this->pagination->pagesTotal > 1)) : ?>
	<div class="pagination">

		<?php if ($this->params->def('show_pagination_results', 1)) : ?>
			<p class="counter">
				<?php echo $this->pagination->getPagesCounter(); ?>
			</p>
		<?php  endif; ?>
				<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
<?php endif; ?>

</div>
<?php else : ?>
<div class="com-contact <?php echo $this->pageclass_sfx; ?>">
	<div class="contact-category<?php echo $this->pageclass_sfx;?>">
		<?php if ($this->params->def('show_page_heading', 1)) : ?>
		<h2 class="componentheading"> <?php echo $this->escape($this->params->get('page_heading')); ?> </h2>
		<?php endif; ?>
		<?php echo $this->loadTemplate('items'); ?>
		<?php if ($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2 && $this->pagination->get('pages.total') > 1)) : ?>
		<div class="jsn-pagination-container"> <?php echo $this->pagination->getPagesLinks(); ?>
			<?php if ($this->params->def('show_pagination_results', 1)) : ?>
			<p class="jsn-pageinfo"> <?php echo $this->pagination->getPagesCounter(); ?> </p>
			<?php  endif; ?>
		</div>
		<?php endif; ?>
	</div>
</div>
<?php endif; ?>