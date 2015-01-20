<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsViewLogs extends F0FViewJson
{
	function onBrowse($tpl = null)
	{
		// I have to override parent method or I'll F0FViewHtml will always save data,
		// overwriting incoming parameters
		return $this->onDisplay($tpl);
	}
}