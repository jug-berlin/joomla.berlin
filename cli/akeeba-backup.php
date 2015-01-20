<?php
/**
 *  @package AkeebaBackup
 *  @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  --
 *
 *  Command-line script to schedule the File Alteration Monitor check
 */

use Akeeba\Engine\Platform;
use Akeeba\Engine\Factory;

// Define ourselves as a parent file
define('_JEXEC', 1);

// Enable Akeeba Engine
define('AKEEBAENGINE', 1);

$minphp = '5.3.1';
if (version_compare(PHP_VERSION, $minphp, 'lt'))
{
	$curversion = PHP_VERSION;
	$bindir = PHP_BINDIR;
	echo <<< ENDWARNING
================================================================================
WARNING! Incompatible PHP version $curversion
================================================================================

This CRON script must be run using PHP version $minphp or later. Your server is
currently using a much older version which would cause this script to crash. As
a result we have aborted execution of the script. Please contact your host and
ask them for the correct path to the PHP CLI binary for PHP $minphp or later, then
edit your CRON job and replace your current path to PHP with the one your host
gave you.

For your information, the current PHP version information is as follows.

PATH:    $bindir
VERSION: $curversion

Further clarifications:

1. There is absolutely no possible way that you are receiving this warning in
   error. We are using the PHP_VERSION constant to detect the PHP version you
   are currently using. This is what PHP itself reports as its own version. It
   simply cannot lie.

2. Even though your *site* may be running in a higher PHP version that the one
   reported above, your CRON scripts will most likely not be running under it.
   This has to do with the fact that your site DOES NOT run under the command
   line and there are different executable files (binaries) for the web and
   command line versions of PHP.

3. Please note that you MUST NOT ask us for support about this error. We cannot
   possibly know the correct path to the PHP CLI binary as we have not set up
   your server. Your host must know and give that information.

4. The latest published versions of PHP can be found at http://www.php.net/
   Any older version is considered insecure and must NOT be used on a live
   server. If your server uses a much older version of PHP than that please
   notify them that their servers are insecure and in need of an update.

This script will now terminate. Goodbye.

ENDWARNING;
	die();
}

// Required by the CMS
define('DS', DIRECTORY_SEPARATOR);

// Timezone fix; avoids errors printed out by PHP 5.3.3+ (thanks Yannick!)
if (function_exists('date_default_timezone_get') && function_exists('date_default_timezone_set'))
{
	if (function_exists('error_reporting'))
	{
		$oldLevel = error_reporting(0);
	}
	$serverTimezone	 = @date_default_timezone_get();
	if (empty($serverTimezone) || !is_string($serverTimezone))
		$serverTimezone	 = 'UTC';
	if (function_exists('error_reporting'))
	{
		error_reporting($oldLevel);
	}
	@date_default_timezone_set($serverTimezone);
}

// Load system defines
if (file_exists(__DIR__ . '/defines.php'))
{
	include_once __DIR__ . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	$path = rtrim(__DIR__, DIRECTORY_SEPARATOR);
	$rpos = strrpos($path, DIRECTORY_SEPARATOR);
	$path = substr($path, 0, $rpos);
	define('JPATH_BASE', $path);
	require_once JPATH_BASE . '/includes/defines.php';
}

// Load the rest of the framework include files
if (file_exists(JPATH_LIBRARIES . '/import.legacy.php'))
{
	require_once JPATH_LIBRARIES . '/import.legacy.php';
}
else
{
	require_once JPATH_LIBRARIES . '/import.php';
}
require_once JPATH_LIBRARIES . '/cms.php';

// You can't fix stupid… but you can try working around it
if( (!function_exists('json_encode')) || (!function_exists('json_decode')) )
{
	require_once JPATH_ADMINISTRATOR . '/components/com_akeeba/helpers/jsonlib.php';
}

// Load the JApplicationCli class
JLoader::import('joomla.application.cli');

/**
 * Akeeba Backup CLI application
 */
class AkeebaBackupCLI extends JApplicationCli
{
	/**
	 * JApplicationCli didn't want to run on PHP CGI. I have my way of becoming
	 * VERY convincing. Now obey your true master, you petty class!
	 *
	 * @param JInputCli $input
	 * @param JRegistry $config
	 * @param JDispatcher $dispatcher
	 */
	public function __construct(JInputCli $input = null, JRegistry $config = null, JDispatcher $dispatcher = null)
	{
		// Close the application if we are not executed from the command line, Akeeba style (allow for PHP CGI)
		if (array_key_exists('REQUEST_METHOD', $_SERVER))
		{
			die('You are not supposed to access this script from the web. You have to run it from the command line. If you don\'t understand what this means, you must not try to use this file before reading the documentation. Thank you.');
		}

		$cgiMode = false;

		if (!defined('STDOUT') || !defined('STDIN') || !isset($_SERVER['argv']))
		{
			$cgiMode = true;
		}

		// If a input object is given use it.
		if ($input instanceof JInput)
		{
			$this->input = $input;
		}
		// Create the input based on the application logic.
		else
		{
			if (class_exists('JInput'))
			{
				if ($cgiMode)
				{
					$query = "";
					if (!empty($_GET))
					{
						foreach ($_GET as $k => $v)
						{
							$query .= " $k";
							if ($v != "")
							{
								$query .= "=$v";
							}
						}
					}
					$query	 = ltrim($query);
					$argv	 = explode(' ', $query);
					$argc	 = count($argv);

					$_SERVER['argv'] = $argv;
				}

				$this->input = new JInputCLI();
			}
		}

		// If a config object is given use it.
		if ($config instanceof JRegistry)
		{
			$this->config = $config;
		}
		// Instantiate a new configuration object.
		else
		{
			$this->config = new JRegistry;
		}

		// If a dispatcher object is given use it.
		if ($dispatcher instanceof JDispatcher)
		{
			$this->dispatcher = $dispatcher;
		}
		// Create the dispatcher based on the application logic.
		else
		{
			$this->loadDispatcher();
		}

		// Load the configuration object.
		$this->loadConfiguration($this->fetchConfigurationData());

		// Set the execution datetime and timestamp;
		$this->set('execution.datetime', gmdate('Y-m-d H:i:s'));
		$this->set('execution.timestamp', time());

		// Set the current directory.
		$this->set('cwd', getcwd());
	}

	public function execute()
	{
		// Load the language files
		$paths	 = array(JPATH_ADMINISTRATOR, JPATH_ROOT);
		$jlang	 = JFactory::getLanguage();
		$jlang->load('com_akeeba', $paths[0], 'en-GB', true);
		$jlang->load('com_akeeba', $paths[1], 'en-GB', true);
		$jlang->load('com_akeeba' . '.override', $paths[0], 'en-GB', true);
		$jlang->load('com_akeeba' . '.override', $paths[1], 'en-GB', true);

		// Get the backup profile and description
		$profile	 = $this->input->get('profile', 1, 'int');
		$description = $this->input->get('description', 'Command-line backup', 'string');
		$overrides	 = $this->getOption('override', array(), false);

		if (!empty($overrides))
		{
			$override_message = "\nConfiguration variables overriden in the command line:\n";
			$override_message .= implode(', ', array_keys($overrides));
			$override_message .= "\n";
		}
		else
		{
			$override_message = "";
		}

		$debugmessage = '';
		if ($this->input->get('debug', -1, 'int') != -1)
		{
			if (!defined('AKEEBADEBUG'))
			{
				define('AKEEBADEBUG', 1);
			}
			$debugmessage = "*** DEBUG MODE ENABLED ***\n";
		}

		$version		 = AKEEBA_VERSION;
		$date			 = AKEEBA_DATE;
		$start_backup	 = time();
		$memusage		 = $this->memUsage();

		$phpversion		 = PHP_VERSION;
		$phpenvironment	 = PHP_SAPI;
		$phpos			 = PHP_OS;

		if ($this->input->get('quiet', -1, 'int') == -1)
		{
			$year = gmdate('Y');
			echo <<<ENDBLOCK
Akeeba Backup CLI $version ($date)
Copyright (C) 2010-$year Nicholas K. Dionysopoulos
-------------------------------------------------------------------------------
Akeeba Backup is Free Software, distributed under the terms of the GNU General
Public License version 3 or, at your option, any later version.
This program comes with ABSOLUTELY NO WARRANTY as per sections 15 & 16 of the
license. See http://www.gnu.org/licenses/gpl-3.0.html for details.
-------------------------------------------------------------------------------
You are using PHP $phpversion ($phpenvironment)
$debugmessage
Starting a new backup with the following parameters:
Profile ID  $profile
Description "$description"
$override_message
Current memory usage: $memusage


ENDBLOCK;
		}

		// Attempt to use an infinite time limit, in case you are using the PHP CGI binary instead
		// of the PHP CLI binary. This will not work with Safe Mode, though.
		$safe_mode = true;
		if (function_exists('ini_get'))
		{
			$safe_mode = ini_get('safe_mode');
		}
		if (!$safe_mode && function_exists('set_time_limit'))
		{
			if ($this->input->get('quiet', -1, 'int') == -1)
			{
				echo "Unsetting time limit restrictions.\n";
			}
			@set_time_limit(0);
		}
		elseif (!$safe_mode)
		{
			if ($this->input->get('quiet', -1, 'int') == -1)
			{
				echo "Could not unset time limit restrictions; you may get a timeout error\n";
			}
		}
		else
		{
			if ($this->input->get('quiet', -1, 'int') == -1)
			{
				echo "You are using PHP's Safe Mode; you may get a timeout error\n";
			}
		}
		if ($this->input->get('quiet', -1, 'int') == -1)
		{
			echo "\n";
		}

		// Log some paths
		if ($this->input->get('quiet', -1, 'int') == -1)
		{
			echo "Site paths determined by this script:\n";
			echo "JPATH_BASE : " . JPATH_BASE . "\n";
			echo "JPATH_ADMINISTRATOR : " . JPATH_ADMINISTRATOR . "\n\n";
		}

		// Load the engine
		$factoryPath = JPATH_ADMINISTRATOR . '/components/com_akeeba/engine/Factory.php';
		define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_akeeba');
		define('AKEEBAROOT', JPATH_ADMINISTRATOR . '/components/com_akeeba/akeeba');
		if (!file_exists($factoryPath))
		{
			echo "ERROR!\n";
			echo "Could not load the backup engine; file does not exist. Technical information:\n";
			echo "Path to " . basename(__FILE__) . ": " . __DIR__ . "\n";
			echo "Path to factory file: $factoryPath\n";
			die("\n");
		}
		else
		{
			try
			{
				require_once $factoryPath;
			}
			catch (Exception $e)
			{
				echo "ERROR!\n";
				echo "Backup engine returned an error. Technical information:\n";
				echo "Error message:\n\n";
				echo $e->getMessage() . "\n\n";
				echo "Path to " . basename(__FILE__) . ":" . __DIR__ . "\n";
				echo "Path to factory file: $factoryPath\n";
				die("\n");
			}
		}

		// Assign the correct platform
		Platform::addPlatform('joomla25', JPATH_COMPONENT_ADMINISTRATOR . '/platform/joomla25');

		// Forced CLI mode settings
		define('AKEEBA_PROFILE', $profile);
		define('AKEEBA_BACKUP_ORIGIN', 'cli');

		// Load the profile
		Platform::getInstance()->load_configuration($profile);

		// Reset Kettenrad and its storage
		Factory::resetState(array(
			'maxrun' => 0
		));

		Factory::getFactoryStorage()->reset(AKEEBA_BACKUP_ORIGIN);

		// Setup
		$kettenrad = Factory::getKettenrad();

		$options	 = array(
			'description'	 => $description,
			'comment'		 => ''
		);

		if (!empty($overrides))
		{
			Platform::getInstance()->configOverrides = $overrides;
		}

		$kettenrad->setup($options);

		// Dummy array so that the loop iterates once
		$array = array(
			'HasRun' => 0,
			'Error'	 => ''
		);

		$warnings_flag = false;

		while (($array['HasRun'] != 1) && (empty($array['Error'])))
		{
			// Recycle the database conenction to minimise problems with database timeouts
			$db = Factory::getDatabase();
			$db->close();
			$db->open();

			Factory::getLog()->open(AKEEBA_BACKUP_ORIGIN);
			Factory::getLog()->unpause();

			// Apply overrides in the command line
			if (!empty($overrides))
			{
				$config = Factory::getConfiguration();

				foreach ($overrides as $key => $value)
				{
					$config->set($key, $value);
				}
			}

			// Apply engine optimization overrides
			$config = Factory::getConfiguration();
			$config->set('akeeba.tuning.min_exec_time', 0);
			$config->set('akeeba.tuning.nobreak.beforelargefile', 1);
			$config->set('akeeba.tuning.nobreak.afterlargefile', 1);
			$config->set('akeeba.tuning.nobreak.proactive', 1);
			$config->set('akeeba.tuning.nobreak.finalization', 1);
			$config->set('akeeba.tuning.settimelimit', 0);
			$config->set('akeeba.tuning.nobreak.domains', 0);

			$kettenrad->tick();

			Factory::getTimer()->resetTime();

			$array		 = $kettenrad->getStatusArray();

			Factory::getLog()->close();

			$time		 = date('Y-m-d H:i:s \G\M\TO (T)');
			$memusage	 = $this->memUsage();

			$warnings		 = "no warnings issued (good)";
			$stepWarnings	 = false;

			if (!empty($array['Warnings']))
			{
				$warnings_flag	 = true;
				$warnings		 = "POTENTIAL PROBLEMS DETECTED; " . count($array['Warnings']) . " warnings issued (see below).\n";
				foreach ($array['Warnings'] as $line)
				{
					$warnings .= "\t$line\n";
				}
				$stepWarnings = true;
				$kettenrad->resetWarnings();
			}

			if (($this->input->get('quiet', -1, 'int') == -1) || $stepWarnings)
				echo <<<ENDSTEPINFO
Last Tick   : $time
Domain      : {$array['Domain']}
Step        : {$array['Step']}
Substep     : {$array['Substep']}
Memory used : $memusage
Warnings    : $warnings


ENDSTEPINFO;
		}

		// Clean up
		Factory::getFactoryStorage()->reset(AKEEBA_BACKUP_ORIGIN);

		if (!empty($array['Error']))
		{
			echo "An error has occurred:\n{$array['Error']}\n\n";

			$exitCode = 2;
		}
		else
		{
			if ($this->input->get('quiet', -1, 'int') == -1)
			{
				echo "Backup job finished successfully after approximately " . $this->timeago($start_backup, time(), '', false) . "\n";
			}

			$exitCode = 0;
		}

		if ($warnings_flag && ($this->input->get('quiet', -1, 'int') == -1))
		{
			$exitCode = 1;
			echo "\n" . str_repeat('=', 79) . "\n";
			echo "!!!!!  W A R N I N G  !!!!!\n\n";
			echo "Akeeba Backup issued warnings during the backup process. You have to review them\n";
			echo "and make sure that your backup has completed successfully. Always test a backup with\n";
			echo "warnings to make sure that it is working properly, by restoring it to a local server.\n";
			echo "DO NOT IGNORE THIS MESSAGE! AN UNTESTED BACKUP IS AS GOOD AS NO BACKUP AT ALL.\n";
			echo "\n" . str_repeat('=', 79) . "\n";
		}
		elseif ($warnings_flag)
		{
			$exitCode = 1;
		}

		if ($this->input->get('quiet', -1, 'int') == -1)
		{
			echo "Peak memory usage: " . $this->peakMemUsage() . "\n\n";
		}

		$this->close($exitCode);
	}

	/**
	 * Returns a fancy formatted time lapse code
	 *
	 * @param  $referencedate	int		Timestamp of the reference date/time
	 * @param  $timepointer		int		Timestamp of the current date/time
	 * @param  $measureby		string	One of s, m, h, d, or y (time unit)
	 * @param  $autotext			bool
	 *
	 * @return  string
	 */
	private function timeago($referencedate = 0, $timepointer = '', $measureby = '', $autotext = true)
	{
		if ($timepointer == '')
		{
			$timepointer = time();
		}

		// Raw time difference
		$Raw	 = $timepointer - $referencedate;
		$Clean	 = abs($Raw);

		$calcNum = array(
			array('s', 60),
			array('m', 60 * 60),
			array('h', 60 * 60 * 60),
			array('d', 60 * 60 * 60 * 24),
			array('y', 60 * 60 * 60 * 24 * 365)
		);

		$calc = array(
			's'	 => array(1, 'second'),
			'm'	 => array(60, 'minute'),
			'h'	 => array(60 * 60, 'hour'),
			'd'	 => array(60 * 60 * 24, 'day'),
			'y'	 => array(60 * 60 * 24 * 365, 'year')
		);

		if ($measureby == '')
		{
			$usemeasure = 's';

			for ($i = 0; $i < count($calcNum); $i++)
			{
				if ($Clean <= $calcNum[$i][1])
				{
					$usemeasure	 = $calcNum[$i][0];
					$i			 = count($calcNum);
				}
			}
		}
		else
		{
			$usemeasure = $measureby;
		}

		$datedifference = floor($Clean / $calc[$usemeasure][0]);

		if ($autotext == true && ($timepointer == time()))
		{
			if ($Raw < 0)
			{
				$prospect = ' from now';
			}
			else
			{
				$prospect = ' ago';
			}
		}
		else
		{
			$prospect = '';
		}

		if ($referencedate != 0)
		{
			if ($datedifference == 1)
			{
				return $datedifference . ' ' . $calc[$usemeasure][1] . ' ' . $prospect;
			}
			else
			{
				return $datedifference . ' ' . $calc[$usemeasure][1] . 's ' . $prospect;
			}
		}
		else
		{
			return 'No input time referenced.';
		}
	}

	/**
	 * Returns the current memory usage
	 *
	 * @return string
	 */
	private function memUsage()
	{
		if (function_exists('memory_get_usage'))
		{
			$size	 = memory_get_usage();
			$unit	 = array('b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb');
			return @round($size / pow(1024, ($i		 = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
		}
		else
		{
			return "(unknown)";
		}
	}

	/**
	 * Returns the peak memory usage
	 *
	 * @return string
	 */
	private function peakMemUsage()
	{
		if (function_exists('memory_get_peak_usage'))
		{
			$size	 = memory_get_peak_usage();
			$unit	 = array('b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb');
			return @round($size / pow(1024, ($i		 = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
		}
		else
		{
			return "(unknown)";
		}
	}

	/**
	 * Parses POSIX command line options and returns them as an associative array. Each array item contains
	 * a single dimensional array of values. Arguments without a dash are silently ignored.
	 * @return array
	 */
	private function parseOptions()
	{
		global $argc, $argv;

		// Workaround for PHP-CGI
		if (!isset($argc) && !isset($argv))
		{
			$query = "";
			if (!empty($_GET))
				foreach ($_GET as $k => $v)
				{
					$query .= " $k";
					if ($v != "")
					{
						$query .= "=$v";
					}
				}
			$query	 = ltrim($query);
			$argv	 = explode(' ', $query);
			$argc	 = count($argv);
		}

		$currentName = "";
		$options	 = array();

		for ($i = 1; $i < $argc; $i++)
		{
			$argument = $argv[$i];
			if (strpos($argument, "-") === 0)
			{
				$argument = ltrim($argument, '-');
				if (strstr($argument, '='))
				{
					list($name, $value) = explode('=', $argument, 2);
				}
				else
				{
					$name	 = $argument;
					$value	 = null;
				}
				$currentName = $name;
				if (!isset($options[$currentName]) || ($options[$currentName] == NULL))
				{
					$options[$currentName] = array();
				}
			}
			else
			{
				$value = $argument;
			}
			if ((!is_null($value)) && (!is_null($currentName)))
			{
				if (strstr($value, '='))
				{
					$parts	 = explode('=', $value, 2);
					$key	 = $parts[0];
					$value	 = $parts[1];
				}
				else
				{
					$key = null;
				}

				$values = $options[$currentName];
				if (is_null($values))
				{
					$values = array();
				}
				if (is_null($key))
				{
					array_push($values, $value);
				}
				else
				{
					$values[$key] = $value;
				}
				$options[$currentName] = $values;
			}
		}
		return $options;
	}

	/**
	 * Returns the value of a command line option
	 * @param string $key The full name of the option, e.g. "foobar"
	 * @param mixed $default The default value to return
	 * @param bool $first_item_only Return only the first value specified (default = true)
	 * @return mixed
	 */
	private function getOption($key, $default = null, $first_item_only = true)
	{
		static $options = null;
		if (is_null($options))
		{
			$options = $this->parseOptions();
		}

		if (!array_key_exists($key, $options))
		{
			return $default;
		}
		else
		{
			if ($first_item_only)
			{
				return $options[$key][0];
			}
			else
			{
				return $options[$key];
			}
		}
	}

}
// Load the version file
require_once JPATH_ADMINISTRATOR . '/components/com_akeeba/version.php';

// Instanciate and run the application
JApplicationCli::getInstance('AkeebaBackupCLI')->execute();