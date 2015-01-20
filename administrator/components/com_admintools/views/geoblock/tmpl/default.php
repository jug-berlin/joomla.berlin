<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

?>
<div class="alert alert-info">
	<a class="close" data-dismiss="alert" href="#">×</a>

	<h3><?php echo JText::_('ATOOLS_LBL_GEOBLOCK_INFOHEAD'); ?></h3>

	<p><?php echo JText::_('ATOOLS_LBL_GEOBLOCK_INFO'); ?></p>

	<p class="small"><?php echo JText::_('ATOOLS_LBL_GEOBLOCK_MAXMIND'); ?></p>
</div>

<form action="index.php" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
	<input type="hidden" name="option" value="com_admintools"/>
	<input type="hidden" name="view" value="geoblock"/>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken(); ?>" value="1"/>

	<fieldset id="waf-continents">
		<legend><?php echo JText::_('ATOOLS_LBL_GEOBLOCK_CONTINENTS') ?></legend>

		<?php echo $this->continents; ?>
	</fieldset>

	<fieldset id="waf-countries">
		<legend><?php echo JText::_('ATOOLS_LBL_GEOBLOCK_COUNTRIES') ?></legend>

		<table class="table table-striped">
			<thead>
			<tr>
				<th colspan="3">
					<button class="btn"
							onclick="$$('.country').setProperty('checked','checked');return false;"><?php echo JText::_('ATOOLS_LBL_GEOBLOCK_ALL') ?></button>
					<button class="btn"
							onclick="$$('.country').setProperty('checked','');return false;"><?php echo JText::_('ATOOLS_LBL_GEOBLOCK_NONE') ?></button>
				</th>
			</tr>
			</thead>
			<tbody>
			<?php echo $this->countries; ?>
			</tbody>
		</table>
	</fieldset>
</form>