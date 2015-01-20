<?php
/**
 * @package   AkeebaReleaseSystem
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

defined('_JEXEC') or die;

JHTML::_('behavior.framework', true);
JLoader::import('joomla.utilities.date');

$this->item->newfile = empty($this->item->diff);
$this->item->suspicious = substr($this->item->diff, 0, 21) == '###SUSPICIOUS FILE###';

if ($this->item->threat_score == 0)
{
	$threatindex = 'none';
}
elseif ($this->item->threat_score < 10)
{
	$threatindex = 'low';
}
elseif ($this->item->threat_score < 100)
{
	$threatindex = 'medium';
}
else
{
	$threatindex = 'high';
}

if ($this->item->newfile)
{
	$fstatus = 'new';
}
elseif ($this->item->suspicious)
{
	$fstatus = 'suspicious';
}
else
{
	$fstatus = 'modified';
}

// Get the current file data
$filedata = @file_get_contents(JPATH_ROOT . '/' . $this->item->path);

// Various info
$suspiciousFile = false;

// Should I render a diff?
if (!empty($this->item->diff))
{
	$diffLines = explode("\n", $this->item->diff);
	$firstLine = array_shift($diffLines);
	if ($firstLine == '###SUSPICIOUS FILE###')
	{
		$suspiciousFile = true;
		$this->item->diff = '';
	}
	elseif ($firstLine == '###MODIFIED FILE###')
	{
		$this->item->diff = '';
	}
	if ($suspiciousFile && (count($diffLines) > 4))
	{
		array_shift($diffLines);
		array_shift($diffLines);
		array_shift($diffLines);
		array_shift($diffLines);
		$this->item->diff = implode("\n", $diffLines);
	}
	unset($diffLines);
}

$scan = F0FModel::getTmpInstance('Scans', 'AdmintoolsModel')
	->scan_id($this->item->scan_id)
	->getFirstItem();
$scanDate = new JDate($scan->backupstart);

$subtitle = JText::sprintf('COM_ADMINTOOLS_TITLE_SCANALERT_EDIT', $this->item->scan_id);
JToolBarHelper::title(JText::_('COM_ADMINTOOLS') . ' &ndash; <small>' . $subtitle . '</small>', 'admintools');

?>
<form name="adminForm" id="adminForm" action="index.php" method="post" class="form form-horizontal">
	<input type="hidden" name="option" value="com_admintools"/>
	<input type="hidden" name="view" value="scanalert"/>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="admintools_scanalert_id" value="<?php echo $this->item->admintools_scanalert_id ?>"/>
	<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken(); ?>" value="1"/>

	<fieldset>
		<legend><?php echo JText::_('COM_ADMINTOOLS_LBL_SCANALERT_FILEINFO'); ?></legend>

		<table class="table table-striped">
			<tr>
				<td>
					<?php echo JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_PATH'); ?>
				</td>
				<td>
					<?php echo $this->item->path ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('COM_ADMINTOOLS_LBL_SCANALERT_SCANDATE'); ?>
				</td>
				<td>
					<?php echo $scanDate->format(JText::_('DATE_FORMAT_LC2'), true) ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS'); ?>
				</td>
				<td>
					<span
						class="admintools-scanfile-<?php echo $fstatus ?> <?php if (!$this->item->threat_score): ?>admintools-scanfile-nothreat<?php endif ?>">
						<?php echo JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS_' . $fstatus) ?>
					</span>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_THREAT_SCORE'); ?>
				</td>
				<td>
					<span class="admintools-scanfile-threat-<?php echo $threatindex ?>">
						<?php echo $this->item->threat_score ?>
					</span>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_ACKNOWLEDGED'); ?>
				</td>
				<td class="decrapbooleanlist">
					<?php echo JHTML::_('select.booleanlist', 'acknowledged', null, $this->item->acknowledged); ?>
				</td>
			</tr>

		</table>

	</fieldset>

	<?php if ($this->generateDiff && ($fstatus == 'modified')):
		if (version_compare(JVERSION, '3.0', 'lt'))
		{
			echo JHtml::_('tabs.start', 'ScanAlertPanes');
			echo JHtml::_('tabs.panel', JText::_('COM_ADMINTOOLS_LBL_SCANALERT_DIFF'), 'diff');
		}
		else
		{
			echo JHtml::_('sliders.start', 'ScanAlertPanes');
			echo JHtml::_('sliders.panel', JText::_('COM_ADMINTOOLS_LBL_SCANALERT_DIFF'), 'diff');
		}
		?>

		<?php if (class_exists('GeSHi') && (strlen($this->item->diff) < 60000)): ?>
		<?php
		$geshi = new GeSHi($this->item->diff, $suspiciousFile ? 'php' : 'diff');
		$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
		echo $geshi->parse_code();
		?>
	<?php else: ?>
		<pre class="<?php echo $suspiciousFile ? 'php' : 'diff' ?>">
		<?php echo $this->item->diff ?>
	</pre>
	<?php endif; ?>

		<?php
		if (version_compare(JVERSION, '3.0', 'lt'))
		{
			echo JHtml::_('tabs.panel', JText::_('COM_ADMINTOOLS_LBL_SCANALERT_SOURCE'), 'source');
		}
		else
		{
			echo JHtml::_('sliders.panel', JText::_('COM_ADMINTOOLS_LBL_SCANALERT_SOURCE'), 'source');
		}
	else: ?>
	<fieldset>
		<legend><?php echo JText::_('COM_ADMINTOOLS_LBL_SCANALERT_SOURCE') ?></legend>
		<?php endif; ?>

		<div class="control-group">
			<label class="control-label"><?php echo JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_MD5'); ?></label>

			<div class="controls">
				<span class="help-block"><?php echo @md5_file(JPATH_SITE . '/' . $this->item->path) ?></span>
			</div>
		</div>
		<div style="clear:left"></div>

		<?php if (class_exists('GeSHi') && (strlen($filedata) < 60000)): ?>
			<?php
			$geshi = new GeSHi($filedata, 'php');
			$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
			echo $geshi->parse_code();
			?>
		<?php else: ?>
			<pre class="php">
		<?php echo $filedata ?>
	</pre>
		<?php endif; ?>

		<?php if ($this->generateDiff && ($fstatus == 'modified')):
			if (version_compare(JVERSION, '3.0', 'lt'))
			{
				echo JHtml::_('tabs.end');
			}
			else
			{
				echo JHtml::_('sliders.end');
			}
			?>
		<?php else: ?>
	</fieldset>
<?php endif; ?>
</form>