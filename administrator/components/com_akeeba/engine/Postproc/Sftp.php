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

namespace Akeeba\Engine\Postproc;

// Protection against direct access
defined('AKEEBAENGINE') or die();

use Psr\Log\LogLevel;
use Akeeba\Engine\Factory;

class Sftp extends Base
{
	public function __construct()
	{
		$this->can_delete = true;
		$this->can_download_to_browser = false;
		$this->can_download_to_file = true;
	}

	public function processPart($absolute_filename, $upload_as = null)
	{
		// Retrieve engine configuration data
		$config = Factory::getConfiguration();

		$host = $config->get('engine.postproc.sftp.host', '');
		$port = $config->get('engine.postproc.sftp.port', 22);
		$user = $config->get('engine.postproc.sftp.user', '');
		$pass = $config->get('engine.postproc.sftp.pass', 0);
		$directory = $config->get('volatile.postproc.directory', null);

		if (empty($directory))
		{
			$directory = $config->get('engine.postproc.sftp.initial_directory', '');
		}

		// You can't fix stupid, but at least you get to shout at them
		if (strtolower(substr($host, 0, 7)) == 'sftp://')
		{
			Factory::getLog()->log(LogLevel::WARNING, 'YOU ARE *** N O T *** SUPPOSED TO ENTER THE sftp:// PROTOCOL PREFIX IN THE FTP HOSTNAME FIELD OF THE Upload to Remote SFTP POST-PROCESSING ENGINE.');
			Factory::getLog()->log(LogLevel::WARNING, 'I am trying to fix your bad configuration setting, but the backup might fail anyway. You MUST fix this in your configuration.');
			$host = substr($host, 7);
		}


		// Process the initial directory
		$directory = '/' . ltrim(trim($directory), '/');

		// Parse tags
		$directory = Factory::getFilesystemTools()->replace_archive_name_variables($directory);
		$config->set('volatile.postproc.directory', $directory);

		// Connect to the SFTP server
		Factory::getLog()->log(LogLevel::DEBUG, __CLASS__ . ':: Connecting to remote SFTP');

		$connection = null;
		$sftphandle = null;

		if (!function_exists('ssh2_connect'))
		{
			$this->setWarning("Your web server does not have the SSH2 PHP module, therefore can not connect and upload archives to SFTP servers.");

			return false;
		}

		$connection = ssh2_connect($host, $port);

		if ($connection === false)
		{
			$this->setWarning("Invalid SFTP hostname or port ($host:$port) or the connection is blocked by your web server's firewall.");

			return false;
		}

		Factory::getLog()->log(LogLevel::DEBUG, __CLASS__ . ':: Logging in');
		if (!ssh2_auth_password($connection, $user, $pass))
		{
			$this->setWarning('Could not authenticate access to SFTP server; check your username and password.');

			return false;
		}

		Factory::getLog()->log(LogLevel::DEBUG, __CLASS__ . ':: Establishing an SFTP connection');
		$sftphandle = ssh2_sftp($connection);

		if ($sftphandle === false)
		{
			$this->setWarning("Your SSH server does not allow SFTP connections");

			return false;
		}

		// Change to initial directory
		Factory::getLog()->log(LogLevel::DEBUG, __CLASS__ . ':: Checking the initial directory');
		if (!$this->_sftp_chdir($directory, $sftphandle))
		{
			$this->setWarning("Invalid initial directory $directory for the remote SFTP server");

			return false;
		}

		$realdir = substr($directory, -1) == '/' ? substr($directory, 0, strlen($directory) - 1) : $directory;
		$basename = empty($upload_as) ? basename($absolute_filename) : $upload_as;
		$realname = $realdir . '/' . $basename;

		// Store the absolute remote path in the class property
		$this->remote_path = $realname;

		Factory::getLog()->log(LogLevel::DEBUG, __CLASS__ . ":: Starting SFTP upload of $absolute_filename");
		$fp = @fopen("ssh2.sftp://{$sftphandle}$realname", 'w');

		if ($fp === false)
		{
			$this->setWarning("Could not open remote SFTP file $realname for writing");

			return false;
		}

		$localfp = @fopen($absolute_filename, 'rb');

		if ($localfp === false)
		{
			$this->setWarning("Could not open local file $absolute_filename for reading");
			@fclose($fp);

			return false;
		}

		$res = true;

		while (!feof($localfp) && ($res !== false))
		{
			$buffer = @fread($localfp, 65567);
			$res = @fwrite($fp, $buffer);
		}

		@fclose($fp);
		@fclose($localfp);

		ssh2_exec($connection, 'exit;');
		$connection = null;

		if ($res === false)
		{
			// If the file was unreadable, just skip it...
			if (is_readable($absolute_filename))
			{
				$this->setWarning('Uploading ' . $realname . ' has failed.');

				return false;
			}
			else
			{
				$this->setWarning('Uploading ' . $realname . ' has failed because the source file is unreadable.');

				return true;
			}
		}

		return true;
	}

	public function delete($path)
	{
		// Retrieve engine configuration data
		$config = Factory::getConfiguration();

		$host = $config->get('engine.postproc.sftp.host', '');
		$port = $config->get('engine.postproc.sftp.port', 21);
		$user = $config->get('engine.postproc.sftp.user', '');
		$pass = $config->get('engine.postproc.sftp.pass', 0);

		$directory = dirname($path);

		// Connect to the FTP server
		Factory::getLog()->log(LogLevel::DEBUG, __CLASS__ . '::delete() -- Connecting to remote SFTP');

		$connection = null;
		$sftphandle = null;

		if (!function_exists('ssh2_connect'))
		{
			$this->setWarning("Your web server does not have the SSH2 PHP module, therefore can not connect and upload archives to SFTP servers.");

			return false;
		}

		$connection = ssh2_connect($host, $port);

		if ($connection === false)
		{
			$this->setWarning("Invalid SFTP hostname or port ($host:$port) or the connection is blocked by your web server's firewall.");

			return false;
		}

		if (!ssh2_auth_password($connection, $user, $pass))
		{
			$this->setWarning('Could not authenticate access to SFTP server; check your username and password.');

			return false;
		}

		$sftphandle = ssh2_sftp($connection);

		if ($sftphandle === false)
		{
			$this->setWarning("Your SSH server does not allow SFTP connections");

			return false;
		}

		// Change to initial directory
		if (!$this->_sftp_chdir($directory, $sftphandle))
		{
			$this->setWarning("Invalid initial directory $directory for the remote SFTP server");

			return false;
		}

		try
		{
			$res = @ssh2_sftp_unlink($sftphandle, $path);
		}
		catch (\Exception $e)
		{
			// Funny how PHP dies without returning false if you don't use a try/catch statement, eh?
			$res = false;

			$this->setWarning($e->getMessage());
		}

		ssh2_exec($connection, 'exit;');
		$connection = null;

		if (!$res)
		{
			$this->setWarning('Deleting ' . $path . ' has failed.');

			return false;
		}
		else
		{
			return true;
		}
	}

	public function downloadToFile($remotePath, $localFile, $fromOffset = null, $length = null)
	{
		// Retrieve engine configuration data
		$config = Factory::getConfiguration();

		$host = $config->get('engine.postproc.sftp.host', '');
		$port = $config->get('engine.postproc.sftp.port', 21);
		$user = $config->get('engine.postproc.sftp.user', '');
		$pass = $config->get('engine.postproc.sftp.pass', 0);

		$directory = dirname($remotePath);

		// Connect to the FTP server
		Factory::getLog()->log(LogLevel::DEBUG, __CLASS__ . '::delete() -- Connecting to remote SFTP');

		$connection = null;
		$sftphandle = null;

		if (!function_exists('ssh2_connect'))
		{
			$this->setWarning("Your web server does not have the SSH2 PHP module, therefore can not connect and upload archives to SFTP servers.");

			return false;
		}

		$connection = ssh2_connect($host, $port);

		if ($connection === false)
		{
			$this->setWarning("Invalid SFTP hostname or port ($host:$port) or the connection is blocked by your web server's firewall.");

			return false;
		}

		if (!ssh2_auth_password($connection, $user, $pass))
		{
			$this->setWarning('Could not authenticate access to SFTP server; check your username and password.');

			return false;
		}

		$sftphandle = ssh2_sftp($connection);

		if ($sftphandle === false)
		{
			$this->setWarning("Your SSH server does not allow SFTP connections");

			return false;
		}

		// Change to initial directory
		if (!$this->_sftp_chdir($directory, $sftphandle))
		{
			$this->setWarning("Invalid initial directory $directory for the remote SFTP server");

			return false;
		}


		$fp = @fopen("ssh2.sftp://{$sftphandle}$remotePath", 'rb');

		if ($fp === false)
		{
			$this->setWarning("Could not open remote SFTP file $remotePath for reading");

			return false;
		}

		$localfp = @fopen($localFile, 'wb');

		if ($localfp === false)
		{
			$this->setWarning("Could not open local file $localFile for writing");
			@fclose($fp);

			return false;
		}

		$res = true;

		while (!feof($fp) && ($res !== false))
		{
			$buffer = @fread($fp, 65567);
			$res = @fwrite($localfp, $buffer);
		}

		@fclose($fp);
		@fclose($localfp);

		ssh2_exec($connection, 'exit;');
		$connection = null;

		if ($res === false)
		{
			$this->setWarning("Downloading $remotePath has failed.");

			return false;
		}

		return true;
	}

	/**
	 * Changes to the requested directory in the remote server. You give only the
	 * path relative to the initial directory and it does all the rest by itself,
	 * including doing nothing if the remote directory is the one we want. If the
	 * directory doesn't exist, it creates it.
	 *
	 * @param   string   $dir
	 * @param   resource $sftphandle
	 *
	 * @return  boolean
	 */
	protected function  _sftp_chdir($dir, &$sftphandle)
	{
		// Calculate "real" (absolute) SFTP path

		$result = @ssh2_sftp_stat($sftphandle, $dir);

		if ($result === false)
		{
			// The directory doesn't exist, let's try to create it...
			if (!$this->_makeDirectory($dir, $sftphandle))
			{
				return false;
			}
		}

		// Update the private "current remote directory" variable
		return true;
	}

	/**
	 * Creates a nested directory structure on the remote SFTP server
	 *
	 * @param   string   $dir
	 * @param   resource $sftphandle
	 *
	 * @return  boolean
	 */
	protected function  _makeDirectory($dir, &$sftphandle)
	{
		$alldirs = explode('/', $dir);
		$previousDir = '';

		foreach ($alldirs as $curdir)
		{
			$check = $previousDir . '/' . $curdir;

			if (!@ssh2_sftp_stat($sftphandle, $check))
			{
				if (ssh2_sftp_mkdir($sftphandle, $check, 0755, true) === false)
				{
					$this->setWarning('Could not create SFTP directory ' . $check);

					return false;
				}
			}

			$previousDir = $check;
		}

		return true;
	}
}