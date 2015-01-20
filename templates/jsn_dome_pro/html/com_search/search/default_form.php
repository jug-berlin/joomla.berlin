<?php 
defined('_JEXEC') or die;
$lang = JFactory::getLanguage();
$upper_limit = $lang->getUpperLimitSearchWord();

$app 		= JFactory::getApplication();
$template 	= $app->getTemplate();
require_once JPATH_THEMES.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'jsn_utils.php';
$jsnUtils   = JSNUtils::getInstance();
?>
<?php if ($jsnUtils->isJoomla3()): ?>
<?php JHtml::_('bootstrap.tooltip'); ?>
<form id="searchForm" action="<?php echo JRoute::_('index.php?option=com_search');?>" method="post">

	<div class="btn-toolbar">
		<div class="btn-group pull-left">
			<input type="text" name="searchword" placeholder="<?php echo JText::_('COM_SEARCH_SEARCH_KEYWORD'); ?>" id="search-searchword" size="30" maxlength="<?php echo $upper_limit; ?>" value="<?php echo $this->escape($this->origkeyword); ?>" class="inputbox" />
		</div>
		<div class="btn-group pull-left">
			<button name="Search" onclick="this.form.submit()" class="btn hasTooltip" title="<?php echo JText::_('COM_SEARCH_SEARCH');?>"><i class="icon-search"></i></button>
		</div>
		<input type="hidden" name="task" value="search" />
		<div class="clearfix"></div>
	</div>

	<div class="searchintro<?php echo $this->params->get('pageclass_sfx'); ?>">
		<?php if (!empty($this->searchword)):?>
		<p><?php echo JText::plural('COM_SEARCH_SEARCH_KEYWORD_N_RESULTS', '<span class="badge badge-info">'. $this->total. '</span>');?></p>
		<?php endif;?>
	</div>

	<fieldset class="phrases">
		<legend><?php echo JText::_('COM_SEARCH_FOR');?>
		</legend>
			<div class="phrases-box">
			<?php echo $this->lists['searchphrase']; ?>
			</div>
			<div class="ordering-box">
			<label for="ordering" class="ordering">
				<?php echo JText::_('COM_SEARCH_ORDERING');?>
			</label>
			<?php echo $this->lists['ordering'];?>
			</div>
	</fieldset>

	<?php if ($this->params->get('search_areas', 1)) : ?>
		<fieldset class="only">
		<legend><?php echo JText::_('COM_SEARCH_SEARCH_ONLY');?></legend>
		<?php foreach ($this->searchareas['search'] as $val => $txt) :
			$checked = is_array($this->searchareas['active']) && in_array($val, $this->searchareas['active']) ? 'checked="checked"' : '';
		?>
		<label for="area-<?php echo $val;?>" class="checkbox">
			<input type="checkbox" name="areas[]" value="<?php echo $val;?>" id="area-<?php echo $val;?>" <?php echo $checked;?> >
			<?php echo JText::_($txt); ?>
		</label>
		<?php endforeach; ?>
		</fieldset>
	<?php endif; ?>

<?php if ($this->total > 0) : ?>

	<div class="form-limit">
		<label for="limit">
			<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>
		</label>
		<?php echo $this->pagination->getLimitBox(); ?>
	</div>
<p class="counter">
		<?php echo $this->pagination->getPagesCounter(); ?>
	</p>

<?php endif; ?>

</form>
<?php else : ?>
<div class="com-search <?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
	<form id="searchForm" action="<?php echo JRoute::_( 'index.php?option=com_search' );?>" method="post" name="searchForm">
		<div class="contentpaneopen<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
			<p class="search-keyword">
				<label for="search_searchword"><?php echo JText::_('COM_SEARCH_SEARCH_KEYWORD'); ?></label>
				<input type="text" name="searchword" id="search_searchword" size="30" maxlength="<?php echo $upper_limit; ?>" value="<?php echo $this->escape($this->searchword); ?>" class="inputbox" />
				<button name="Search" onclick="this.form.submit()" class="button"><?php echo JText::_( 'COM_SEARCH_SEARCH' );?></button>
			</p>
			<p class="searchintro<?php echo $this->params->get('pageclass_sfx'); ?>"></p>
			<p class="search-phrase"><?php echo $this->lists['searchphrase']; ?></p>
			<p class="search-ordering">
				<label for="ordering"><?php echo JText::_( 'COM_SEARCH_ORDERING' );?></label>
				<?php echo $this->lists['ordering'];?> </p>
			<?php if ($this->params->get( 'search_areas', 1 )) : ?>
			<p class="search-range clearafter"> <span class="search-field-title"><?php echo JText::_( 'COM_SEARCH_SEARCH_ONLY' );?></span>
				<?php foreach ($this->searchareas['search'] as $val => $txt) :
						$checked = is_array( $this->searchareas['active'] ) && in_array( $val, $this->searchareas['active'] ) ? 'checked="checked"' : '';
					?>
				<label for="area_<?php echo $val;?>">
					<input type="checkbox" name="areas[]" value="<?php echo $val;?>" id="area_<?php echo $val;?>" <?php echo $checked;?> />
					<?php echo JText::_($txt); ?> </label>
				<?php endforeach; ?>
			</p>
			<?php if($this->total > 0) : ?>
			<div align="center" class="clearafter">
				<?php if (!empty($this->searchword)):?>
				<div style="float: left"><?php echo JText::plural('COM_SEARCH_SEARCH_KEYWORD_N_RESULTS', $this->total);?></div>
				<?php endif;?>
				<div style="float: right;">
					<label for="limit"> <?php echo JText::_( 'JGLOBAL_DISPLAY_NUM' ); ?> </label>
					<?php echo $this->pagination->getLimitBox( ); ?> </div>
				<div> <?php echo $this->pagination->getPagesCounter(); ?> </div>
			</div>
			<?php endif; ?>
			<?php endif; ?>
		</div>
		<input type="hidden" name="task"   value="search" />
	</form>
</div>
<?php endif; ?>
