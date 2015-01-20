<?php
/**
 * @package   AkeebaReleaseSystem
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

defined('_JEXEC') or die;

$model = $this->getModel();
JHTML::_('behavior.calendar');
JLoader::import('joomla.utilities.date');

if (interface_exists('JModel'))
{
	$cparams = JModelLegacy::getInstance('Storage', 'AdmintoolsModel');
}
else
{
	$cparams = JModel::getInstance('Storage', 'AdmintoolsModel');
}
$iplink = $cparams->getValue('iplookupscheme', 'http') . '://' . $cparams->getValue('iplookup', 'ip-lookup.net/index.php?ip={ip}');

$this->loadHelper('select');

JHTML::_('behavior.framework', true);
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
if (version_compare(JVERSION, '3.0', 'gt'))
{
	JHtml::_('dropdown.init');
	JHtml::_('formbehavior.chosen', 'select');
}

$sortFields = array(
	'id'      => JText::_('JGRID_HEADING_ID'),
	'logdate' => JText::_('ATOOLS_LBL_LOG_LOGDATE'),
	'ip'      => JText::_('ATOOLS_LBL_LOG_IP'),
	'reason'  => JText::_('ATOOLS_LBL_LOG_REASON'),
	'url'     => JText::_('ATOOLS_LBL_LOG_URL'),
);

?>
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
	<input type="hidden" name="view" id="view" value="logs"/>
	<input type="hidden" name="task" id="task" value="browse"/>
	<input type="hidden" name="boxchecked" id="boxchecked" value="0"/>
	<input type="hidden" name="hidemainmenu" id="hidemainmenu" value="0"/>
	<input type="hidden" name="filter_order" id="filter_order" value="<?php echo $this->lists->order ?>"/>
	<input type="hidden" name="filter_order_Dir" id="filter_order_Dir" value="<?php echo $this->lists->order_Dir ?>"/>
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
				<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
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

	<table class="table table-striped">
		<thead>
		<tr>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);"/>
			</th>
			<th>
				<?php echo JHTML::_('grid.sort', 'ATOOLS_LBL_LOG_LOGDATE', 'logdate', $this->lists->order_Dir, $this->lists->order, 'browse'); ?>
			</th>
			<th>
				<?php echo JHTML::_('grid.sort', 'ATOOLS_LBL_LOG_IP', 'ip', $this->lists->order_Dir, $this->lists->order, 'browse'); ?>
			</th>
			<th>
				<?php echo JHTML::_('grid.sort', 'ATOOLS_LBL_LOG_REASON', 'reason', $this->lists->order_Dir, $this->lists->order, 'browse'); ?>
			</th>
			<th>
				<?php echo JHTML::_('grid.sort', 'ATOOLS_LBL_LOG_URL', 'url', $this->lists->order_Dir, $this->lists->order, 'browse'); ?>
			</th>
		</tr>
		<tr>
			<td></td>
			<td width="280" class="form-inline">
				<?php echo JHTML::_('calendar', $this->getModel()->getState('datefrom', ''), 'datefrom', 'datefrom', '%Y-%m-%d', array('onchange' => 'document.adminForm.submit();', 'class' => 'input-small')); ?>
				&ndash;
				<?php echo JHTML::_('calendar', $this->getModel()->getState('dateto', ''), 'dateto', 'dateto', '%Y-%m-%d', array('onchange' => 'document.adminForm.submit();', 'class' => 'input-small')); ?>
			</td>
			<td class="form-inline">
				<input type="text" name="ip" id="ip"
					   value="<?php echo $this->escape($this->getModel()->getState('ip', '')); ?>" size="30"
					   class="input-small" onchange="document.adminForm.submit();"
					   placeholder="<?php echo JText::_('ATOOLS_LBL_LOG_IP') ?>"
					/>
				<button class="btn btn-mini" onclick="this.form.submit();">
					<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>
				</button>
				<button class="btn btn-mini" onclick="document.adminForm.ip.value='';this.form.submit();">
					<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
				</button>
			</td>
			<td>
				<?php echo AdmintoolsHelperSelect::reasons($this->getModel()->getState('reason', ''), 'reason', array('onchange' => 'document.adminForm.submit();', 'class' => 'input-medium')) ?>
			</td>
			<td class="form-inline">
				<input type="text" name="url" id="url"
					   value="<?php echo $this->escape($this->getModel()->getState('url', '')); ?>" size="30"
					   class="input-medium" onchange="document.adminForm.submit();"
					   placeholder="<?php echo JText::_('ATOOLS_LBL_LOG_URL') ?>"/>
				<button class="btn btn-mini" onclick="this.form.submit();">
					<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>
				</button>
				<button class="btn btn-mini" onclick="document.adminForm.url.value='';this.form.submit();">
					<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
				</button>
			</td>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<td colspan="5">
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

			foreach ($this->items as $item):
				?>
				<tr>
					<td>
						<?php echo JHTML::_('grid.id', $i, $item->id, false); ?>
					</td>
					<td>
						<?php $date = new JDate($item->logdate);
						echo $date->format('Y-m-d H:i:s', true); ?>
					</td>
					<td>
						<a href="<?php echo str_replace('{ip}', $item->ip, $iplink) ?>" target="_blank"
						   class="btn btn-mini btn-info">
							<i class="icon-search icon-white"></i>
						</a>&nbsp;
						<?php if ($item->block): ?>
							<a class="btn btn-mini btn-success"
							   href="index.php?option=com_admintools&view=log&task=unban&id=<?php echo (int)($item->id) ?>&<?php echo JFactory::getSession()->getFormToken(); ?>=1"
							   title="<?php echo JText::_('ATOOLS_LBL_LOG_UNBAN') ?>">
								<i class="icon-white icon-minus-sign"></i>
							</a>
						<?php else: ?>
							<a class="btn btn-mini btn-danger"
							   href="index.php?option=com_admintools&view=log&task=ban&id=<?php echo (int)($item->id) ?>&<?php echo JFactory::getSession()->getFormToken(); ?>=1"
							   title="<?php echo JText::_('ATOOLS_LBL_LOG_BAN') ?>">
								<i class="icon-flag icon-white"></i>
							</a>
						<?php endif; ?>
						<?php echo $this->escape($item->ip) ?>
					</td>
					<td>
						<?php echo JText::_('ATOOLS_LBL_REASON_' . strtoupper($item->reason)) ?>
						<?php if ($item->extradata): ?>
							<?php
							if (stristr($item->extradata, '|') === false)
							{
								$item->extradata .= '|';
							}
							list($moreinfo, $techurl) = explode('|', $item->extradata);
							echo JHTML::_('tooltip', strip_tags($this->escape($moreinfo)), '', 'tooltip.png', '', $techurl);
							?>
						<?php endif; ?>
					</td>
					<td>
						<?php echo $this->escape($item->url) ?>
					</td>
				</tr>
				<?php
				$i++;
			endforeach;
			?>
		<?php else : ?>
			<tr>
				<td colspan="5" align="center"><?php echo JText::_('ATOOLS_ERR_LOG_NOITEMS') ?></td>
			</tr>
		<?php endif ?>
		</tbody>
	</table>

</form>