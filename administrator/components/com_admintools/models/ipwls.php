<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsModelIpwls extends F0FModel
{
	public function buildQuery($overrideLimits = false)
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select(array('*'))
			->from($db->quoteName('#__admintools_adminiplist'));

		$fltIP = $this->getState('ip', null, 'string');
		if ($fltIP)
		{
			$fltIP = '%' . $fltIP . '%';
			$query->where($db->quoteName('ip') . ' LIKE ' . $db->quote($fltIP));
		}

		if (!$overrideLimits)
		{
			$order = $this->getState('filter_order', null, 'cmd');
			if (!in_array($order, array_keys($this->getTable()->getData())))
			{
				$order = 'id';
			}
			$dir = $this->getState('filter_order_Dir', 'ASC', 'cmd');
			$query->order($order . ' ' . $dir);
		}

		return $query;
	}
}