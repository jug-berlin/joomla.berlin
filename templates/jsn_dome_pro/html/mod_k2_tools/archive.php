<?php 
/**
 * @version		$Id: archive.php 1492 2012-02-22 17:40:09Z joomlaworks@gmail.com $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2012 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

?>
<div id="k2ModuleBox<?php echo $module->id; ?>" class="k2ArchivesBlock">
  <ul>
    <?php foreach ($months as $month): ?>
    <li>
      <?php if ($params->get('archiveCategory', 0) > 0): ?>
      <a href="<?php echo JRoute::_('index.php?option=com_k2&view=itemlist&task=date&month='.$month->m.'&year='.$month->y.'&catid='.$params->get('archiveCategory')); ?>">
        <?php echo $month->name.' '.$month->y; ?>
        <?php if ($params->get('archiveItemsCounter')) echo '('.$month->numOfItems.')'; ?>
      </a>
      <?php else: ?>
      <a href="<?php echo JRoute::_('index.php?option=com_k2&view=itemlist&task=date&month='.$month->m.'&year='.$month->y); ?>">
        <?php echo $month->name.' '.$month->y; ?>
        <?php if ($params->get('archiveItemsCounter')) echo '('.$month->numOfItems.')'; ?>
      </a>
      <?php endif; ?>
    </li>
    <?php endforeach; ?>
  </ul>
</div>
