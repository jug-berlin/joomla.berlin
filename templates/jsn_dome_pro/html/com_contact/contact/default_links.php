<?php
/**
 * @version		$Id: default_links.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Joomla.Site
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app 		= JFactory::getApplication();
$template 	= $app->getTemplate();
require_once JPATH_THEMES.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'jsn_utils.php';
$jsnUtils   = JSNUtils::getInstance();
?>
<?php if ($jsnUtils->isJoomla3()): ?>
<?php if ($this->params->get('presentation_style') == 'sliders'): ?>
        <?php echo JHtml::_('bootstrap.addSlide', 'slide-contact', JText::_('COM_CONTACT_LINKS'), 'display-links'); ?>
<?php endif; ?>
<?php if ($this->params->get('presentation_style') == 'tabs'): ?>
        <?php echo JHtml::_('bootstrap.addPanel', 'myTab', 'display-links'); ?>
<?php endif; ?>
<?php if ($this->params->get('presentation_style') == 'plain'):?>
        <?php echo '<h3>'. JText::_('COM_CONTACT_LINKS').'</h3>';  ?>
<?php endif; ?>

			<div class="contact-links">
				<ul class="nav nav-tabs nav-stacked">
					<?php
					foreach(range('a', 'e') as $char) :// letters 'a' to 'e'
						$link = $this->contact->params->get('link'.$char);
						$label = $this->contact->params->get('link'.$char.'_name');

						if (!$link) :
							continue;
						endif;

						// Add 'http://' if not present
						$link = (0 === strpos($link, 'http')) ? $link : 'http://'.$link;

						// If no label is present, take the link
						$label = ($label) ? $label : $link;
						?>
						<li>
							<a href="<?php echo $link; ?>">
							    <?php echo $label; ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

<?php if ($this->params->get('presentation_style') == 'sliders'): ?>
        <?php echo JHtml::_('bootstrap.endSlide'); ?>
<?php endif; ?>
<?php if ($this->params->get('presentation_style') == 'tabs'): ?>
        <?php echo JHtml::_('bootstrap.endPanel'); ?>
<?php endif; ?>
<?php else : ?>
<?php if ($this->params->get('presentation_style')!='plain'){?>
	<?php echo JHtml::_($this->params->get('presentation_style').'.panel', JText::_('COM_CONTACT_LINKS'), 'display-links'); }?>
<?php if ($this->params->get('presentation_style')=='plain'){?>
	<?php echo '<h3>'.JText::_('COM_CONTACT_LINKS').'</h3>'; }?>

<div class="contact-links">

	<ul>
		<?php if ($this->contact->params->get('linka')) : ?>
			<li><a href="<?php echo $this->contact->params->get('linka') ?>"><?php echo $this->contact->params->get('linka_name')  ?></a></li>
		<?php endif; ?>
		<?php if ($this->contact->params->get('linkb')) : ?>
			<li><a href="<?php echo $this->contact->params->get('linkb') ?>"><?php echo $this->contact->params->get('linkb_name')  ?></a></li>
		<?php endif; ?>
		<?php if ($this->contact->params->get('linkc')) : ?>
			<li><a href="<?php echo $this->contact->params->get('linkc') ?>"><?php echo $this->contact->params->get('linkc_name')  ?></a></li>
		<?php endif; ?>
		<?php if ($this->contact->params->get('linkd')) : ?>
			<li><a href="<?php echo $this->contact->params->get('linkd') ?>"><?php echo $this->contact->params->get('linkd_name')  ?></a></li>
		<?php endif; ?>
		<?php if ($this->contact->params->get('linke')) : ?>
			<li><a href="<?php echo $this->contact->params->get('linke') ?>"><?php echo $this->contact->params->get('linke_name')  ?></a></li>
		<?php endif; ?>
	</ul>
</div>
<?php endif; ?>
