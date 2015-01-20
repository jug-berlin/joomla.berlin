<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protection against direct access
defined('AKEEBAENGINE') or die();

/**
 * Subdirectories exclusion filter. Excludes temporary, cache and backup output
 * directories' contents from being backed up.
 */
class AEFilterPlatformFiles extends AEAbstractFilter
{
	public function __construct()
	{
		$this->object = 'file';
		$this->subtype = 'all';
		$this->method = 'direct';
		$this->filter_name = 'PlatformFiles';

		// We take advantage of the filter class magic to inject our custom filters
		$allFiles = explode('|', AEFactory::getConfiguration()->get('akeeba.basic.exclude_files'));
		$this->filter_data['[SITEROOT]'] = array_unique($allFiles);

		parent::__construct();
	}
}