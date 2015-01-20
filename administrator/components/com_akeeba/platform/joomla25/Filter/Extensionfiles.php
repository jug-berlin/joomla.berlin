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
 * Joomla! Extensions exclusion filter (files part)
 */
class Extensionfiles extends Base
{
	public function __construct()
	{
		$this->object = 'file';
		$this->subtype = 'all';
		$this->method = 'direct';
		$this->filter_name = 'Extensionfiles';

		if (Factory::getKettenrad()->getTag() == 'restorepoint')
		{
			$this->enabled = false;
		}

		if (!defined('_JEXEC'))
		{
			$this->enabled = false;
		}

		if (empty($this->filter_name))
		{
			$this->filter_name = strtolower(basename(__FILE__, '.php'));
		}
		parent::__construct();
	}

	public function hasFilters()
	{
		if (!$this->enabled)
		{
			return false;
		}

		// Reset the filters
		$this->filter_data = array();

		// Add filters for modules
		$admin = substr(JPATH_ADMINISTRATOR, strlen(JPATH_SITE) + 1);
		$filterObjects = array(
			array('modules', 'frontend', 'modules'),
			array('modules', 'backend', $admin . '/modules'),
		);
		foreach ($filterObjects as $filterObject)
		{
			$this->createFilterEntry($filterObject[0], $filterObject[1], $filterObject[2]);
		}

		// Plugins are a special case. They're stored in the front end, but
		// there are subdirectories based on type. Extensions can define custom types,
		// therefore there is no predetermined set of directories. We have to scan for it.
		$this->createPluginFilters();

		return parent::hasFilters();
	}

	private function createFilterEntry($filter_class, $root, $base_path)
	{
		// Get the items of the specified filter class
		$filter = Factory::getFilterObject($filter_class);
		$items = $filter->getFilters($root);
		if (!empty($items))
		{
			// Add a directory exclusion for each item
			foreach ($items as $item)
			{
				$this->set('[SITEROOT]', $base_path . '/' . $item . '.php');
				$this->set('[SITEROOT]', $base_path . '/' . $item . '.xml');
			}
		}
	}

	private function createPluginFilters()
	{
		// Base plugins path
		$plugins_path = 'plugins';

		// Get all plug-in filters
		$filter = Factory::getFilterObject('plugins');
		$types = $filter->getFilters(null);
		if (!empty($types))
		{
			// Loop all plug-in types
			foreach ($types as $type => $items)
			{
				if (!empty($items))
				{
					// Base path for this plugin type
					$base_path = $plugins_path . '/' . $type;
					// Loop all plugins of this type and add a directory exclusion
					foreach ($items as $item)
					{
						$this->set('[SITEROOT]', $base_path . '/' . $item . '.php');
						$this->set('[SITEROOT]', $base_path . '/' . $item . '.xml');
					}
				}
			}
		}
	}
}