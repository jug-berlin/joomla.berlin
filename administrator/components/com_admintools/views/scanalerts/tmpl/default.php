<?php
/**
 * @package   AkeebaReleaseSystem
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

defined('_JEXEC') or die;


JLoader::import('joomla.utilities.date');

if (version_compare(JVERSION, '3.0', 'ge'))
{
	JHTML::_('behavior.framework');
	JHtml::_('bootstrap.tooltip');
	JHtml::_('dropdown.init');
	JHtml::_('formbehavior.chosen', 'select');
}
else
{
	JHTML::_('behavior.mootools');
}

$this->loadHelper('select');
?>

<script type="text/javascript">
	function exportCSV()
	{
		document.getElementById('format').value = 'csv';
		document.forms.adminForm.submit();
		// This looks stupid, but it is REQUIRED
		document.getElementById('format').value = 'html';
	}

	function printReport()
	{
		document.getElementById('layout').value = 'print';
		document.getElementById('tmpl').value = 'component';
		document.forms.adminForm.submit();
	}
</script>

<form name="adminForm" id="adminForm" action="index.php" method="post">
	<input type="hidden" name="option" id="option" value="com_admintools"/>
	<input type="hidden" name="view" id="view" value="scanalerts"/>
	<input type="hidden" name="task" id="task" value="browse"/>
	<input type="hidden" name="boxchecked" id="boxchecked" value="0"/>
	<input type="hidden" name="hidemainmenu" id="hidemainmenu" value="0"/>
	<input type="hidden" name="filter_order" id="filter_order" value="<?php echo $this->lists->order ?>"/>
	<input type="hidden" name="filter_order_Dir" id="filter_order_Dir" value="<?php echo $this->lists->order_Dir ?>"/>
	<input type="hidden" name="scan_id" id="scan_id" value="<?php echo $this->getModel()->getState('scan_id', ''); ?>"/>
	<input type="hidden" name="format" id="format" value="html"/>
	<input type="hidden" name="layout" id="layout" value="default"/>
	<input type="hidden" name="tmpl" id="tmpl" value="index"/>
	<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken(); ?>" value="1"/>

	<?php if (version_compare(JVERSION, '3.2.0', 'ge')): ?>
		<div id="filter-bar" class="btn-toolbar">
			<div class="btn-group pull-right hidden-phone">
				<label for="limit"
					   class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC') ?></label>
				<?php echo $this->getModel()->getPagination()->getLimitBox(); ?>
			</div>
		</div>
	<?php endif; ?>

	<table class="table table-striped">
		<thead>
		<tr>
			<th width="20" rowspan="2">
				<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);"/>
			</th>
			<th>
				<?php echo JHTML::_('grid.sort', 'COM_ADMINTOOLS_LBL_SCANALERTS_PATH', 'path', $this->lists->order_Dir, $this->lists->order, 'browse'); ?>
			</th>
			<th width="80">
				<?php echo JHTML::_('grid.sort', 'COM_ADMINTOOLS_LBL_SCANALERTS_STATUS', 'filestatus', $this->lists->order_Dir, $this->lists->order, 'browse'); ?>
			</th>
			<th width="80">
				<?php echo JHTML::_('grid.sort', 'COM_ADMINTOOLS_LBL_SCANALERTS_THREAT_SCORE', 'threat_score', $this->lists->order_Dir, $this->lists->order, 'browse'); ?>
			</th>
			<th width="40">
				<?php echo JHTML::_('grid.sort', 'COM_ADMINTOOLS_LBL_SCANALERTS_ACKNOWLEDGED', 'acknowledged', $this->lists->order_Dir, $this->lists->order, 'browse'); ?>
			</th>
		</tr>
		<tr>
			<td class="form-inline">
				<input type="text" name="search" id="search"
					   value="<?php echo $this->escape($this->getModel()->getState('search', '')); ?>"
					   class="input-large" onchange="document.adminForm.submit();"
					   placeholder="<?php echo JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_PATH') ?>"/>
				<button class="btn btn-mini" onclick="this.form.submit();">
					<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>
				</button>
				<button class="btn btn-mini" onclick="document.adminForm.search.value='';this.form.submit();">
					<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
				</button>
			</td>
			<td>
				<?php echo AdmintoolsHelperSelect::scanresultstatus('status', array('onchange' => 'this.form.submit();', 'class' => 'input-medium'), $this->getModel()->getState('status', '')); ?>
			</td>
			<td></td>
			<td>
				<?php echo AdmintoolsHelperSelect::booleanlist('safe', array('onchange' => 'this.form.submit();', 'class' => 'input-small'), $this->getModel()->getState('safe', '')); ?>
			</td>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<td colspan="20">
				<?php if ($this->pagination->total > 0)
				{
					echo $this->pagination->getListFooter();
				}
				?>
			</td>
		</tr>
		</tfoot>
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
					<td>
						<?php echo JHTML::_('grid.id', $i, $item->admintools_scanalert_id, false); ?>
					</td>
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
						<span class="admintools-scanfile-pic">&nbsp;</span>
						<?php echo $item->threat_score ?>
					</td>
					<td>
						<?php echo JHtml::_('grid.published', $item->acknowledged, $i, 'tick.png', 'publish_x.png'); ?>
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
</form>