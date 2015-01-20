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

// Protection against direct access
defined('AKEEBAENGINE') or die();

/**
 * Checks for runtime errors, ie Backup Timeout, timeout on post-processing etc etc
 */
class AliceCoreDomainFilesystem extends AliceCoreDomainAbstract
{
	public function __construct()
	{
		parent::__construct(40, 'filesystem', JText::_('ALICE_ANALYZE_FILESYSTEM'));
	}
}