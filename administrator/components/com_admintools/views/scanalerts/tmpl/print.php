<?php
/**
 * @package   AkeebaReleaseSystem
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

defined('_JEXEC') or die;

JLoader::import('joomla.utilities.date');
JHTML::_('behavior.framework', true);

$scan_id = $this->input->getInt('scan_id', 0);
$subtitle_key = $this->input->getCmd('option', 'com_foobar') . '_TITLE_' . strtoupper($this->input->getCmd('view', 'cpanel'));
$jDate = new JDate($this->scan->backupstart);

$script = <<<JS

;// This comment is intentionally put here to prevent badly written plugins from causing a Javascript error
// due to missing trailing semicolon and/or newline in their code.
(function($){
	$(document).ready(function(){
		if (window.print) {
			window.print();
		}
	});
})(akeeba.jQuery);

JS;
JFactory::getDocument()->addScriptDeclaration($script);
?>
<h1>
	<?php echo JText::sprintf($subtitle_key, $scan_id) ?>
</h1>
<h2>
	<?php echo $jDate->format(JText::_('DATE_FORMAT_LC2'), true) ?>
</h2>

<table class="adminlist">
	<thead>
	<tr>
		<th width="20"></th>
		<th>
			<?php echo JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_PATH') ?>
		</th>
		<th width="80">
			<?php echo JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS') ?>
		</th>
		<th width="40">
			<?php echo JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_THREAT_SCORE') ?>
		</th>
		<th width="40">
			<?php echo JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_ACKNOWLEDGED') ?>
		</th>
	</tr>
	</thead>
	<tbody>
	<?php if ($count = count($this->items)): ?>
		<?php
		$i = 0;
		$m = 1;
		foreach ($this->items as $item):
			if ($item->threat_score == 0)
			{
				$threatindex = 'none';
			}
			elseif ($item->threat_score < 10)
			{
				$threatindex = 'low';
			}
			elseif ($item->threat_score < 100)
			{
				$threatindex = 'medium';
			}
			else
			{
				$threatindex = 'high';
			}

			if ($item->newfile)
			{
				$fstatus = 'new';
			}
			elseif ($item->suspicious)
			{
				$fstatus = 'suspicious';
			}
			else
			{
				$fstatus = 'modified';
			}

			if (strlen($item->path) > 100)
			{
				$truncatedPath = true;
				$path = $this->escape(substr($item->path, -100));
				$alt = 'title="' . $this->escape($item->path) . '"';
			}
			else
			{
				$truncatedPath = false;
				$path = $this->escape($item->path);
				$alt = '';
			}
			?>
			<tr class="row<?php $m = 1 - $m;
			echo $m; ?>">
				<td><?php echo $i + 1 ?></td>
				<td>
					<?php echo $truncatedPath ? "&hellip;" : ''; ?>
					<a href="index.php?option=com_admintools&view=scanalert&id=<?php echo $item->admintools_scanalert_id ?>" <?php echo $alt ?>>
						<?php echo $path ?>
					</a>
				</td>
				<td class="admintools-scanfile-<?php echo $fstatus ?> <?php if (!$item->threat_score): ?>admintools-scanfile-nothreat<?php endif ?>">
					<?php echo JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS_' . $fstatus) ?>
				</td>
				<td class="admintools-scanfile-threat-<?php echo $threatindex ?>">
					<?php echo $item->threat_score ?>
				</td>
				<td>
					<?php if ($item->acknowledged): ?>
						<span class="admintools-scanfile-markedsafe">
				<?php echo JText::_('JYES') ?>
				</span>
					<?php else: ?>
						<?php echo JText::_('JNO') ?>
					<?php endif; ?>
				</td>
			</tr>
			<?php
			$i++;
		endforeach;
		?>
	<?php else: ?>
		<tr>
			<td colspan="20" align="center"><?php echo JText::_('COM_ADMINTOOLS_MSG_COMMON_NOITEMS') ?></td>
		</tr>
	<?php endif; ?>
	</tbody>
</table>