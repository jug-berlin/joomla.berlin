<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsModelScanalerts extends F0FModel
{
	public function buildQuery($overrideLimits = false)
	{
		$db = $this->getDbo();
		$query = parent::buildQuery($overrideLimits)
			->clear('select')
			->clear('order')
			->select(array(
				$db->quoteName('admintools_scanalert_id'),
				'IF(' . $db->quoteName('diff') . ' != "",0,1) AS ' . $db->quoteName('newfile'),
				'IF(' . $db->quoteName('diff') . ' LIKE "###SUSPICIOUS FILE###%",1,0) AS ' . $db->quoteName('suspicious'),
				'IF(' . $db->quoteName('diff') . ' != "",' .
				'IF(' . $db->quoteName('diff') . ' LIKE "###SUSPICIOUS FILE###%",' .
				$db->quote('0-suspicious') . ',' . $db->quote('2-modified') . ')'
				. ',' . $db->quote('1-new') . ') AS ' . $db->quoteName('filestatus'),
				$db->quoteName('path'),
				$db->quoteName('threat_score'),
				$db->quoteName('acknowledged'),
			));

		$search = $this->getState('search', '');
		if ($search)
		{
			$query->where($db->qn('path') . ' LIKE ' . $db->q('%' . $search . '%'));
		}

		$status = $this->getState('status', '');
		switch ($status)
		{
			case 'new':
				$query->where('IF(' . $db->quoteName('diff') . ' != "",0,1) = ' . $db->q(1));
				break;

			case 'suspicious':
				$query->where('IF(' . $db->quoteName('diff') . ' LIKE "###SUSPICIOUS FILE###%",1,0)  = ' . $db->q(1));
				break;

			case 'modified':
				$query->where('IF(' . $db->quoteName('diff') . ' != "",0,1) = ' . $db->q(0));
				$query->where('IF(' . $db->quoteName('diff') . ' LIKE "###SUSPICIOUS FILE###%",1,0)  = ' . $db->q(0));
				break;
		}

		$safe = $this->getState('safe', '');
		if (is_numeric($safe) && ($safe != '-1'))
		{
			$query->where($db->qn('acknowledged') . ' = ' . $db->q($safe));
		}

		if (!$overrideLimits)
		{
			$order = $this->getState('filter_order', null, 'cmd');
			$dir = $this->getState('filter_order_Dir', 'ASC', 'cmd');
			if (!in_array($order, array('path', 'threat_score', 'acknowledged', 'filestatus', 'newfile', 'suspcious')))
			{
				$order = 'threat_score';
				$dir = 'DESC';
			}
			$query->order($db->quoteName($order) . ' ' . $dir);
		}

		return $query;
	}
}