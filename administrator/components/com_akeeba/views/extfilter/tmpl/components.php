<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 *
 * @since 2.1
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

JLoader::import('joomla.html.html');
JHtml::_('behavior.framework');

?>
<div class="alert alert-warning">
	<span class="icon icon-warning-circle"></span>
	<?php echo JText::_('COM_AKEEBA_DEPRECATED_FEATURE'); ?>
</div>

<div class="alert alert-info">
	<strong><?php echo JText::_('CPANEL_PROFILE_TITLE'); ?>: #<?php echo $this->profileid; ?></strong>
	<?php echo $this->profilename; ?>
</div>

<table class="table table-striped" width="100%">
	<thead>
		<tr>
			<th width="50px"><?php echo JText::_('EXTFILTER_LABEL_STATE'); ?></th>
			<th><?php echo JText::_('EXTFILTER_LABEL_COMPONENT'); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php
	$i = 0;
	foreach($this->components as $component):
	$i++;
	$link = JUri::base().'index.php?option=com_akeeba&view=extfilter&task=toggleComponent&root='.$component['root'].'&item='.$component['item'].'&random='.time();
	if($component['status'])
	{
		if(version_compare(JVERSION, '3.0', 'lt')) {
			$image = JHTML::_('image.administrator', 'admin/publish_x.png');
		} else {
			$image = '<i class="icon-unpublish"></i>';
		}
		$html = '<b>'.$component['name'].'</b>';
	}
	else
	{
		if(version_compare(JVERSION, '3.0', 'lt')) {
			$image = JHTML::_('image.administrator', 'admin/tick.png');
		} else {
			$image = '<i class="icon-publish"></i>';
		}
		$html = $component['name'];
	}

	?>
		<tr>
			<td style="text-align: center;"><a href="<?php echo $link ?>"><?php echo $image ?></a></td>
			<td><a href="<?php echo $link ?>"><?php echo $html ?></a></td>
		</tr>
	<?php
	endforeach;
	?>
	</tbody>
</table>