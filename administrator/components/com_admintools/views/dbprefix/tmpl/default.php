<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

?>
<div class="alert">
	<h3><?php echo JText::_('COM_ADMINTOOLS_COMMON_DEPRECATED_TITLE');?></h3>
	<p><?php echo JText::_('COM_ADMINTOOLS_COMMON_DEPRECATED_BODY');?></p>
</div>

<div class="alert alert-info">
	<a class="close" data-dismiss="alert" href="#">×</a>

	<p><?php echo JText::_('ATOOLS_LBL_DBREFIX_INTRO'); ?></p>
</div>

<?php if ($this->isDefaultPrefix): ?>
	<p class="alert alert-error">
		<a class="close" data-dismiss="alert" href="#">×</a>
		<?php echo JText::_('ATOOLS_LBL_DBREFIX_DEFAULTFOUND'); ?>
	</p>
<?php endif; ?>

<form name="adminForm" action="index.php" action="post" id="adminForm" class="form form-horizontal">
	<input type="hidden" name="option" value="com_admintools"/>
	<input type="hidden" name="view" value="dbprefix"/>
	<input type="hidden" name="task" value="change"/>

	<div class="control-group">
		<label for="oldprefix" class="control-label"><?php echo JText::_('ATOOLS_LBL_DBREFIX_OLDPREFIX') ?></label>

		<div class="controls">
			<input type="text" name="oldprefix" disabled="disabled" value="<?php echo $this->currentPrefix ?>"
				   size="7"/>
		</div>
	</div>

	<div class="control-group">
		<label for="prefix" class="control-label"><?php echo JText::_('ATOOLS_LBL_DBREFIX_NEWPREFIX') ?></label>

		<div class="controls">
			<input type="text" name="prefix" value="<?php echo $this->newPrefix ?>" size="7"/><br/>
		</div>
	</div>

	<div class="form-actions">
		<input type="submit" class="btn btn-warning btn-large"
			   value="<?php echo JText::_('ATOOLS_LBL_DBREFIX_CHANGE') ?>"/>
	</div>
</form>