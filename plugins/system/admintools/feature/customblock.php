<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

class AtsystemFeatureCustomblock extends AtsystemFeatureAbstract
{
	/**
	 * Shows the Admin Tools custom block message
	 */
	public function onAfterRoute()
	{
		$session = JFactory::getSession();

		if ($session->get('block', false, 'com_admintools'))
		{
			// This is an underhanded way to short-circuit Joomla!'s internal router.
			$this->input->set('option', 'com_admintools');

			if (class_exists('JRequest'))
			{
				JRequest::set(array(
					'option' => 'com_admintools'
				), 'get', true);
			}
		}
	}
} 