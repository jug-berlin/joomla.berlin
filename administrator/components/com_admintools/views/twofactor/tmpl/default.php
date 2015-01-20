<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

JHTML::_('behavior.framework', true);

$this->loadHelper('select');

?>
<div class="alert">
	<h3><?php echo JText::_('COM_ADMINTOOLS_COMMON_DEPRECATED_TITLE');?></h3>
	<p><?php echo JText::_('COM_ADMINTOOLS_COMMON_DEPRECATED_BODY');?></p>
</div>

<?php if (!$this->supported): ?>
	<div class="alert alert-error">
		<?php echo JText::_('COM_ADMINTOOLS_TWOFACTOR_STATUS_FAIL') ?>
	</div>
<?php else: ?>

	<div class="well">
		<p>
			<?php if ($this->enabled): ?>
				<span class="label label-success">
				<?php echo JText::_('COM_ADMINTOOLS_TWOFACTOR_STATUS_ENABLED'); ?>
			</span>
			<?php else: ?>
				<span class="label label-important">
				<?php echo JText::_('COM_ADMINTOOLS_TWOFACTOR_STATUS_DISABLED'); ?>
			</span>
			<?php endif; ?>
		</p>

		<p>
			<?php echo JText::_('COM_ADMINTOOLS_TWOFACTOR_ABOUT_LABEL') ?>
		</p>

		<p>
			<?php echo JText::_('COM_ADMINTOOLS_TWOFACTOR_ABOUT_WARNING') ?>
		</p>
	</div>

	<fieldset>
		<legend><?php echo JText::_('COM_ADMINTOOLS_TWOFACTOR_STEP1_HEADER') ?></legend>
		<p>
			<?php echo JText::_('COM_ADMINTOOLS_TWOFACTOR_STEP1_TEXT') ?>
		</p>
		<ul>
			<li>
				<a href="http://support.google.com/accounts/bin/answer.py?hl=en&answer=1066447" target="_blank">
					<?php echo JText::_('COM_ADMINTOOLS_TWOFACTOR_STEP1_TEXT_DLOFFICIAL') ?>
				</a>
			</li>
			<li>
				<a href="http://en.wikipedia.org/wiki/Google_Authenticator#Implementation" target="_blank">
					<?php echo JText::_('COM_ADMINTOOLS_TWOFACTOR_STEP1_TEXT_DLALT') ?>
				</a>
			</li>
		</ul>
		<div class="alert">
			<?php echo JText::_('COM_ADMINTOOLS_TWOFACTOR_STEP1_TIMEWARNING'); ?>
		</div>
	</fieldset>

	<fieldset>
		<legend><?php echo JText::_('COM_ADMINTOOLS_TWOFACTOR_STEP2_HEADER') ?></legend>
		<p>
			<?php echo JText::_('COM_ADMINTOOLS_TWOFACTOR_STEP2_TEXT'); ?>
		</p>
		<table class="table table-striped">
			<tr>
				<td>
					<?php echo JText::_('COM_ADMINTOOLS_TWOFACTOR_STEP2_TEXT_ACCOUNT'); ?>
				</td>
				<td>
					<?php echo $this->user ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('COM_ADMINTOOLS_TWOFACTOR_STEP2_TEXT_SECRET'); ?>
				</td>
				<td>
					<?php echo $this->secret ?>
				</td>
			</tr>
		</table>
		<p>
			<?php echo JText::_('COM_ADMINTOOLS_TWOFACTOR_STEP2_TEXT_QRCODE'); ?>
			<br/>
			<img src="<?php echo $this->qrcodeurl ?>" style="float: none"/>
		</p>

		<p>
			<?php echo JText::_('COM_ADMINTOOLS_TWOFACTOR_STEP2_TEXT_RESET'); ?>
			<br/>
			<a class="btn btn-danger"
			   href="index.php?option=com_admintools&view=twofactor&task=resetkey&<?php echo JFactory::getSession()->getFormToken(); ?>=1">
				<?php echo JText::_('COM_ADMINTOOLS_TWOFACTOR_STEP2_BTNRESET'); ?>
			</a>
		</p>
	</fieldset>

	<fieldset>
		<legend><?php echo JText::_('COM_ADMINTOOLS_TWOFACTOR_STEP3_HEADER') ?></legend>
		<p>
			<?php echo JText::_('COM_ADMINTOOLS_TWOFACTOR_STEP3_TEXT'); ?>
		</p>
		<pre>
<?php echo $this->panic ?>
		</pre>
		<a class="btn btn-danger"
		   href="index.php?option=com_admintools&view=twofactor&task=resetpanic&<?php echo JFactory::getSession()->getFormToken(); ?>=1">
			<?php echo JText::_('COM_ADMINTOOLS_TWOFACTOR_STEP3_RESETBTN'); ?>
		</a>
	</fieldset>

	<fieldset>
		<legend><?php echo JText::_('COM_ADMINTOOLS_TWOFACTOR_STEP4_HEADER') ?></legend>

		<p>
			<?php echo JText::_('COM_ADMINTOOLS_TWOFACTOR_STEP4_TEXT') ?>
		</p>

		<form name="adminForm" id="adminForm" action="index.php" method="post" class="form form-horizontal">
			<input type="hidden" name="option" value="com_admintools"/>
			<input type="hidden" name="view" value="twofactor"/>
			<input type="hidden" name="task" value="validate"/>
			<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken(); ?>" value="1"/>

			<div class="control-group">
				<label class="control-label" for="securitycode">
					<?php echo JText::_('COM_ADMINTOOLS_LOGIN_TWOFACTOR_LABEL') ?>
				</label>

				<div class="controls">
					<input type="text" class="input-small" name="securitycode" id="securitycode" autocomplete="0"
						   autofocus="autofocus"/>
				</div>
			</div>

			<div class="form-actions">
				<button class="btn btn-primary">
					<?php echo JText::_('COM_ADMINTOOLS_TWOFACTOR_STEP4_VALIDATEBTN') ?>
				</button>
			</div>
		</form>

	</fieldset>

<?php endif; ?>