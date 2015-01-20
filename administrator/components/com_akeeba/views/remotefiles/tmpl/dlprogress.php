<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 *
 * @since 3.2
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

<form action="index.php" name="adminForm" id="adminForm">
	<input type="hidden" name="option" value="com_akeeba" />
	<input type="hidden" name="view" value="remotefiles" />
	<input type="hidden" name="task" value="dltoserver" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="id" value="<?php echo $this->id ?>" />
	<input type="hidden" name="part" value="<?php echo $this->part ?>" />
	<input type="hidden" name="frag" value="<?php echo $this->frag ?>" />
</form>