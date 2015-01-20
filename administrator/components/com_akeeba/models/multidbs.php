<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 *
 * @since     1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;

/**
 * Multiple databases definition View
 *
 */
class AkeebaModelMultidbs extends F0FModel
{

	/**
	 * Returns an array containing a list of database definitions
	 *
	 * @return array Array of definitions; The key contains the internal root name, the data is the database
	 *               configuration data
	 */
	public function get_databases()
	{
		// Get database inclusion filters
		$filter        = Factory::getFilterObject('multidb');
		$database_list = $filter->getInclusions('db');

		return $database_list;
	}

	/**
	 * Delete a database definition
	 *
	 * @param string $root The name of the database root key to remove
	 *
	 * @return bool True on success
	 */
	public function remove($root)
	{
		$filter  = Factory::getFilterObject('multidb');
		$success = $filter->remove($root, null);
		$filters = Factory::getFilters();
		if ($success)
		{
			$filters->save();
		}

		return $success;
	}

	/**
	 * Creates a new database definition
	 *
	 * @param string $root
	 * @param array  $data
	 *
	 * @return bool
	 */
	public function setFilter($root, $data)
	{
		$filter  = Factory::getFilterObject('multidb');
		$success = $filter->set($root, $data);
		$filters = Factory::getFilters();
		if ($success)
		{
			$filters->save();
		}

		return $success;
	}

	/**
	 * Tests the connectivity to a database
	 *
	 * @param array $data
	 *
	 * @return array Status array: 'status' is true on success, 'message' contains any error message while connecting
	 *               to the database
	 */
	public function test($data)
	{
		$db    = Factory::getDatabase($data);
		$error = $db->getErrorMsg();

		return array(
			'status'  => ($db->getErrorNum() <= 0),
			'message' => $error
		);
	}

	public function doAjax()
	{
		$action = $this->getState('action');
		$verb   = array_key_exists('verb', $action) ? $action['verb'] : null;

		$ret_array = array();

		switch ($verb)
		{
			// Set a filter (used by the editor)
			case 'set':
				$ret_array = $this->setFilter($action['root'], $action['data']);
				break;

			// Remove a filter (used by the editor)
			case 'remove':
				$ret_array = array('success' => $this->remove($action['root']));
				break;

			// Test connection (used by the editor)
			case 'test':
				$ret_array = $this->test($action['data']);
				break;
		}

		return $ret_array;
	}
}