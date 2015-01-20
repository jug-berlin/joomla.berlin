<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

$lang = JFactory::getLanguage();
$option = 'com_admintools';

if (version_compare(JVERSION, '3.0', 'ge'))
{
	JHTML::_('behavior.framework');
	JHTML::_('behavior.tabstate');
	JHtml::_('formbehavior.chosen');
}
else
{
	JHTML::_('behavior.mootools');
	AkeebaStrapper::addCSSfile('media://com_admintools/css/chosen.min.css');
	AkeebaStrapper::addJSfile('media://com_admintools/js/chosen.jquery.min.js');

	$js = <<<JS
akeeba.jQuery(document).ready(function(){
    akeeba.jQuery('.advancedSelect').chosen({
        disable_search_threshold : 10,
        allow_single_deselect : true,
        width: '300px'
    });
})
JS;
	AkeebaStrapper::addJSdef($js);
}

$js = <<<JS
akeeba.jQuery(document).ready(function(){
    // Enable popovers
	akeeba.jQuery('[rel="popover"]').popover({
		trigger: 'manual',
		animate: false,
		html: true,
		placement: 'bottom',
		template: '<div class="popover akeeba-bootstrap-popover" onmouseover="akeeba.jQuery(this).mouseleave(function() {akeeba.jQuery(this).hide(); });"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"><p></p></div></div></div>'
	})
	.click(function(e) {
		e.preventDefault();
	})
	.mouseenter(function(e) {
		akeeba.jQuery('div.popover').remove();
		akeeba.jQuery(this).popover('show');
	});
})
JS;
AkeebaStrapper::addJSdef($js);

$this->loadHelper('select');

AkeebaStrapper::addJSdef($js);
?>
<form name="adminForm" id="adminForm" action="index.php" method="post"
	  class="form form-horizontal form-horizontal-wide">
<input type="hidden" name="option" value="com_admintools"/>
<input type="hidden" name="view" value="wafconfig"/>
<input type="hidden" name="task" value="save"/>
<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken(); ?>" value="1"/>

<?php if (!$this->longConfig && version_compare(JVERSION, '3.0.0', 'lt')): ?>
<?php echo JHtml::_('tabs.start', 'admintools-wafconfig', array('useCookie' => 1)); ?>
<?php elseif (!$this->longConfig && version_compare(JVERSION, '3.0.0', 'ge')): ?>
<?php echo JHtml::_('bootstrap.startTabSet', 'admintools-wafconfig', array('active' => 'basic')); ?>
<?php endif; ?>

<?php if ($this->longConfig): ?>
	<h3><?php echo JText::_('ATOOLS_LBL_WAF_OPTGROUP_BASICSETTINGS') ?></h3>
<?php elseif (version_compare(JVERSION, '3.0.0', 'lt')): ?>
	<?php echo JHtml::_('tabs.panel', JText::_('ATOOLS_LBL_WAF_OPTGROUP_BASICSETTINGS'), 'admintools-wafconfig-basic'); ?>
<?php else: ?>
	<?php echo JHtml::_('bootstrap.addTab', 'admintools-wafconfig', 'basic', addslashes(JText::_('ATOOLS_LBL_WAF_OPTGROUP_BASICSETTINGS'))); ?>
<?php endif; ?>

<div class="control-group">
	<label class="control-label" for="ipwl"
		rel="popover"
		data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_IPWL'); ?>"
		data-content="<?php echo JText::sprintf('COM_ADMINTOOLS_WAFCONFIG_IPWL_TIP', JURI::base() . 'index.php?option=com_admintools&view=ipwls') ?>"
		>
		<?php echo JText::_('ATOOLS_LBL_WAF_OPT_IPWL'); ?>
	</label>

	<div class="controls">
		<?php echo AdmintoolsHelperSelect::booleanlist('ipwl', array(), $this->wafconfig['ipwl']) ?>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="ipbl"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_IPBL'); ?>"
		   data-content="<?php echo JText::sprintf('COM_ADMINTOOLS_WAFCONFIG_IPBL_TIP', JURI::base() . 'index.php?option=com_admintools&view=ipbls') ?>"
		>
		<?php echo JText::_('ATOOLS_LBL_WAF_OPT_IPBL'); ?>
	</label>

	<div class="controls">
		<?php echo AdmintoolsHelperSelect::booleanlist('ipbl', array(), $this->wafconfig['ipbl']) ?>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="adminpw"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_ADMINPW'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_ADMINPW_TIP'); ?>">
		<?php echo JText::_('ATOOLS_LBL_WAF_OPT_ADMINPW'); ?>
	</label>

	<div class="controls">
		<input type="text" size="20" name="adminpw" value="<?php echo $this->wafconfig['adminpw'] ?>"/>
	</div>
</div>

<?php
$disabled = '';
$message = '';

if (!JFactory::getConfig()->get('sef') || !JFactory::getConfig()->get('sef_rewrite'))
{
	$disabled = ' disabled="true"';
	$message = '<div class="alert" style="margin:10px 0 0">' . JText::_('ATOOLS_LBL_WAF_OPT_ADMINLOGINFOLDER_ALERT') . '</div>';
}
?>

<div class="control-group">
	<label class="control-label" for="adminpw"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_ADMINLOGINFOLDER'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_ADMINLOGINFOLDER_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_ADMINLOGINFOLDER'); ?>
	</label>

	<div class="controls">
		<input type="text" <?php echo $disabled ?>size="20" name="adminlogindir"
			   value="<?php echo $this->wafconfig['adminlogindir'] ?>"/>
		<?php echo $message ?>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="awayschedule_from"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_AWAYSCHEDULE'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_AWAYSCHEDULE_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_AWAYSCHEDULE') ?>
	</label>

	<div class="controls">
		<?php echo JText::_('ATOOLS_LBL_WAF_AWAYSCHEDULE_FROM') ?>
		<input type="text" name="awayschedule_from" id="awayschedule_from" class="input-mini"
			   value="<?php echo $this->wafconfig['awayschedule_from'] ?>"/>
		<?php echo JText::_('ATOOLS_LBL_WAF_AWAYSCHEDULE_TO') ?>
		<input type="text" name="awayschedule_to" id="awayschedule_to" class="input-mini"
			   value="<?php echo $this->wafconfig['awayschedule_to'] ?>"/>

		<div class="alert alert-info" style="margin-top: 10px">
			<?php
			$date = new JDate('now', JFactory::getConfig()->get('offset', 'UTC'));
			echo JText::sprintf('ATOOLS_LBL_WAF_AWAYSCHEDULE_TIMEZONE', $date->format('H:i', true));
			?>
		</div>
	</div>
</div>

<?php if ($this->longConfig): ?>
	<h3><?php echo JText::_('ATOOLS_LBL_WAF_OPTGROUP_ACTIVEFILTERING') ?></h3>
<?php elseif (version_compare(JVERSION, '3.0.0', 'lt')): ?>
	<?php echo JHtml::_('tabs.panel', JText::_('ATOOLS_LBL_WAF_OPTGROUP_ACTIVEFILTERING'), 'admintools-wafconfig-activefiltering'); ?>
<?php else: ?>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'admintools-wafconfig', 'activefiltering', addslashes(JText::_('ATOOLS_LBL_WAF_OPTGROUP_ACTIVEFILTERING'))); ?>
<?php endif; ?>

<div class="control-group">
	<label class="control-label" for="sqlishield"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_SQLISHIELD'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_SQLISHIELD_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_SQLISHIELD'); ?>
	</label>

	<div class="controls">
		<?php echo AdmintoolsHelperSelect::booleanlist('sqlishield', array(), $this->wafconfig['sqlishield']) ?>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="xssshield"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_XSSSHIELD'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_XSSSHIELD_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_XSSSHIELD'); ?>
	</label>

	<div class="controls">
		<?php echo AdmintoolsHelperSelect::booleanlist('xssshield', array(), $this->wafconfig['xssshield']) ?>
	</div>
</div>

<div class="control-group">
	<label class="control-label"
		   for="xssshield_allowphp"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_XSSSHIELD_PHP'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_XSSSHIELD_PHP_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_XSSSHIELD_PHP'); ?>
	</label>

	<div class="controls">
		<?php echo AdmintoolsHelperSelect::booleanlist('xssshield_allowphp', array(), $this->wafconfig['xssshield_allowphp']) ?>
	</div>
</div>

<?php
// Warning! If the users inputs an empty string, the default one will be prompted, even if in the DB there aren't any
// However, this is a very edge scenario, since only experienced users should touch this field and they can blank it
// out inserting a space character
?>
<div class="control-group">
	<label class="control-label"
		   for="xssshield_safe_keys"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_XSSSHIELD_SAFE_PARAMS'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_XSSSHIELD_SAFE_PARAMS_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_XSSSHIELD_SAFE_PARAMS'); ?>
	</label>

	<div class="controls">
		<input type="text" size="20" name="xssshield_safe_keys"
			   value="<?php echo $this->wafconfig['xssshield_safe_keys'] ?>"/>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="muashield"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_MUASHIELD'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_MUASHIELD_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_MUASHIELD'); ?>
	</label>

	<div class="controls">
		<?php echo AdmintoolsHelperSelect::booleanlist('muashield', array(), $this->wafconfig['muashield']) ?>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="csrfshield"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_CSRFSHIELD'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_CSRFSHIELD_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_CSRFSHIELD'); ?>
	</label>

	<div class="controls">
		<?php echo AdmintoolsHelperSelect::csrflist('csrfshield', array(), $this->wafconfig['csrfshield']) ?>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="rfishield"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_RFISHIELD'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_RFISHIELD_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_RFISHIELD'); ?>
	</label>

	<div class="controls">
		<?php echo AdmintoolsHelperSelect::booleanlist('rfishield', array(), $this->wafconfig['rfishield']) ?>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="dfishield"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_DFISHIELD'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_DFISHIELD_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_DFISHIELD'); ?>
	</label>

	<div class="controls">
		<?php echo AdmintoolsHelperSelect::booleanlist('dfishield', array(), $this->wafconfig['dfishield']) ?>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="uploadshield"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_UPLOADSHIELD'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_UPLOADSHIELD_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_UPLOADSHIELD'); ?>
	</label>

	<div class="controls">
		<?php echo AdmintoolsHelperSelect::booleanlist('uploadshield', array(), $this->wafconfig['uploadshield']) ?>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="antispam"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_ANTISPAM'); ?>"
		   data-content="<?php echo JText::sprintf('ATOOLS_LBL_WAF_OPT_ANTISPAM_TIP', JURI::base() . 'index.php?option=com_admintools&view=badwords'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_ANTISPAM'); ?>
	</label>

	<div class="controls">
		<?php echo AdmintoolsHelperSelect::booleanlist('antispam', array(), $this->wafconfig['antispam']) ?>
	</div>
</div>

<?php if ($this->longConfig): ?>
	<h3><?php echo JText::_('ATOOLS_LBL_WAF_OPTGROUP_JHARDENING') ?></h3>
<?php elseif (version_compare(JVERSION, '3.0.0', 'lt')): ?>
	<?php echo JHtml::_('tabs.panel', JText::_('ATOOLS_LBL_WAF_OPTGROUP_JHARDENING'), 'admintools-wafconfig-jhardening'); ?>
<?php else: ?>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'admintools-wafconfig', 'jhardening', addslashes(JText::_('ATOOLS_LBL_WAF_OPTGROUP_JHARDENING'))); ?>
<?php endif; ?>

<div class="control-group">
	<label class="control-label" for="nonewadmins"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_NONEWADMINS'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_NONEWADMINS_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_NONEWADMINS'); ?>
	</label>

	<div class="controls">
		<?php echo AdmintoolsHelperSelect::booleanlist('nonewadmins', array(), $this->wafconfig['nonewadmins']) ?>
	</div>
</div>

<?php if (version_compare(JVERSION, '3.2.0', 'ge')): ?>
	<div class="control-group">
		<label class="control-label"
			   for="resetjoomlatfa"
			   rel="popover"
			   data-original-title="<?php echo JText::_('COM_ADMINTOOLS_LBL_WAF_OPT_RESETJOOMLATFA'); ?>"
			   data-content="<?php echo JText::_('COM_ADMINTOOLS_LBL_WAF_OPT_RESETJOOMLATFA_TIP'); ?>">
		<?php echo JText::_('COM_ADMINTOOLS_LBL_WAF_OPT_RESETJOOMLATFA'); ?>
		</label>

		<div class="controls">
			<?php echo AdmintoolsHelperSelect::booleanlist('resetjoomlatfa', array(), $this->wafconfig['resetjoomlatfa']) ?>
		</div>
	</div>
<?php endif; ?>

<div class="control-group">
	<label class="control-label" for="nofesalogin"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_NOFESALOGIN'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_NOFESALOGIN_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_NOFESALOGIN'); ?>
	</label>

	<div class="controls">
		<?php echo AdmintoolsHelperSelect::booleanlist('nofesalogin', array(), $this->wafconfig['nofesalogin']) ?>
	</div>
</div>

<div class="control-group">
	<label class="control-label"
		   for="trackfailedlogins"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_TRACKFAILEDLOGINS'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_TRACKFAILEDLOGINS_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_TRACKFAILEDLOGINS'); ?>
	</label>

	<div class="controls">
		<?php echo AdmintoolsHelperSelect::booleanlist('trackfailedlogins', array(), $this->wafconfig['trackfailedlogins']) ?>
	</div>
</div>

<?php
// Detect user registration and activation type
$disabled = '';
$message = '';
$classes = array('class' => 'input-small');
$userParams = JComponentHelper::getParams('com_users');

// User registration disabled
if (!$userParams->get('allowUserRegistration'))
{
	$classes['disabled'] = 'true';
	$disabled = ' disabled="true" ';
	$message = '<div style="margin-top:10px" class="alert alert-info">' . JText::_('ATOOLS_LBL_WAF_ALERT_NOREGISTRATION') . '</div>';
}
// Super User user activation
elseif ($userParams->get('useractivation') == 2)
{
	$message = '<div style="margin-top: 10px" class="alert">' . JText::_('ATOOLS_LBL_WAF_ALERT_ADMINACTIVATION') . '</div>';
}
// No user activation
elseif ($userParams->get('useractivation') == 0)
{
	$classes['disabled'] = 'true';
	$disabled = ' disabled="true" ';
	$message = '<div style="margin-top:10px" class="alert alert-info">' . JText::_('ATOOLS_LBL_WAF_ALERT_NOUSERACTIVATION') . '</div>';
}
?>

<div class="control-group">
	<label class="control-label"
		   for="deactivateusers"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_DEACTIVATEUSERS'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_DEACTIVATEUSERS_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_DEACTIVATEUSERS'); ?>
	</label>

	<div class="controls">
		<input class="input-mini pull-left" type="text" size="5" name="deactivateusers_num" <?php echo $disabled ?>
			   value="<?php echo $this->wafconfig['deactivateusers_num'] ?>"/>
		<span class="floatme"><?php echo JText::_('ATOOLS_LBL_WAF_LBL_DEACTIVATENUMFREQ') ?></span>
		<input class="input-mini" type="text" size="5" name="deactivateusers_numfreq" <?php echo $disabled ?>
			   value="<?php echo $this->wafconfig['deactivateusers_numfreq'] ?>"/>
		<?php
		echo AdmintoolsHelperSelect::trsfreqlist('deactivateusers_frequency', $classes, $this->wafconfig['deactivateusers_frequency']);

		echo $message;
		?>
	</div>
</div>

<div class="control-group">
	<label class="control-label"
		   for="blockedemaildomains"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_BLOCKEDEMAILDOMAINS')?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_BLOCKEDEMAILDOMAINS_TIP')?>">
		<?php echo JText::_('ATOOLS_LBL_WAF_OPT_BLOCKEDEMAILDOMAINS')?>
	</label>

	<div class="controls">
		<textarea id="blockedemaildomains" name="blockedemaildomains" rows="5"><?php echo $this->wafconfig['blockedemaildomains']?></textarea>
	</div>
</div>

<?php if ($this->longConfig): ?>
	<h3><?php echo JText::_('ATOOLS_LBL_WAF_OPTGROUP_FINGERPRINTING') ?></h3>
<?php elseif (version_compare(JVERSION, '3.0.0', 'lt')): ?>
	<?php echo JHtml::_('tabs.panel', JText::_('ATOOLS_LBL_WAF_OPTGROUP_FINGERPRINTING'), 'admintools-wafconfig-fingerprinting'); ?>
<?php else: ?>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'admintools-wafconfig', 'fingerprinting', addslashes(JText::_('ATOOLS_LBL_WAF_OPTGROUP_FINGERPRINTING'))); ?>
<?php endif; ?>

<div class="control-group">
	<label class="control-label"
		   for="custgenerator"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_CUSTGENERATOR'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_CUSTGENERATOR_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_CUSTGENERATOR'); ?>
	</label>

	<div class="controls">
		<?php echo AdmintoolsHelperSelect::booleanlist('custgenerator', array(), $this->wafconfig['custgenerator']) ?>
	</div>
</div>
<div class="control-group">
	<label class="control-label" for="generator"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_GENERATOR'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_GENERATOR_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_GENERATOR'); ?>
	</label>

	<div class="controls">
		<input type="text" size="45" name="generator" value="<?php echo $this->wafconfig['generator'] ?>">
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="tmpl"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_TMPL'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_TMPL_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_TMPL'); ?>
	</label>

	<div class="controls">
		<?php echo AdmintoolsHelperSelect::booleanlist('tmpl', array(), $this->wafconfig['tmpl']) ?>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="tmplwhitelist"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_TMPLWHITELIST'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_TMPLWHITELIST_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_TMPLWHITELIST'); ?>
	</label>

	<div class="controls">
		<input type="text" size="45" name="tmplwhitelist" value="<?php echo $this->wafconfig['tmplwhitelist'] ?>"/>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="template"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_TEMPLATE'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_TEMPLATE_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_TEMPLATE'); ?>
	</label>

	<div class="controls">
		<?php echo AdmintoolsHelperSelect::booleanlist('template', array(), $this->wafconfig['template']) ?>
	</div>
</div>

<div class="control-group">
	<label class="control-label"
		   for="allowsitetemplate"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_ALLOWSITETEMPLATE'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_ALLOWSITETEMPLATE_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_ALLOWSITETEMPLATE') ?>
	</label>

	<div class="controls">
		<?php echo AdmintoolsHelperSelect::booleanlist('allowsitetemplate', array(), $this->wafconfig['allowsitetemplate']) ?>
	</div>
</div>

<?php if ($this->longConfig): ?>
	<h3><?php echo JText::_('ATOOLS_LBL_WAF_LBL_PROJECTHONEYPOT') ?></h3>
<?php elseif (version_compare(JVERSION, '3.0.0', 'lt')): ?>
	<?php echo JHtml::_('tabs.panel', JText::_('ATOOLS_LBL_WAF_LBL_PROJECTHONEYPOT'), 'admintools-wafconfig-projecthoneypot'); ?>
<?php else: ?>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'admintools-wafconfig', 'projecthoneypot', addslashes(JText::_('ATOOLS_LBL_WAF_LBL_PROJECTHONEYPOT'))); ?>
<?php endif; ?>

<div class="control-group">
	<label class="control-label"
		   for="httpblenable"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_HTTPBLENABLE'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_HTTPBLENABLE_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_HTTPBLENABLE'); ?>
	</label>

	<div class="controls">
		<?php echo AdmintoolsHelperSelect::booleanlist('httpblenable', array(), $this->wafconfig['httpblenable']) ?>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="bbhttpblkey"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_BBHTTPBLKEY'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_BBHTTPBLKEY_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_BBHTTPBLKEY'); ?>
	</label>

	<div class="controls">
		<input type="text" size="45" name="bbhttpblkey" value="<?php echo $this->wafconfig['bbhttpblkey'] ?>"/>
	</div>
</div>

<div class="control-group">
	<label class="control-label"
		   for="httpblthreshold"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_HTTPBLTHRESHOLD'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_HTTPBLTHRESHOLD_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_HTTPBLTHRESHOLD'); ?>
	</label>

	<div class="controls">
		<input type="text" size="5" name="httpblthreshold"
			   value="<?php echo $this->wafconfig['httpblthreshold'] ?>"/>
	</div>
</div>

<div class="control-group">
	<label class="control-label"
		   for="httpblmaxage"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_HTTPBLMAXAGE'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_HTTPBLMAXAGE_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_HTTPBLMAXAGE'); ?>
	</label>

	<div class="controls">
		<input type="text" size="5" name="httpblmaxage" value="<?php echo $this->wafconfig['httpblmaxage'] ?>"/>
	</div>
</div>

<div class="control-group">
	<label class="control-label"
		   for="httpblblocksuspicious"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_HTTPBLBLOCKSUSPICIOUS'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_HTTPBLBLOCKSUSPICIOUS_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_HTTPBLBLOCKSUSPICIOUS'); ?>
	</label>

	<div class="controls">
		<?php echo AdmintoolsHelperSelect::booleanlist('httpblblocksuspicious', array(), $this->wafconfig['httpblblocksuspicious']) ?>
	</div>
</div>

<?php if ($this->longConfig): ?>
	<h3><?php echo JText::_('ATOOLS_LBL_WAF_OPTGROUP_EXCEPTIONS') ?></h3>
<?php elseif (version_compare(JVERSION, '3.0.0', 'lt')): ?>
	<?php echo JHtml::_('tabs.panel', JText::_('ATOOLS_LBL_WAF_OPTGROUP_EXCEPTIONS'), 'admintools-wafconfig-exceptions'); ?>
<?php else: ?>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'admintools-wafconfig', 'exceptions', addslashes(JText::_('ATOOLS_LBL_WAF_OPTGROUP_EXCEPTIONS'))); ?>
<?php endif; ?>

<div class="control-group">
	<label class="control-label"
		   for="neverblockips"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_LBL_NEVERBLOCKIPS'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_LBL_NEVERBLOCKIPS_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_LBL_NEVERBLOCKIPS'); ?>
	</label>

	<div class="controls">
		<input class="input-xxlarge" type="text" size="50" name="neverblockips"
			   value="<?php echo $this->wafconfig['neverblockips'] ?>"/>
	</div>
</div>

<div class="control-group">
	<label class="control-label"
		   for="whitelist_domains"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_WHITELIST_DOMAINS'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_WHITELIST_DOMAINS_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_WHITELIST_DOMAINS'); ?>
	</label>

	<div class="controls">
		<input type="text" class="input-large" name="whitelist_domains" id="whitelist_domains"
			   value="<?php echo $this->wafconfig['whitelist_domains'] ?>">
	</div>
</div>

<?php if ($this->longConfig): ?>
	<h3><?php echo JText::_('ATOOLS_LBL_WAF_LBL_TSR') ?></h3>
<?php elseif (version_compare(JVERSION, '3.0.0', 'lt')): ?>
	<?php echo JHtml::_('tabs.panel', JText::_('ATOOLS_LBL_WAF_LBL_TSR'), 'admintools-wafconfig-tsr'); ?>
<?php else: ?>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'admintools-wafconfig', 'tsr', addslashes(JText::_('ATOOLS_LBL_WAF_LBL_TSR'))); ?>
<?php endif; ?>

<div class="control-group">
	<label class="control-label" for="tsrenable"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_LBL_TSRENABLE'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_LBL_TSRENABLE_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_LBL_TSRENABLE'); ?>
	</label>

	<div class="controls">
		<?php echo AdmintoolsHelperSelect::booleanlist('tsrenable', array(), $this->wafconfig['tsrenable']) ?>
	</div>
</div>

<div class="control-group">
	<label class="control-label"
		   for="emailafteripautoban"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_LBL_EMAILAFTERIPAUTOBAN'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_LBL_EMAILAFTERIPAUTOBAN_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_LBL_EMAILAFTERIPAUTOBAN'); ?>
	</label>

	<div class="controls">
		<input class="input-large" type="text" size="50" name="emailafteripautoban"
			   value="<?php echo $this->wafconfig['emailafteripautoban'] ?>"/>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="tsrstrikes"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_LBL_TSRSTRIKES'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_LBL_TSRSTRIKES_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_LBL_TSRSTRIKES'); ?>
	</label>

	<div class="controls">
		<input class="input-mini pull-left" type="text" size="5" name="tsrstrikes"
			   value="<?php echo $this->wafconfig['tsrstrikes'] ?>"/>
		<span class="floatme"><?php echo JText::_('ATOOLS_LBL_WAF_LBL_TSRNUMFREQ') ?></span>
		<input class="input-mini" type="text" size="5" name="tsrnumfreq"
			   value="<?php echo $this->wafconfig['tsrnumfreq'] ?>"/>
		<?php echo AdmintoolsHelperSelect::trsfreqlist('tsrfrequency', array('class' => 'input-small'), $this->wafconfig['tsrfrequency']) ?>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="tsrbannum"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_LBL_TSRBANNUM'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_LBL_TSRBANNUM_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_LBL_TSRBANNUM'); ?>
	</label>

	<div class="controls">
		<input class="input-mini" type="text" size="5" name="tsrbannum"
			   value="<?php echo $this->wafconfig['tsrbannum'] ?>"/>
		&nbsp;
		<?php echo AdmintoolsHelperSelect::trsfreqlist('tsrbanfrequency', array(), $this->wafconfig['tsrbanfrequency']) ?>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="tsrpermaban"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_LBL_PERMABAN'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_LBL_PERMABAN_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_LBL_PERMABAN'); ?>
	</label>

	<div class="controls">
		<?php echo AdmintoolsHelperSelect::booleanlist('permaban', array(), $this->wafconfig['permaban']) ?>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="permabannum"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_LBL_PERMABANNUM'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_LBL_PERMABANNUM_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_LBL_PERMABANNUM'); ?>
	</label>

	<div class="controls">
		<input class="input-mini" type="text" size="5" name="permabannum"
			   value="<?php echo $this->wafconfig['permabannum'] ?>"/>
		<span><?php echo JText::_('ATOOLS_LBL_WAF_LBL_PERMABANNUM_2') ?></span>
	</div>
</div>

<div class="control-group">
	<label class="control-label"
		   for="spammermessage"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_LBL_SPAMMERMESSAGE'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_LBL_SPAMMERMESSAGE_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_LBL_SPAMMERMESSAGE'); ?>
	</label>

	<div class="controls">
		<input type="text" class="input-xxlarge" name="spammermessage"
			   value="<?php echo $this->wafconfig['spammermessage'] ?>"/>
	</div>
</div>

<?php if ($this->longConfig): ?>
	<h3><?php echo JText::_('ATOOLS_LBL_WAF_OPTGROUP_LOGGINGANDREPORTING') ?></h3>
<?php elseif (version_compare(JVERSION, '3.0.0', 'lt')): ?>
	<?php echo JHtml::_('tabs.panel', JText::_('ATOOLS_LBL_WAF_OPTGROUP_LOGGINGANDREPORTING'), 'admintools-wafconfig-loggingandreporting'); ?>
<?php else: ?>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'admintools-wafconfig', 'loggingandreporting', addslashes(JText::_('ATOOLS_LBL_WAF_OPTGROUP_LOGGINGANDREPORTING'))); ?>
<?php endif; ?>

<div class="control-group">
	<label class="control-label"
		   for="saveusersignupip"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_SAVEUSERSIGNUPIP'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_SAVEUSERSIGNUPIP_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_SAVEUSERSIGNUPIP'); ?>
	</label>

	<div class="controls">
		<?php echo AdmintoolsHelperSelect::booleanlist('saveusersignupip', array(), $this->wafconfig['saveusersignupip']) ?>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="logbreaches"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_LOGBREACHES'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_LOGBREACHES_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_LOGBREACHES'); ?>
	</label>

	<div class="controls">
		<?php echo AdmintoolsHelperSelect::booleanlist('logbreaches', array(), $this->wafconfig['logbreaches']) ?>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="iplookup"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_IPLOOKUP_LABEL'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_IPLOOKUP_DESC'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_IPLOOKUP_LABEL'); ?>
	</label>

	<div class="controls">
		<?php echo AdmintoolsHelperSelect::httpschemes('iplookupscheme', array('class' => 'input-small'), $this->wafconfig['iplookupscheme']) ?>
		<input type="text" size="50" name="iplookup" value="<?php echo $this->wafconfig['iplookup'] ?>"
			   title="<?php echo JText::_('ATOOLS_LBL_WAF_IPLOOKUP_DESC') ?>"/>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="emailbreaches"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_EMAILBREACHES'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_EMAILBREACHES_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_EMAILBREACHES'); ?>
	</label>

	<div class="controls">
		<input type="text" size="20" name="emailbreaches" value="<?php echo $this->wafconfig['emailbreaches'] ?>">
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="emailonadminlogin"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_EMAILADMINLOGIN'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_EMAILADMINLOGIN_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_EMAILADMINLOGIN'); ?>
	</label>

	<div class="controls">
		<input type="text" size="20" name="emailonadminlogin"
			   value="<?php echo $this->wafconfig['emailonadminlogin'] ?>" >
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="emailonfailedadminlogin"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_EMAILADMINFAILEDLOGIN'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_EMAILADMINFAILEDLOGIN_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_EMAILADMINFAILEDLOGIN'); ?>
	</label>

	<div class="controls">
		<input type="text" size="20" name="emailonfailedadminlogin"
			   value="<?php echo $this->wafconfig['emailonfailedadminlogin'] ?>">
	</div>
</div>

<div class="control-group">
	<label class="control-label"
		   for="showpwonloginfailure"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_SHOWPWONLOGINFAILURE'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_SHOWPWONLOGINFAILURE_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_SHOWPWONLOGINFAILURE'); ?>
	</label>

	<div class="controls">
		<?php echo AdmintoolsHelperSelect::booleanlist('showpwonloginfailure', array(), $this->wafconfig['showpwonloginfailure']) ?>
	</div>
</div>

<div class="control-group">
	<label class="control-label"
		   for="reasons_nolog"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_REASONS_NOLOG'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_REASONS_NOLOG_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_REASONS_NOLOG'); ?>
	</label>

	<div class="controls">
		<?php
		echo AdmintoolsHelperSelect::reasons($this->wafconfig['reasons_nolog'], 'reasons_nolog[]', array(
				'class'     => 'advancedSelect input-large',
				'multiple'  => 'multiple',
				'size'      => 5,
				'hideEmpty' => true
			)
		)
		?>
	</div>
</div>

<div class="control-group">
	<label class="control-label"
		   for="reasons_noemail"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_REASONS_NOEMAIL'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_REASONS_NOEMAIL_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_REASONS_NOEMAIL'); ?>
	</label>

	<div class="controls">
		<?php
		echo AdmintoolsHelperSelect::reasons($this->wafconfig['reasons_noemail'], 'reasons_noemail[]', array(
				'class'     => 'advancedSelect input-large',
				'multiple'  => 'multiple',
				'size'      => 5,
				'hideEmpty' => true
			)
		)
		?>
	</div>
</div>

<div class="control-group">
	<label class="control-label"
		   for="email_throttle"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_EMAILTHROTTLE'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_EMAILTHROTTLE_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_EMAILTHROTTLE'); ?>
	</label>

	<div class="controls">
		<?php echo AdmintoolsHelperSelect::booleanlist('email_throttle', array(), $this->wafconfig['email_throttle']) ?>
	</div>
</div>

<?php if ($this->longConfig): ?>
	<h3><?php echo JText::_('ATOOLS_LBL_WAF_CUSTOMMESSAGE_HEADER') ?></h3>
<?php elseif (version_compare(JVERSION, '3.0.0', 'lt')): ?>
	<?php echo JHtml::_('tabs.panel', JText::_('ATOOLS_LBL_WAF_CUSTOMMESSAGE_HEADER'), 'admintools-wafconfig-custommessage'); ?>
<?php else: ?>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'admintools-wafconfig', 'custommessage', addslashes(JText::_('ATOOLS_LBL_WAF_CUSTOMMESSAGE_HEADER'))); ?>
<?php endif; ?>

<div class="control-group">
	<label class="control-label" for="custom403msg"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_CUSTOMMESSAGE_LABEL'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_CUSTOMMESSAGE_DESC'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_CUSTOMMESSAGE_LABEL'); ?>
	</label>

	<div class="controls">
		<input type="text" class="input-xxlarge" name="custom403msg"
			   value="<?php echo $this->wafconfig['custom403msg'] ?>"
			   title="<?php echo JText::_('ATOOLS_LBL_WAF_CUSTOMMESSAGE_DESC') ?>"/>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="use403view"
		   rel="popover"
		   data-original-title="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_USE403VIEW'); ?>"
		   data-content="<?php echo JText::_('ATOOLS_LBL_WAF_OPT_USE403VIEW_TIP'); ?>">
	<?php echo JText::_('ATOOLS_LBL_WAF_OPT_USE403VIEW'); ?>
	</label>

	<div class="controls">
		<?php echo AdmintoolsHelperSelect::booleanlist('use403view', array(), $this->wafconfig['use403view']) ?>
	</div>
</div>

<?php if (!$this->longConfig && version_compare(JVERSION, '3.0.0', 'lt')): ?>
<?php echo JHtml::_('tabs.end'); ?>
<?php elseif (!$this->longConfig && version_compare(JVERSION, '3.0.0', 'ge')): ?>
<?php echo JHtml::_('bootstrap.endTab'); ?>
<?php echo JHtml::_('bootstrap.endTabSet'); ?>
<?php endif; ?>
</form>