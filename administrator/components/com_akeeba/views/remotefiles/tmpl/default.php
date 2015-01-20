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
	dt.message { display: none; }
	dd.message { list-style: none; }
</style>

<h2><?php echo JText::_('AKEEBA_REMOTEFILES') ?></h2>

<?php if(empty($this->actions)): ?>
<div class="alert alert-error">
	<h3 class="alert-heading"><?php echo JText::_('REMOTEFILES_ERR_NOTSUPPORTED_HEADER') ?></h3>
	<p>
		<?php echo JText::_('REMOTEFILES_ERR_NOTSUPPORTED'); ?>
	</p>
</div>
<?php else: ?>

<div id="cpanel">
<?php foreach($this->actions as $action): ?>
<?php if($action['type'] == 'button'): ?>
	<button class="btn <?php echo $action['class'] ?>" onclick="window.location = '<?php echo $action['link'] ?>'; return false;">
		<i class="<?php echo $action['icon'] ?>"></i>
		<?php echo $action['label']; ?>
	</button>
<?php endif; ?>
<?php endforeach; ?>
</div>
<div style="clear: both;"></div>

<h3><?php echo JText::_('REMOTEFILES_LBL_DOWNLOADLOCALLY')?></h3>
<?php $items = 0; foreach($this->actions as $action): ?>
<?php if($action['type'] == 'link'): ?>
<?php $items++ ?>
	<a href="<?php echo $action['link'] ?>" class="btn btn-mini">
		<i class="<?php echo $action['icon'] ?>"></i>
		<?php echo $action['label'] ?>
	</a>
<?php endif; ?>
<?php endforeach; ?>

<?php if(!$items): ?>
	<p class="alert">
		<?php echo JText::_('REMOTEFILES_LBL_NOTSUPPORTSLOCALDL') ?>
	</p>
<?php endif; ?>

<?php endif; ?>