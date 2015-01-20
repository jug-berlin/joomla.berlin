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
?>
<div class="alert alert-error">
	<h4><?php echo JText::_('AKEEBA_TRANSFER_MSG_FAILED')?></h4>
	<p>
		<?php echo $this->errorMessage; ?>
	</p>
</div>