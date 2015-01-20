<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsModelScans extends F0FModel
{
	function onProcessList(&$resultArray)
	{
		parent::onProcessList($resultArray);

		// Don't process an empty list
		if (empty($resultArray))
		{
			return;
		}

		// Get the scan_id's and initialise the special fields
		$scanids = array();
		$map = array();
		foreach ($resultArray as $index => &$row)
		{
			$scanids[] = $row->id;
			$map[$row->id] = $index;
			$row->files_new = 0;
			$row->files_modified = 0;
		}

		// Fetch the stats for the IDs at hand
		$ids = implode(',', $scanids);
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select(array(
				$db->quoteName('scan_id'),
				'(' . $db->quoteName('diff') . ' = ' . $db->quote('') . ') AS ' . $db->quoteName('newfile'),
				'COUNT(*) AS ' . $db->quoteName('count')
			))
			->from($db->quoteName('#__admintools_scanalerts'))
			->where($db->quoteName('scan_id') . ' IN (' . $ids . ')')
			->group(array(
				$db->quoteName('scan_id'),
				$db->quoteName('newfile'),
			));
		$db->setQuery($query);
		$alertstats = $db->loadObjectList();

		$query = $db->getQuery(true)
			->select(array(
				$db->quoteName('scan_id'),
				'COUNT(*) AS ' . $db->quoteName('count')
			))
			->from($db->quoteName('#__admintools_scanalerts'))
			->where(
				$db->quoteName('scan_id') . ' IN (' . $ids . ')'
			)->where(
				'(' . $db->quoteName('threat_score') . ' > ' . $db->quote('0') . ')'
			)->group(array(
				$db->quoteName('scan_id')
			));
		$db->setQuery($query);
		$suspiciousstats = $db->loadObjectList();

		// Update the $resultArray with the loaded stats
		if (!empty($alertstats))
		{
			foreach ($alertstats as $stat)
			{
				$idx = $map[$stat->scan_id];
				if ($stat->newfile)
				{
					$resultArray[$idx]->files_new = $stat->count;
				}
				else
				{
					$resultArray[$idx]->files_modified = $stat->count;
				}
			}
		}

		if (!empty($suspiciousstats))
		{
			foreach ($suspiciousstats as $stat)
			{
				$idx = $map[$stat->scan_id];
				$resultArray[$idx]->files_suspicious = $stat->count;
			}
		}
	}

	function removeIncompleteScans()
	{
		$list1 = F0FModel::getTmpInstance('Scan', 'AdmintoolsModel')
			->status('fail')
			->profile_id(1)
			->getItemList();
		$list2 = F0FModel::getTmpInstance('Scan', 'AdmintoolsModel')
			->status('run')
			->profile_id(1)
			->getItemList();

		$list = array_merge($list1, $list2);
		unset($list1);
		unset($list2);

		if (!empty($list))
		{
			$ids = array();
			foreach ($list as $item)
			{
				$ids[] = $item->id;
			}
			$ids = implode(',', $ids);

			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->delete('#__admintools_scans')
				->where($db->quoteName('id') . ' IN (' . $ids . ')');
			$db->setQuery($query);
			$db->execute();

			$query = $db->getQuery(true)
				->delete('#__admintools_scanalerts')
				->where($db->quoteName('scan_id') . ' IN (' . $ids . ')');
			$db->setQuery($query);
			$db->execute();
		}
	}

	public function deleteScanResults()
	{
		if (is_array($this->id_list) && !empty($this->id_list))
		{
			$table = $this->getTable($this->table);
			foreach ($this->id_list as $id)
			{
				if (!$table->deleteScanResults($id))
				{
					$this->setError($table->getError());

					return false;
				}
			}
		}

		return true;
	}

    /**
     * Clears the table with files information
     *
     * @return bool
     */
    public function purgeFilesCache()
    {
        $db = $this->getDbo();

        // The best choice should be the TRUNCATE statement, however there isn't the proper function inside Joomla driver...
        $query = $db->getQuery(true)
                    ->delete($db->qn('#__admintools_filescache'));

        try{
            $result = $db->setQuery($query)->execute();
        }
        catch(Exception $e)
        {
            $result = false;
        }

        return $result;
    }

	/**
	 * Starts a new file scan
	 *
	 * @return array
	 */
	public function startScan()
	{
		if (!$this->scanEngineSetup())
		{
			return array(
				'status' => false,
				'error'  => 'Could not load the file scanning engine; please try reinstalling the component',
				'done'   => true
			);
		}

		AEPlatform::getInstance()->load_configuration(1);
		AECoreKettenrad::reset();
		AEUtilTempvars::reset(AKEEBA_BACKUP_ORIGIN);

		$configOverrides['volatile.core.finalization.action_handlers'] = array(
			new AEFinalizationEmail()
		);
		$configOverrides['volatile.core.finalization.action_queue'] = array(
			'remove_temp_files',
			'update_statistics',
			'update_filesizes',
			'apply_quotas',
			'send_scan_email'
		);

		// Apply the configuration overrides, please
		$platform = AEPlatform::getInstance();
		$platform->configOverrides = $configOverrides;

		$kettenrad = AEFactory::getKettenrad();
		$options = array(
			'description' => '',
			'comment'     => '',
			'jpskey'      => ''
		);
		$kettenrad->setup($options);

		AEUtilLogger::openLog(AKEEBA_BACKUP_ORIGIN);
		AEUtilLogger::WriteLog(true, '');

		$kettenrad->tick();
		$kettenrad->tick();

		AECoreKettenrad::save(AKEEBA_BACKUP_ORIGIN);

		return $this->parseScanArray($kettenrad->getStatusArray());
	}

	/**
	 * Steps the file scan
	 *
	 * @return array
	 */
	public function stepScan()
	{
		if (!$this->scanEngineSetup())
		{
			return array(
				'status' => false,
				'error'  => 'Could not load the file scanning engine; please try reinstalling the component',
				'done'   => true
			);
		}

		$kettenrad = AECoreKettenrad::load(AKEEBA_BACKUP_ORIGIN);

		$kettenrad->tick();

		AECoreKettenrad::save(AKEEBA_BACKUP_ORIGIN);

		return $this->parseScanArray($kettenrad->getStatusArray());
	}

	/**
	 * Sets up the environment to start or continue a file scan
	 *
	 * @return bool
	 */
	private function scanEngineSetup()
	{
		// Constants required for the Akeeba Engine
		define('AKEEBAROOT', JPATH_ADMINISTRATOR . '/components/com_admintools/akeeba');
		define('AKEEBAENGINE', 1); // Enable Akeeba Engine
		define('AKEEBAPLATFORM', 'jfscan'); // Joomla! file scanner
		define('AKEEBA_PROFILE', 1);
		define('AKEEBA_BACKUP_ORIGIN', 'backend');

		// Unset time limits
		$safe_mode = true;
		if (function_exists('ini_get'))
		{
			$safe_mode = ini_get('safe_mode');
		}
		if (!$safe_mode && function_exists('set_time_limit'))
		{
			@set_time_limit(0);
		}

		// Load Akeeba Engine's factory class
		$factoryPath = AKEEBAROOT . '/factory.php';
		if (!file_exists($factoryPath))
		{
			return false;
		}
		else
		{
			require_once $factoryPath;
		}

		return true;
	}

	private function parseScanArray($array)
	{
		$kettenrad = AEFactory::getKettenrad();
		$kettenrad->resetWarnings();

		if (($array['HasRun'] != 1) && (empty($array['Error'])))
		{
			// Still have work to do
			return array(
				'status' => true,
				'done'   => false,
				'error'  => ''
			);
		}
		elseif (!empty($array['Error']))
		{
			// Error!
			return array(
				'status' => false,
				'done'   => true,
				'error'  => $array['Error']
			);
		}
		else
		{
			// All done
			AEUtilTempvars::reset(AKEEBA_BACKUP_ORIGIN);

			return array(
				'status' => true,
				'done'   => true,
				'error'  => ''
			);
		}
	}

	public function buildQuery($overrideLimits = false)
	{
		$query = parent::buildQuery($overrideLimits);

		if (!$overrideLimits)
		{
			$db = $this->getDbo();
			$table = $this->getTable();
			$tableName = $table->getTableName();
			$tableKey = $table->getKeyName();

			$order = $this->getState('filter_order', null, 'cmd');
			if (!in_array($order, array_keys($this->getTable()->getData())))
			{
				$order = $tableKey;
			}
			$dir = $this->getState('filter_order_Dir', 'DESC', 'cmd');
			$query->order($db->qn($order) . ' ' . $dir);
		}

		return $query;
	}
}