<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsTableBadword extends F0FTable
{
	public function __construct($table, $key, &$db)
	{
		parent::__construct('#__admintools_badwords', 'id', $db);
	}

	public function check()
	{
		if (!$this->word)
		{
			$this->setError(JText::_('ATOOLS_ERR_BADWORDS_NEEDS_WORD'));

			return false;
		}

		return true;
	}
}
