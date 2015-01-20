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
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="option" value="com_akeeba" />
	<input type="hidden" name="view" value="s3import" />
	<input type="hidden" name="task" value="display" />
	
	<input type="hidden" id="ak_s3import_folder" name="folder" value="<?php echo $this->root ?>" />

	<div class="row-fluid">
		<div class="span12 form-inline">
			<input type="text" size="40" name="s3access" id="s3access" value="<?php echo $this->s3access ?>" placeholder="<?php echo JText::_('CONFIG_S3ACCESSKEY_TITLE') ?>" />
			<input type="password" size="40" name="s3secret" id="s3secret" value="<?php echo $this->s3secret ?>" placeholder="<?php echo JText::_('CONFIG_S3SECRETKEY_TITLE') ?>" />
			<?php if(empty($this->buckets)): ?>
			<button class="btn btn-primary" type="submit" onclick="ak_s3import_resetroot()">
				<i class="icon-globe icon-white"></i>
				<?php echo JText::_('S3IMPORT_LABEL_CONNECT') ?>
			</button>
			<?php else: ?>
			<?php echo $this->bucketSelect ?>
			<button class="btn btn-primary" type="submit" onclick="ak_s3import_resetroot()">
				<i class="icon-folder-open icon-white"></i>
				<?php echo JText::_('S3IMPORT_LABEL_CHANGEBUCKET') ?>
			</button>
			<?php endif;?>
		</div>
	</div>
	
	<div class="row-fluid">
		<div id="ak_crumbs_container">
			<ul class="breadcrumb">
				<li>
					<a href="javascript:ak_s3import_chdir('');">&lt;root&gt;</a>
					<span class="divider">/</span>
				</li>
				
				<?php $runningCrumb = ''; $i = 0; ?>
				<?php if(!empty($this->crumbs)) foreach($this->crumbs as $crumb):?>
				<?php $runningCrumb .= $crumb.'/'; $i++; ?>
				<li>
					<a href="javascript:ak_s3import_chdir('<?php echo $runningCrumb ?>');">
						<?php echo $crumb ?>
					</a>
					<?php if($i < count($this->crumbs)): ?>
					<span class="divider">/</span>
					<?php endif; ?>
				</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
	
	<div>
		<fieldset id="ak_folder_container">
			<legend><?php echo JText::_('FSFILTER_LABEL_DIRS'); ?></legend>
			<div id="folders">
				<?php if(!empty($this->contents['folders'])) foreach($this->contents['folders'] as $name => $record): ?>
				<div class="folder-container" onclick="ak_s3import_chdir('<?php echo $record['prefix'] ?>')">
					<span class="folder-icon-container">
						<span class="ui-icon ui-icon-folder-collapsed"></span>
					</span>
					<span class="folder-name">
						<?php echo rtrim($name,'/'); ?>
					</span>
				</div>
				<?php endforeach; ?>
			</div>
		</fieldset>

		<fieldset id="ak_files_container">
			<legend><?php echo JText::_('FSFILTER_LABEL_FILES'); ?></legend>
			<div id="files">
				<?php if(!empty($this->contents['files'])) foreach($this->contents['files'] as $name => $record): ?>
				<div class="file-container" onclick="window.location = 'index.php?option=com_akeeba&view=s3import&task=dltoserver&part=-1&frag=-1&layout=downloading&file=<?php echo $name?>'">
					<span class="file-icon-container">
						<span class="ui-icon ui-icon-document"></span>
					</span>
					<span class="file-name file-clickable">
						<?php echo basename($record['name']); ?>
					</span>
				</div>
				<?php endforeach; ?>
			</div>
		</fieldset>
	</div>
</form>

<script type="text/javascript">
akeeba.jQuery(document).ready(function($){
	// Work around Safari which ignores autocomplete=off (FOR CRYING OUT LOUD!)
	setTimeout(function(){
		$('#s3access').val('<?php echo $this->s3access ?>');
		$('#s3secret').val('<?php echo $this->s3secret ?>');
	}, 500);
});
</script>