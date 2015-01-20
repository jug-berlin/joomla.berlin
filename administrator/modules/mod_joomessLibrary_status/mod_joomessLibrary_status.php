<?php
/**
 * @package Joomess.de Libary for the extensions of Johannes Meßmer
 * @projectsite www.joomess.de
 * @author Johannes Meßmer
 * @copyright (C) 2012 Johannes Me�mer
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

//-- No direct access
defined('_JEXEC') or die('=;)'); 

//joomessLibrary - Plugin laden
$jlib = JPATH_SITE.'/'.'plugins'.'/'.'system'.( (version_compare( JVERSION, '1.6.0', 'lt' )) ? "" : '/'.'joomessLibrary' ).'/'.'joomessLibrary.php';
if(!JFile::exists($jlib)) return;
else require_once $jlib;

$lib =& joomessLibrary::getInstance();
if(!$lib->active()) return;
$lib->css("administrator/modules/mod_joomessLibrary_status/assets/default.css");

$now = time();
$cache_time = $lib->getParam("cache_enabled", $now);
$caching = ($now-$cache_time >= 0);

if($caching) { ?>
	<span class="jm-cache-status">
		<a href="#" onclick="joomessLibraryCache.style.display = 'inline'; this.style.display = 'none'; return false;"><?php echo JText::_("JLIB_MOD_STATUS_JMCache_ENABLED");?></a>
		<form action="<?php $u =& JURI::getInstance(); echo $u->toString();?>" method="post" name="joomessLibraryCache" class="jm_cache_form">
			<select onchange="joomessLibraryCache.submit();" name="joomessLibraryCacheValue">
				<option value="0" selected="selected"><?php echo JText::_("JLIB_MOD_STATUS_Disable_for");?>..</option>
				<option value="60">1 <?php echo JText::_("JLIB_MOD_STATUS_MINUTE");?></option>
				<option value="600">10 <?php echo JText::_("JLIB_MOD_STATUS_MINUTES");?></option>
				<option value="1800">30 <?php echo JText::_("JLIB_MOD_STATUS_MINUTES");?></option>
				<option value="3600">1 <?php echo JText::_("JLIB_MOD_STATUS_HOUR");?></option>
				<option value="7200">2 <?php echo JText::_("JLIB_MOD_STATUS_HOURs");?></option>
				<option value="86400">1 <?php echo JText::_("JLIB_MOD_STATUS_DAY");?></option>
				<option value="-2"><?php echo JText::_("JLIB_MOD_STATUS_CLEAR_ONCE");?></option>
				<option value="-3"><?php echo JText::_("JLIB_MOD_STATUS_HIDE_MODULE");?></option>
			</select>
		</form>
	</span>
<?php } else { ?>
	<span class="jm-cache-status status-off">
		<a href="#" onclick="joomessLibraryCache.style.display = 'inline'; this.style.display = 'none'; return false;" title="<?php echo sprintf(JText::_("JLIB_MOD_STATUS_DISABLED_FOR"), abs($now-$cache_time)." ".JText::_("JLIB_MOD_STATUS_Seconds"));?>"><?php echo JText::_("JLIB_MOD_STATUS_JMCache_DISABLED");?></a>
		<form action="<?php $u =& JURI::getInstance(); echo $u->toString();?>" method="post" name="joomessLibraryCache" class="jm_cache_form">
			<input type="hidden" name="joomessLibraryCacheValue" value="-1" />
			<a href="#" onclick="joomessLibraryCache.submit();"><?php echo JText::_("JLIB_MOD_STATUS_Enable_Caching")?></a>
		</form>
	</span>
<?php } 