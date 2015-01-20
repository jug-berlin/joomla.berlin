<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsTableLog extends F0FTable
{
	var $logdate = '0000-00-00 00:00:00';

	public function __construct($table, $key, &$db)
	{
		parent::__construct('#__admintools_log', 'id', $db);
	}

	function check()
	{
		return true;
	}
}
