<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsModelTwofactor extends F0FModel
{
	/**
	 * Generates a new two-factor authentication secret key
	 *
	 * @return boolean False if the Google Authenticator library is not present
	 */
	public function generateTwoFactorAuthenticationSecret()
	{
		$ga = new F0FEncryptTotp();
		$secret = $ga->generateSecret();

		$model = F0FModel::getTmpInstance('Wafconfig', 'AdmintoolsModel');
		$config = $model->getConfig();
		$config['twofactorauth'] = 0;
		$config['twofactorauth_secret'] = $secret;
		$model->saveConfig($config);

		$this->resetPanic();

		return true;
	}

	/**
	 * Get a QR code URL to set up the authenticator
	 *
	 * @return boolean
	 */
	public function getQRCodeURL()
	{
		$userData = $this->getFakeUser();

		$model = F0FModel::getTmpInstance('Wafconfig', 'AdmintoolsModel');
		$config = $model->getConfig();
		$secret = $config['twofactorauth_secret'];

		if (empty($secret))
		{
			return false;
		}

		$ga = new F0FEncryptTotp();

		return $ga->getUrl($userData['user'], $userData['hostname'], $secret);
	}

	/**
	 * Validates a security code. If the code is valid, the two factor
	 * authentication is enabled.
	 *
	 * @param string $code
	 *
	 * @return boolean
	 */
	public function validateAndEnable($code)
	{
		$model = F0FModel::getTmpInstance('Wafconfig', 'AdmintoolsModel');
		$config = $model->getConfig();
		$secret = $config['twofactorauth_secret'];

		$ga = new F0FEncryptTotp();
		if ($ga->checkCode($secret, $code))
		{
			$config['twofactorauth'] = 1;
			$model->saveConfig($config);

			return true;
		}
		else
		{
			$config['twofactorauth'] = 0;
			$model->saveConfig($config);

			return false;
		}
	}

	/**
	 * Resets the 16-digit emergency (panic) code
	 */
	public function resetPanic()
	{
		$panic = '';
		for ($i = 1; $i <= 16; $i++)
		{
			$c = rand(0, 9);
			$panic .= $c;
			if (!($i % 4))
			{
				$panic .= '-';
			}
		}
		$panic = substr($panic, 0, -1);

		$model = F0FModel::getTmpInstance('Wafconfig', 'AdmintoolsModel');
		$config = $model->getConfig();
		$config['twofactorauth_panic'] = $panic;
		$model->saveConfig($config);
	}

	/**
	 * Google Authenticator requires a user and a hostname. Well, provide a fake
	 * pair based on the site's URL!
	 *
	 * @return array
	 */
	public function getFakeUser()
	{
		$myURI = JURI::getInstance();
		$path = $myURI->getPath();
		$path_parts = explode('/', $path);
		$path_parts = array_slice($path_parts, 0, count($path_parts) - 2);
		$path = implode('/', $path_parts);
		$myURI->setPath($path);
		// Unset any query parameters
		$myURI->setQuery('');

		$host = $myURI->toString();
		$host = substr($host, strpos($host, '://') + 3);

		$path = trim($path, '/');

		if (empty($path))
		{
			$path = 'joomla';
		}
		else
		{
			$path = 'joomla_' . $path;
		}

		return array(
			'user'     => $path,
			'hostname' => $host
		);
	}
}