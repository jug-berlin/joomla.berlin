<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 *
 * @since     3.2.5
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;

/**
 * Site Transfer Wizard
 */
class AkeebaModelStws extends F0FModel
{
	/**
	 * @var string The name of the Site Transfer Wizard profile
	 */
	private $stwProfileName = 'Site Transfer Wizard (do not rename)';

	/**
	 * Returns the numeric ID of the Site Transfer Wizard profile, or 0 if there
	 * is no STW profile created yet.
	 *
	 * @staticvar int $id Cached ID
	 * @return int The ID of the Site Transfer Wizard profile, or 0 if it doesn't exist
	 */
	public function getSTWProfileID()
	{
		static $id = -1;

		if ($id < 0)
		{
			$m        = F0FModel::getAnInstance('Profiles', 'AkeebaModel');
			$profiles = $m->getItemList(true);
			$id       = 0;
			foreach ($profiles as $profile)
			{
				if ($profile->description == $this->stwProfileName)
				{
					$id = $profile->id;
					break;
				}
			}
		}

		return $id;
	}

	/**
	 * Makes or updates the STW profile, based on the state information passed
	 */
	public function makeOrUpdateProfile()
	{
		$method     = $this->getState('method');
		$oldprofile = $this->getState('oldprofile');

		switch ($method)
		{
			case 'none':
				$id = $this->getSTWProfileID();
				if ( !$id)
				{
					return $this->createNewSTWProfile(null);
				}
				else
				{
					$session = JFactory::getSession();
					$session->set('profile', $id, 'akeeba');
					Platform::getInstance()->load_configuration($id);
				}

				return true;
				break;

			case 'copyfrom':
				return $this->createNewSTWProfile($oldprofile);
				break;

			case 'blank':
				return $this->createNewSTWProfile(null);
				break;
		}
	}

	/**
	 * Apply the transfer settings to the profile. Returns true if the connection
	 * and uploading a test file works properly, an error message otherwise
	 *
	 * @return bool|string
	 */
	public function applyTransferSettings()
	{
		// Get state variables
		$method    = $this->getState('method');
		$hostname  = $this->getState('hostname');
		$port      = $this->getState('port');
		$username  = $this->getState('username');
		$password  = $this->getState('password');
		$directory = $this->getState('directory');
		$passive   = $this->getState('passive');
		$livesite  = $this->getState('livesite');

		// Fix the hostname, in case the user added a protocol (grr...)
		$protoPos = strpos($hostname, '://');
		if ($protoPos !== false)
		{
			$proto = substr($hostname, 0, $protoPos - 1);
			$proto = strtolower($proto);
			switch ($proto)
			{
				case 'ftp':
					$method = 'ftp';
					break;
				case 'ftps':
				case 'ftpes':
					$method = 'ftps';
					break;
				case 'sftp':
					$method = 'sftp';
					break;
			}
			$hostname = substr($hostname, $protoPos + 3);
		}

		// Assign default configuration variables
		$config = Factory::getConfiguration();
		$config->set('akeeba.basic.backup_type', 'full');
		$config->set('akeeba.advanced.postproc_engine', 'none');
		if ((substr($livesite, 0, 7) != 'http://') && (substr($livesite, 0, 8) != 'https://'))
		{
			$livesite = 'http://' . $livesite;
		}
		$livesite = rtrim($livesite, "/\n\r ");
		$config->set('akeeba.stw.livesite', $livesite);

		// Apply the transfer settings
		switch ($method)
		{
			case 'ftp':
			case 'ftps':
				$config->set('akeeba.advanced.archiver_engine', 'directftp');
				$config->set('engine.archiver.directftp.host', $hostname);
				$config->set('engine.archiver.directftp.port', $port);
				$config->set('engine.archiver.directftp.user', $username);
				$config->set('engine.archiver.directftp.pass', $password);
				$config->set('engine.archiver.directftp.initial_directory', $directory);
				$config->set('engine.archiver.directftp.ftps', ($method == 'ftps' ? 1 : 0));
				$config->set('engine.archiver.directftp.passive_mode', ($passive ? 1 : 0));
				break;

			case 'sftp':
				$config->set('akeeba.advanced.archiver_engine', 'directsftp');
				$config->set('engine.archiver.directsftp.host', $hostname);
				$config->set('engine.archiver.directsftp.port', $port);
				$config->set('engine.archiver.directsftp.user', $username);
				$config->set('engine.archiver.directsftp.pass', $password);
				$config->set('engine.archiver.directsftp.initial_directory', $directory);
				break;
		}

		Platform::getInstance()->save_configuration();

		// Connection test
		switch ($method)
		{
			case 'ftp':
			case 'ftps':
				$config = array(
					'host'    => $config->get('engine.archiver.directftp.host'),
					'port'    => $config->get('engine.archiver.directftp.port'),
					'user'    => $config->get('engine.archiver.directftp.user'),
					'pass'    => $config->get('engine.archiver.directftp.pass'),
					'initdir' => $config->get('engine.archiver.directftp.initial_directory'),
					'usessl'  => $config->get('engine.archiver.directftp.ftps'),
					'passive' => $config->get('engine.archiver.directftp.passive_mode')
				);

				$test = new \Akeeba\Engine\Archiver\Directftp();
				$test->initialize('', $config);
				$errors = $test->getError();
				break;

			case 'sftp':
				$config = array(
					'host'    => $config->get('engine.archiver.directsftp.host'),
					'port'    => $config->get('engine.archiver.directsftp.port'),
					'user'    => $config->get('engine.archiver.directsftp.user'),
					'pass'    => $config->get('engine.archiver.directsftp.pass'),
					'initdir' => $config->get('engine.archiver.directsftp.initial_directory')
				);

				$test = new \Akeeba\Engine\Archiver\Directsftp();
				$test->initialize('', $config);
				$errors = $test->getError();
				break;
		}

		// Check for connection errors
		if (empty($errors) || $test->connect_ok)
		{
			$result = true;
		}
		else
		{
			$result = JText::_('STW_LBL_CONNECTION_ERR_CONNECTION') . ' ' . $errors;

			return $result;
		}

		// Test upload file
		$file   = JPATH_ROOT . '/media/com_akeeba/icons/ok_small.png';
		$result = $test->addFileRenamed($file, 'akeeba_connection_test.png');

		if ( !$result)
		{
			$result = JText::_('STW_LBL_CONNECTION_ERR_UPLOAD') . ' ' . $result;

			return $result;
		}

		return true;
	}

	public function getTransferSettings()
	{
		$config = Factory::getConfiguration();

		$ret = array(
			'method'    => 'ftp',
			'hostname'  => 'ftp.example.com',
			'port'      => 21,
			'username'  => '',
			'password'  => '',
			'directory' => '/public_html',
			'passive'   => 1,
			'livesite'  => 'http://www.example.com'
		);

		switch ($config->get('akeeba.advanced.archiver_engine', 'jpa'))
		{
			case 'directftp':
				$ret = array(
					'method'    => ($config->get('engine.archiver.directftp.ftps', 0)) ? 'ftps' : 'ftp',
					'hostname'  => $config->get('engine.archiver.directftp.host', ''),
					'port'      => $config->get('engine.archiver.directftp.port', 21),
					'username'  => $config->get('engine.archiver.directftp.user', ''),
					'password'  => $config->get('engine.archiver.directftp.pass', ''),
					'directory' => $config->get('engine.archiver.directftp.initial_directory', ''),
					'passive'   => $config->get('engine.archiver.directftp.passive_mode', 1),
					'livesite'  => $config->get('akeeba.stw.livesite', '')
				);
				break;

			case 'directsftp':
				$ret = array(
					'method'    => 'sftp',
					'hostname'  => $config->get('engine.archiver.directsftp.host', ''),
					'port'      => $config->get('engine.archiver.directsftp.port', 21),
					'username'  => $config->get('engine.archiver.directsftp.user', ''),
					'password'  => $config->get('engine.archiver.directsftp.pass', ''),
					'directory' => $config->get('engine.archiver.directsftp.initial_directory', ''),
					'livesite'  => $config->get('akeeba.stw.livesite', '')
				);
				break;
		}

		return (object)$ret;
	}


	private function createNewSTWProfile($copyfrom = null)
	{
		// Get the Profiles model and fetch the STW profile ID
		$m  = F0FModel::getTmpInstance('Profiles', 'AkeebaModel');
		$id = $this->getSTWProfileID();

		if ($id != 0)
		{
			Platform::getInstance()->load_configuration($id);
		}

		// Create a new profile
		$data = array(
			'id'            => $id,
			'description'   => $this->stwProfileName,
			'configuration' => '',
			'filters'       => serialize(array())
		);

		// Inherit settings from another profile
		if (($id != 0) && !empty($copyfrom))
		{
			$m->setId($copyfrom);
			if ($m->checkID())
			{
				$oldProfile            = $m->getProfile();
				$data['configuration'] = $oldProfile->configuration;
				$data['filters']       = $oldProfile->filters;
			}
		}

		// Save the new/changed profile
		$m->setId($id);
		$result = $m->save($data);

		if ($result)
		{
			// If the save was successful, switch the active profile
			$newRecord = $m->getSavedTable();
			$session   = JFactory::getSession();
			$session->set('profile', $newRecord->id, 'akeeba');
		}

		return $result;
	}
}