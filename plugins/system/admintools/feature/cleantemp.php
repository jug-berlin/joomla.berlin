<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

class AtsystemFeatureCleantemp extends AtsystemFeatureAbstract
{
	protected $loadOrder = 650;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return ($this->params->get('cleantemp', 0) == 1);
	}

	public function onAfterInitialise()
	{
		$minutes = (int)$this->params->get('cleantemp_freq', 0);

		if ($minutes <= 0)
		{
			return;
		}

		$lastJob = $this->getTimestamp('clean_temp');
		$nextJob = $lastJob + $minutes * 60;

		JLoader::import('joomla.utilities.date');
		$now = new JDate();

		if ($now->toUnix() >= $nextJob)
		{
			$this->setTimestamp('clean_temp');
			$this->tempDirectoryCleanup();
		}
	}

	/**
	 * Cleans up the temporary director
	 */
	private function tempDirectoryCleanup()
	{
		$file = JPATH_ADMINISTRATOR . '/components/com_admintools/models/cleantmp.php';

		if (@file_exists($file))
		{
			include_once($file);

			$model = new AdmintoolsModelCleantmp();
			$model->startScanning(); // This also runs the first batch of deletions
			$model->run(); // and this runs more deletions until the time is up
		}
	}
}