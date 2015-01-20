<?php
	defined('_JEXEC') or die('Restricted access'); 
	JHtml::_('behavior.keepalive');
	$app 		= JFactory::getApplication();
	$template 	= $app->getTemplate();
	require_once JPATH_THEMES.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'jsn_utils.php';
	$jsnUtils   = JSNUtils::getInstance();
?>
<?php if ($jsnUtils->isJoomla3()): ?>
<div class="login <?php echo $this->pageclass_sfx?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
	<div class="page-header">
		<h1>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h1>
	</div>
	<?php endif; ?>

	<?php if (($this->params->get('logindescription_show') == 1 && str_replace(' ', '', $this->params->get('login_description')) != '') || $this->params->get('login_image') != '') : ?>
	<div class="login-description">
	<?php endif; ?>

		<?php if($this->params->get('logindescription_show') == 1) : ?>
			<?php echo $this->params->get('login_description'); ?>
		<?php endif; ?>

		<?php if (($this->params->get('login_image') != '')) :?>
			<img src="<?php echo $this->escape($this->params->get('login_image')); ?>" class="login-image" alt="<?php echo JTEXT::_('COM_USER_LOGIN_IMAGE_ALT')?>"/>
		<?php endif; ?>

	<?php if (($this->params->get('logindescription_show') == 1 && str_replace(' ', '', $this->params->get('login_description')) != '') || $this->params->get('login_image') != '') : ?>
	</div>
	<?php endif; ?>

	<form action="<?php echo JRoute::_('index.php?option=com_users&task=user.login'); ?>" method="post" class="form-horizontal">

		<fieldset class="well">
			<?php foreach ($this->form->getFieldset('credentials') as $field): ?>
				<?php if (!$field->hidden): ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $field->label; ?>
						</div>
						<div class="controls">
							<?php echo $field->input; ?>
						</div>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
			<div class="control-group">
				<div class="controls">
					<button type="submit" class="btn btn-primary"><?php echo JText::_('JLOGIN'); ?></button>
				</div>
			</div>
			<input type="hidden" name="return" value="<?php echo base64_encode($this->params->get('login_redirect_url', $this->form->getValue('return'))); ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</fieldset>
	</form>
</div>
<?php else : ?>

	<?php if ( $this->params->get( 'show_page_heading' ) ) : ?>
		<h2> <?php echo $this->params->get( 'show_page_heading' ); ?> </h2>
	<?php endif; ?>
	
	<?php if ($this->params->get('logindescription_show') == 1 || $this->params->get('login_image') != '') : ?>
	<div class="contentdescription clearafter">
	<?php endif ; ?>
		<?php if (($this->params->get('login_image')!='')) :?>
			<img src="<?php echo $this->escape($this->params->get('login_image')); ?>" class="login-image" alt="<?php echo JTEXT::_('COM_USER_LOGIN_IMAGE_ALT')?>"/>
		<?php endif; ?>
		<?php if($this->params->get('logindescription_show') == 1) : ?>
			<?php echo $this->params->get('login_description'); ?>
		<?php endif; ?>
	<?php if ($this->params->get('logindescription_show') == 1 || $this->params->get('login_image') != '') : ?>
	</div>
	<?php endif ; ?>

	<form action="<?php echo JRoute::_('index.php?option=com_users&task=user.login'); ?>" method="post" name="com-login" id="com-form-login">
		<?php foreach ($this->form->getFieldset('credentials') as $field): ?>
				<?php if (!$field->hidden): ?>
					<div class="login-fields"><?php echo $field->label; ?>
					<?php echo $field->input; ?></div>
				<?php endif; ?>
			<?php endforeach; ?>
			<button type="submit" class="button"><?php echo JText::_('JLOGIN'); ?></button>
			<input type="hidden" name="return" value="<?php echo base64_encode($this->params->get('login_redirect_url',$this->form->getValue('return'))); ?>" />
			<?php echo JHtml::_('form.token'); ?>
	</form>
<?php endif; ?>
	<div>
	<ul <?php if ($jsnUtils->isJoomla3()){echo 'class="nav nav-tabs nav-stacked"';} ?>>
		<li>
			<a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>">
			<?php echo JText::_('COM_USERS_LOGIN_RESET'); ?></a>
		</li>
		<li>
			<a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>">
			<?php echo JText::_('COM_USERS_LOGIN_REMIND'); ?></a>
		</li>
		<?php
		$usersConfig = JComponentHelper::getParams('com_users');
		if ($usersConfig->get('allowUserRegistration')) : ?>
		<li>
			<a href="<?php echo JRoute::_('index.php?option=com_users&view=registration'); ?>">
				<?php echo JText::_('COM_USERS_LOGIN_REGISTER'); ?></a>
		</li>
		<?php endif; ?>
	</ul>
	</div>
	<div class="clearbreak"></div>