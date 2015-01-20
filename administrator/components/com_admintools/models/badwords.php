<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsModelBadword extends F0FModel
{
	public function buildQuery($overrideLimits = false)
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select(array('*'))
			->from($db->quoteName('#__admintools_badwords'));

		$fltWord = $this->getState('word', null, 'string');
		if ($fltWord)
		{
			$fltWord = '%' . $fltWord . '%';
			$query->where($db->quoteName('word') . ' LIKE ' . $db->quote($fltWord));
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