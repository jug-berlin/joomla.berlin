<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 *
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license   GNU GPL version 3 or, at your option, any later version
 * @package   akeebaengine
 *
 */

namespace Akeeba\Engine\Filter;

// Protection against direct access
defined('AKEEBAENGINE') or die();

use Akeeba\Engine\Filter\Base;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;

/**
 * Joomla! Components exclusion filter
 */
class Components extends Base
{
	private $joomla16;

	public function __construct()
	{
		$this->object = 'components';
		$this->subtype = 'all';
		$this->method = 'direct';
		$this->filter_name = 'Components';

		if (empty($this->filter_name))
		{
			$this->filter_name = strtolower(basename(__FILE__, '.php'));
		}

		if (Factory::getKettenrad()->getTag() == 'restorepoint')
		{
			$this->enabled = false;
		}

		$this->joomla16 = !@file_exists(JPATH_SITE . '/includes/joomla.php');

		parent::__construct();
	}

	public function &getExtraSQL($root)
	{
		$empty = '';
		if ($root != '[SITEDB]')
		{
			return $empty;
		}

		$sql = '';
		$db = Factory::getDatabase();
		$this->getFilters(null); // Forcibly reload the filter data
		// Loop all components and add SQL statements
		if (!empty($this->filter_data))
		{
			foreach ($this->filter_data as $type => $items)
			{
				if (!empty($items))
				{
					// Make sure that DB only backups get the correct prefix
					$configuration = Factory::getConfiguration();
					$abstract = Factory::getEngineParamsProvider()->getScriptingParameter('db.abstractnames', 1);
					if ($abstract)
					{
						$prefix = '#__';
					}
					else
					{
						$prefix = $db->getPrefix();
					}

					foreach ($items as $item)
					{
						if (!$this->joomla16)
						{
							$sql .= 'DELETE FROM ' . $db->quoteName($prefix . 'components') .
								' WHERE ' . $db->quoteName('option') . ' = ' .
								$db->Quote($item) . ";\n";
						}
						else
						{
							$sql .= 'DELETE FROM ' . $db->quoteName($prefix . 'extensions') .
								' WHERE ' . $db->quoteName('element') . ' = ' .
								$db->Quote($item) . " AND " . $db->quoteName('type') . ' = ' .
								$db->Quote('component') . ";\n";
						}
						$sql .= 'DELETE FROM ' . $db->quoteName($prefix . 'menu') .
							' WHERE ' . $db->quoteName('type') . ' = ' .
							$db->Quote('component') . ' AND ' .
							$db->quoteName('link') . ' LIKE ' .
							$db->Quote('%option=' . $item . '%') . ";\n";
					}
				}
			}
		}

		return $sql;
	}
}