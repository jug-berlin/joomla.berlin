<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @since 3.4
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

JHtml::_('behavior.framework');
?>
<style type="text/css">
	dl { display: none; }
</style>

<div id="backup-percentage" class="progress">
	<div id="progressbar-inner" class="bar" style="width: <?php echo $this->percent ?>%"></div>
</div>

<div class="well">
	<?php echo JText::sprintf('REMOTEFILES_LBL_DOWNLOADEDSOFAR', $this->done, $this->total, $this->percent); ?>
</div>