<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsTableScanalert extends F0FTable
{
	public function publish($cid = null, $publish = 1, $user_id = 0)
	{
		JArrayHelper::toInteger($cid);
		$user_id = (int)$user_id;
		$publish = (int)$publish;
		$k = $this->_tbl_key;

		if (count($cid) < 1)
		{
			if ($this->$k)
			{
				$cid = array($this->$k);
			}
			else
			{
				$this->setError("No items selected.");

				return false;
			}
		}

		if (!$this->onBeforePublish($cid, $publish))
		{
			return false;
		}

		$query = $this->_db->getQuery(true)
			->update($this->_db->quoteName($this->_tbl))
			->set($this->_db->quoteName('acknowledged') . ' = ' . (int)$publish);

		$checkin = in_array('locked_by', array_keys($this->getProperties()));
		if ($checkin)
		{
			$query->where(
				' (' . $this->_db->quoteName('locked_by') .
				' = 0 OR ' . $this->_db->quoteName('locked_by') . ' = ' . (int)$user_id . ')',
				'AND'
			);
		}

		$cids = $this->_db->quoteName($k) . ' = ' .
			implode(' OR ' . $this->_db->quoteName($k) . ' = ', $cid);
		$query->where('(' . $cids . ')');

		$this->_db->setQuery((string)$query);
		if (!$this->_db->execute())
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		if (count($cid) == 1 && $checkin)
		{
			if ($this->_db->getAffectedRows() == 1)
			{
				$this->checkin($cid[0]);
				if ($this->$k == $cid[0])
				{
					$this->acknowledged = $publish;
				}
			}
		}
		$this->setError('');

		return true;
	}
}