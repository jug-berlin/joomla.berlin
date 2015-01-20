<?php
/**
 * @package   AkeebaReleaseSystem
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

defined('_JEXEC') or die;

$ip = htmlspecialchars($_SERVER['REMOTE_ADDR']);
if ((strpos($ip, '::') === 0) && (strstr($ip, '.') !== false))
{
	$ip = substr($ip, strrpos($ip, ':') + 1);
}

?>
<form name="adminForm" id="adminForm" action="index.php" method="post" class="form form-horizontal">
	<input type="hidden" name="option" value="com_admintools"/>
	<input type="hidden" name="view" value="ipbls"/>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="id" value="<?php echo $this->item->id ?>"/>
	<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken(); ?>" value="1"/>

	<div class="alert alert-info">
		<a class="close" data-dismiss="alert" href="#">×</a>

		<p><?php echo JText::_('ATOOLS_LBL_IPBL_IP_INTRO') ?></p>
		<ol>
			<li><?php echo JText::_('ATOOLS_LBL_IPBL_IP_OPT1') ?></li>
			<li><?php echo JText::_('ATOOLS_LBL_IPBL_IP_OPT2') ?></li>
			<li><?php echo JText::_('ATOOLS_LBL_IPBL_IP_OPT3') ?></li>
			<li><?php echo JText::_('ATOOLS_LBL_IPBL_IP_OPT4') ?></li>
		</ol>

		<p>
			<?php echo JText::_('ATOOLS_LBL_IPBL_YOURIP') ?>
			<code><?php echo $ip ?></code>
		</p>

	</div>

	<fieldset>
		<div class="control-group">
			<label for="ip" class="control-label"><?php echo JText::_('ATOOLS_LBL_IPBL_IP'); ?></label>

			<div class="controls">
				<input type="text" name="ip" id="ip" value="<?php echo $this->item->ip ?>">
			</div>
		</div>
		<div class="control-group">
			<label for="description"
				   class="control-label"><?php echo JText::_('ATOOLS_LBL_IPBL_DESCRIPTION'); ?></label>

			<div class="controls">
				<input type="text" name="description" id="description" value="<?php echo $this->item->description ?>">
			</div>
		</div>
	</fieldset>
</form>