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
class AEFilterPlatformRefolders extends AEAbstractFilter
{
	function __construct()
	{
		$this->object = 'dir';
		$this->subtype = 'all';
		$this->method = 'regex';

		if (empty($this->filter_name))
		{
			$this->filter_name = strtolower(basename(__FILE__, '.php'));
		}
		parent::__construct();

		$this->filter_data['[SITEROOT]'] = array(
			"!#^(administrator|cli|components|images|includes|language|libraries|media|modules|plugins|templates|)#"
		);
	}
}