<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protection against direct access
defined('AKEEBAENGINE') or die();

/**
 * Files exclusion filter based on regular expressions
 */
class AEFilterPlatformSystemcachefiles extends AEAbstractFilter
{
	function __construct()
	{
		$this->object = 'file';
		$this->subtype = 'all';
		$this->method = 'regex';

		if (empty($this->filter_name))
		{
			$this->filter_name = strtolower(basename(__FILE__, '.php'));
		}
		parent::__construct();

		$this->filter_data['[SITEROOT]'] = array(
			'#/Thumbs.db$#',
			'#^Thumbs.db$#',
			'#/.DS_Store$#i',
			'#^.DS_Store$#i'
		);
	}
}