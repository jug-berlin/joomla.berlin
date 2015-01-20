<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 *
 * @since 3.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

JHtml::_('behavior.framework');
JHtml::_('behavior.modal');

?>
<div class="alert alert-success">
<?php echo JText::_('AKEEBA_TRANSFER_MSG_DONE');?>
</div>

<script type="text/javascript" language="javascript">
	window.setTimeout('closeme();', 3000);
	function closeme()
	{
		parent.SqueezeBox.close();
	}
</script>