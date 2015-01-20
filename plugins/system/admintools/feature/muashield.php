<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

class AtsystemFeatureMuashield extends AtsystemFeatureAbstract
{
	protected $loadOrder = 330;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		if (!F0FPlatform::getInstance()->isFrontend())
		{
			return false;
		}

		if ($this->skipFiltering)
		{
			return false;
		}

		return ($this->cparams->getValue('muashield', 0) == 1);
	}

	/**
	 * Protects against a malicious User Agent string
	 */
	public function onAfterInitialise()
	{
		// Some PHP binaries don't set the $_SERVER array under all platforms
		if (!isset($_SERVER))
		{
			return;
		}

		if (!is_array($_SERVER))
		{
			return;
		}

		// Some user agents don't set a UA string at all
		if (!array_key_exists('HTTP_USER_AGENT', $_SERVER))
		{
			return;
		}

		$mua = $_SERVER['HTTP_USER_AGENT'];

		if (strstr($mua, '<?'))
		{
			$this->exceptionsHandler->blockRequest('muashield');
		}
	}
} 