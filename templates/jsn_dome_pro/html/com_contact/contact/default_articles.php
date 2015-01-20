<?php
/**
 * @version		$Id: default_articles.php 20196 2011-01-09 02:40:25Z ian $
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
require_once JPATH_SITE . '/components/com_content/helpers/route.php';
<?php endif; ?>
<?php if ($this->params->get('show_articles')) : ?>
<div class="contact-articles">
	<?php if ($jsnUtils->isJoomla3()): ?>
		<ul class="nav nav-tabs nav-stacked">
		<?php foreach ($this->item->articles as $article) :	?>
			<li>
				<?php echo JHtml::_('link', JRoute::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catslug)), htmlspecialchars($article->title, ENT_COMPAT, 'UTF-8')); ?>
			</li>
		<?php endforeach; ?>
		</ul>
	<?php else : ?>
		<ol>
		<?php foreach ($this->item->articles as $article) :	?>
			<li>
			<?php $link = JRoute::_('index.php?option=com_content&view=article&id='.$article->id); ?>
			<?php echo '<a href="'.$link.'">' ?>
				<?php echo $article->text = htmlspecialchars($article->title, ENT_COMPAT, 'UTF-8'); ?>
				</a>
			</li>
		<?php endforeach; ?>
		</ol>
	<?php endif; ?>
</div>
<?php endif; ?>