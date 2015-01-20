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

class Ftp extends Base
{
	public function __construct()
	{
		$this->can_delete = true;
		$this->can_download_to_browser = true;
		$this->can_download_to_file = true;
	}

	public function processPart($absolute_filename, $upload_as = null)
	{
		// Retrieve engine configuration data
		$config = Factory::getConfiguration();

		$host = $config->get('engine.postproc.ftp.host', '');
		$port = $config->get('engine.postproc.ftp.port', 21);
		$user = $config->get('engine.postproc.ftp.user', '');
		$pass = $config->get('engine.postproc.ftp.pass', 0);
		$directory = $config->get('volatile.postproc.directory', null);
		if (empty($directory))
		{
			$directory = $config->get('engine.postproc.ftp.initial_directory', '');
		}
		$ssl = $config->get('engine.postproc.ftp.ftps', 0) == 0 ? false : true;
		$passive = $config->get('engine.postproc.ftp.passive_mode', 0) == 0 ? false : true;

		// You can't fix stupid, but at least you get to shout at them
		if (strtolower(substr($host, 0, 6)) == 'ftp://')
		{
			Factory::getLog()->log(LogLevel::WARNING, 'YOU ARE *** N O T *** SUPPOSED TO ENTER THE ftp:// PROTOCOL PREFIX IN THE FTP HOSTNAME FIELD OF THE Upload to Remote FTP POST-PROCESSING ENGINE.');
			Factory::getLog()->log(LogLevel::WARNING, 'I am trying to fix your bad configuration setting, but the backup might fail anyway. You MUST fix this in your configuration.');
			$host = substr($host, 6);
		}

		// Process the initial directory
		$directory = '/' . ltrim(trim($directory), '/');

		// Parse tags
		$directory = Factory::getFilesystemTools()->replace_archive_name_variables($directory);
		$config->set('volatile.postproc.directory', $directory);

		// Connect to the FTP server
		Factory::getLog()->log(LogLevel::DEBUG, __CLASS__ . ':: Connecting to remote FTP');

		if ($ssl)
		{
			if (function_exists('ftp_ssl_connect'))
			{
				$ftphandle = @ftp_ssl_connect($host, $port);
			}
			else
			{
				$ftphandle = false;
			}
		}
		else
		{
			$ftphandle = @ftp_connect($host, $port);
		}

		if ($ftphandle === false)
		{
			$this->setWarning("Wrong FTP hostname or port (host:port = $host:$port)");

			return false;
		}

		// Login
		Factory::getLog()->log(LogLevel::DEBUG, __CLASS__ . ":: Logging in");

		if (!@ftp_login($ftphandle, $user, $pass))
		{
			$this->setWarning('Invalid username/password for the remote FTP server');
			ftp_close($ftphandle);

			return false;
		}

		// Change to initial directory
		Factory::getLog()->log(LogLevel::DEBUG, __CLASS__ . ":: Changing to initial directory");
		if (!@ftp_chdir($ftphandle, $directory))
		{
			$this->setWarning("Invalid initial directory $directory for the remote FTP server");

			return false;
		}

		$isPassiveTag = $passive ? 'Passive' : 'Active';
		Factory::getLog()->log(LogLevel::DEBUG, __CLASS__ . ":: Enabling $isPassiveTag mode");
		@ftp_pasv($ftphandle, $passive);

		Factory::getLog()->log(LogLevel::DEBUG, __CLASS__ . ":: Starting FTP upload of $absolute_filename");
		$realdir = substr($directory, -1) == '/' ? substr($directory, 0, strlen($directory) - 1) : $directory;
		$basename = empty($upload_as) ? basename($absolute_filename) : $upload_as;
		$realname = $realdir . '/' . $basename;
		try
		{
			$res = @ftp_put($ftphandle, $realname, $absolute_filename, FTP_BINARY);
		}
		catch (\Exception $e)
		{
			// Funny how PHP dies without returning false if you don't use a try/catch statement, eh?
			$res = false;
			$this->setWarning($e->getMessage());
		}

		// Store the absolute remote path in the class property
		$this->remote_path = $realname;

		@ftp_close($ftphandle);

		if (!$res)
		{
			// If the file was unreadable, just skip it...
			if (is_readable($absolute_filename))
			{
				$this->setWarning('Uploading ' . $absolute_filename . ' has failed.');

				return false;
			}
			else
			{
				$this->setWarning('Uploading ' . $absolute_filename . ' has failed because the file is unreadable.');

				return true;
			}
		}
		else
		{
			return true;
		}
	}

	public function delete($path)
	{
		// Retrieve engine configuration data
		$config = Factory::getConfiguration();

		$host = $config->get('engine.postproc.ftp.host', '');
		$port = $config->get('engine.postproc.ftp.port', 21);
		$user = $config->get('engine.postproc.ftp.user', '');
		$pass = $config->get('engine.postproc.ftp.pass', 0);
		$ssl = $config->get('engine.postproc.ftp.ftps', 0) == 0 ? false : true;
		$passive = $config->get('engine.postproc.ftp.passive_mode', 0) == 0 ? false : true;

		$directory = dirname($path);

		// Connect to the FTP server
		Factory::getLog()->log(LogLevel::DEBUG, __CLASS__ . '::delete() -- Connecting to remote FTP');

		if ($ssl)
		{
			if (function_exists('ftp_ssl_connect'))
			{
				$ftphandle = @ftp_ssl_connect($host, $port);
			}
			else
			{
				$ftphandle = false;
			}
		}
		else
		{
			$ftphandle = @ftp_connect($host, $port);
		}

		if ($ftphandle === false)
		{
			$this->setWarning("Wrong FTP hostname or port (host:port = $host:$port)");

			return false;
		}

		// Login
		if (!@ftp_login($ftphandle, $user, $pass))
		{
			$this->setWarning('Invalid username/password for the remote FTP server');
			ftp_close($ftphandle);

			return false;
		}

		@ftp_pasv($ftphandle, $passive);

		try
		{
			$res = @ftp_delete($ftphandle, $path);
		}
		catch (\Exception $e)
		{
			// Funny how PHP dies without returning false if you don't use a try/catch statement, eh?
			$res = false;
			$this->setWarning($e->getMessage());
		}

		@ftp_close($ftphandle);

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

		$host = $config->get('engine.postproc.ftp.host', '');
		$port = $config->get('engine.postproc.ftp.port', 21);
		$user = $config->get('engine.postproc.ftp.user', '');
		$pass = $config->get('engine.postproc.ftp.pass', 0);
		$ssl = $config->get('engine.postproc.ftp.ftps', 0) == 0 ? false : true;
		$passive = $config->get('engine.postproc.ftp.passive_mode', 0) == 0 ? false : true;

		$directory = dirname($remotePath);

		// Connect to the FTP server
		Factory::getLog()->log(LogLevel::DEBUG, __CLASS__ . '::delete() -- Connecting to remote FTP');

		if ($ssl)
		{
			if (function_exists('ftp_ssl_connect'))
			{
				$ftphandle = @ftp_ssl_connect($host, $port);
			}
			else
			{
				$ftphandle = false;
			}
		}
		else
		{
			$ftphandle = @ftp_connect($host, $port);
		}

		if ($ftphandle === false)
		{
			$this->setWarning("Wrong FTP hostname or port (host:port = $host:$port)");

			return false;
		}

		// Login
		if (!@ftp_login($ftphandle, $user, $pass))
		{
			$this->setWarning('Invalid username/password for the remote FTP server');
			ftp_close($ftphandle);

			return false;
		}

		@ftp_pasv($ftphandle, $passive);

		try
		{
			$result = ftp_get($ftphandle, $localFile, $remotePath, FTP_BINARY);
		}
		catch (\Exception $e)
		{
			$this->setWarning($e->getMessage());

			return false;
		}
		ftp_close($ftphandle);

		return $result;
	}

	/**
	 * Returns an FTP/FTPS URL for directly downloading the requested file (lame and functional)
	 */
	public function downloadToBrowser($remotePath)
	{
		// Retrieve engine configuration data
		$config = Factory::getConfiguration();

		$host = $config->get('engine.postproc.ftp.host', '');
		$port = $config->get('engine.postproc.ftp.port', 21);
		$user = $config->get('engine.postproc.ftp.user', '');
		$pass = $config->get('engine.postproc.ftp.pass', 0);
		$ssl = $config->get('engine.postproc.ftp.ftps', 0) == 0 ? false : true;

		$uri = $ssl ? 'ftps://' : 'ftp://';
		if ($user && $pass)
		{
			$uri .= urlencode($user) . ':' . urlencode($pass) . '@';
		}
		$uri .= $host;
		if ($port && ($port != 21))
		{
			$uri .= ':' . $port;
		}
		if (substr($remotePath, 0, 1) != '/')
		{
			$uri .= '/';
		}
		$uri .= $remotePath;

		return $uri;
	}
}