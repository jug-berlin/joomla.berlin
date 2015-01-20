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
<form action="index.php" method="get" name="akeebaform" id="akeebaform">
	<input type="hidden" name="option" value="com_akeeba" />
	<input type="hidden" name="view" value="upload" />
	<input type="hidden" name="task" value="upload" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="id" value="<?php echo $this->id ?>" />
	<input type="hidden" name="part" value="0" />
	<input type="hidden" name="frag" value="0" />
</form>

<p class="well">
	<?php echo JText::_('AKEEBA_TRANSFER_MSG_START') ?>
</p>

<script type="text/javascript" language="javascript">
		document.forms.akeebaform.submit();
</script>