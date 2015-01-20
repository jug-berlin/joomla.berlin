<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsViewAdminuser extends F0FViewHtml
{
	private function randomFalseUsername()
	{
		$usernames = array(
			'42', 'clinteastwood', 'chucknorris', 'rantanplan', 'pinky',
			'brain', 'beavis', 'tux', 'larry', 'stevenseagal',
			'jeanclaudevandamme', 'jackiechan'
		);

		$id = 42;

		$dontadd = JFactory::getUser($id)->username;

		$ret = $dontadd;

		while ($ret == $dontadd)
		{
			$rand = rand(0, count($usernames) - 1);
			$ret = $usernames[$rand];
		}

		return $ret;
	}

	protected function onBrowse($tpl = null)
	{
		$model = $this->getModel();
		$this->hasDefaultAdmin = $model->hasDefaultAdmin();
		$this->getDefaultUsername = $model->getDefaultUsername();
		$this->fakeUsername = $this->randomFalseUsername();

		JHTML::_('behavior.framework', true);
	}
}