<?php
/**
 * @package   AkeebaReleaseSystem
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

defined('_JEXEC') or die;

AkeebaStrapper::addJSfile('media://com_admintools/js/scan.js?' . ADMINTOOLS_VERSION);

JLoader::import('joomla.utilities.date');
JHTML::_('behavior.framework', true);
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
if (version_compare(JVERSION, '3.0', 'gt'))
{
	JHtml::_('dropdown.init');
	JHtml::_('formbehavior.chosen', 'select');
}

$sortFields = array(
	'id'               => JText::_('COM_ADMINTOOLS_LBL_SCANS_ID'),
	'backupstart'      => JText::_('COM_ADMINTOOLS_LBL_SCANS_START'),
	'files_modified'   => JText::_('COM_ADMINTOOLS_LBL_SCANS_MODIFIED'),
	'files_suspicious' => JText::_('COM_ADMINTOOLS_LBL_SCANS_THREATNONZERO'),
	'files_new'        => JText::_('COM_ADMINTOOLS_LBL_SCANS_ADDED'),
);

?>
	<div class="form-inline">
		<a class="btn btn-primary" href="index.php?option=com_admintools&view=scanner">
			<i class="icon icon-white icon-cog"></i>
			<?php echo JText::_('COM_ADMINTOOLS_LBL_SCANS_CONFIGURE'); ?>
		</a>
	<span class="help-inline">
		<i class="icon-info-sign"></i>
		<?php echo JText::_('COM_ADMINTOOLS_MSG_SCANS_CONFIGUREHELP'); ?>
	</span>
	</div>
	<hr/>

<?php if (version_compare(JVERSION, '3.0', 'ge')): ?>
	<script type="text/javascript">
		Joomla.orderTable = function ()
		{
			table = document.getElementById("sortTable");
			direction = document.getElementById("directionTable");
			order = table.options[table.selectedIndex].value;
			if (order != '$order')
			{
				dirn = 'asc';
			}
			else
			{
				dirn = direction.options[direction.selectedIndex].value;
			}
			Joomla.tableOrdering(order, dirn);
		}
	</script>
<?php endif; ?>

	<form name="adminForm" id="adminForm" action="index.php" method="post">
		<input type="hidden" name="option" id="option" value="com_admintools"/>
		<input type="hidden" name="view" id="view" value="scans"/>
		<input type="hidden" name="task" id="task" value="browse"/>
		<input type="hidden" name="boxchecked" id="boxchecked" value="0"/>
		<input type="hidden" name="hidemainmenu" id="hidemainmenu" value="0"/>
		<input type="hidden" name="filter_order" id="filter_order" value="<?php echo $this->lists->order ?>"/>
		<input type="hidden" name="filter_order_Dir" id="filter_order_Dir"
			   value="<?php echo $this->lists->order_Dir ?>"/>
		<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken(); ?>" value="1"/>

		<?php if (version_compare(JVERSION, '3.0', 'gt')): ?>
			<div id="filter-bar" class="btn-toolbar">
				<div class="btn-group pull-right hidden-phone">
					<label for="limit"
						   class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC') ?></label>
					<?php echo $this->getModel()->getPagination()->getLimitBox(); ?>
				</div>
				<?php
				$asc_sel = ($this->getLists()->order_Dir == 'asc') ? 'selected="selected"' : '';
				$desc_sel = ($this->getLists()->order_Dir == 'desc') ? 'selected="selected"' : '';
				?>
				<div class="btn-group pull-right hidden-phone">
					<label for="directionTable"
						   class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC') ?></label>
					<select name="directionTable" id="directionTable" class="input-medium"
							onchange="Joomla.orderTable()">
						<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC') ?></option>
						<option
							value="asc" <?php echo $asc_sel ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING') ?></option>
						<option
							value="desc" <?php echo $desc_sel ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING') ?></option>
					</select>
				</div>
				<div class="btn-group pull-right">
					<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY') ?></label>
					<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
						<option value=""><?php echo JText::_('JGLOBAL_SORT_BY') ?></option>
						<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $this->getLists()->order) ?>
					</select>
				</div>
			</div>
			<div class="clearfix"></div>
		<?php endif; ?>

		<table class="table striped">
			<thead>
			<tr>
				<th>
					<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);"/>
				</th>
				<th>
					<?php echo JHTML::_('grid.sort', 'COM_ADMINTOOLS_LBL_SCANS_ID', 'id', $this->lists->order_Dir, $this->lists->order, 'browse'); ?>
				</th>
				<th>
					<?php echo JHTML::_('grid.sort', 'COM_ADMINTOOLS_LBL_SCANS_START', 'backupstart', $this->lists->order_Dir, $this->lists->order, 'browse'); ?>
				</th>
				<th width="80">
					<?php echo JText::_('COM_ADMINTOOLS_LBL_SCANS_TOTAL'); ?>
				</th>
				<th width="80">
					<?php echo JHTML::_('grid.sort', 'COM_ADMINTOOLS_LBL_SCANS_MODIFIED', 'files_modified', $this->lists->order_Dir, $this->lists->order, 'browse'); ?>
				</th>
				<th width="80">
					<?php echo JHTML::_('grid.sort', 'COM_ADMINTOOLS_LBL_SCANS_THREATNONZERO', 'files_suspicious', $this->lists->order_Dir, $this->lists->order, 'browse'); ?>
				</th>
				<th width="80">
					<?php echo JHTML::_('grid.sort', 'COM_ADMINTOOLS_LBL_SCANS_ADDED', 'files_new', $this->lists->order_Dir, $this->lists->order, 'browse'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_ADMINTOOLS_LBL_SCANS_ACTIONS'); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="20">
					<?php if ($this->pagination->total > 0): ?>
					<?php echo $this->pagination->getListFooter(); ?>
					<?php endif; ?>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php if ($count = count($this->items)): ?>
				<?php
				$i = 0;
				$m = 1;
				foreach ($this->items as $item):
					if (!isset($item->files_suspicious))
					{
						$item->files_suspicious = 0;
					}
					//$item->files_modified -= $item->files_suspicious;
					?>
					<tr class="row<?php $m = 1 - $m;
					echo $m; ?>">
						<td>
							<?php echo JHTML::_('grid.id', $i, $item->id, false); ?>
						</td>
						<td>
							<?php echo $item->id ?>
						</td>
						<td>
							<?php
							$jDate = new JDate($item->backupstart);
							echo $jDate->format('Y-m-d H:i:s', true);
							?>
						</td>
						<td>
							<?php echo $item->multipart ?>
						</td>
						<td class="admintools-files-<?php echo $item->files_modified ? 'alert' : 'noalert' ?>">
							<?php echo $item->files_modified ?>
						</td>
						<td class="admintools-files-<?php echo $item->files_suspicious ? 'alert' : 'noalert' ?>">
							<?php echo $item->files_suspicious ?>
						</td>
						<td class="admintools-files-<?php echo $item->files_new ? 'alert' : 'noalert' ?>">
							<?php echo $item->files_new ?>
						</td>
						<td align="center">
							<?php if ($item->files_modified + $item->files_new + $item->files_suspicious): ?>
								<a class="btn btn-mini"
								   href="index.php?option=com_admintools&view=scanalerts&scan_id=<?php echo $item->id ?>">
									<?php echo JText::_('COM_ADMINTOOLS_LBL_SCANS_ACTIONS_VIEW') ?>
								</a>
							<?php else: ?>
								<?php echo JText::_('COM_ADMINTOOLS_LBL_SCANS_ACTIONS_NOREPORT') ?>
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
	</form>

	<div id="admintools-scan-dim" style="display: none">
		<div id="admintools-scan-container">
			<p>
				<?php echo JText::_('COM_ADMINTOOLS_MSG_SCANS_PLEASEWAIT') ?><br/>
				<?php echo JText::_('COM_ADMINTOOLS_MSG_SCANS_SCANINPROGRESS') ?>
			</p>

			<p>
				<progress></progress>
			</p>
			<p>
				<span id="admintools-lastupdate-text" class="lastupdate"></span>
			</p>
		</div>
	</div>

<?php
$msg = JText::_('COM_ADMINTOOLS_MSG_SCANS_LASTSERVERRESPONSE');
$urlStart = 'index.php?option=com_admintools&view=scan&task=startscan';
$urlStep = 'index.php?option=com_admintools&view=scan&task=stepscan';
$script = <<<ENDSCRIPT

;// This comment is intentionally put here to prevent badly written plugins from causing a Javascript error
// due to missing trailing semicolon and/or newline in their code.
admintools_scan_msg_ago = '$msg';
admintools_scan_ajax_url_start='$urlStart';
admintools_scan_ajax_url_step='$urlStep';

ENDSCRIPT;
JFactory::getDocument()->addScriptDeclaration($script);
?>