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
 */

use Akeeba\Engine\Platform;

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

require_once JPATH_LIBRARIES.'/f0f/include.php';

// Load the JApplicationCli class
JLoader::import('joomla.application.cli');
JLoader::import('joomla.application.component.helper');
JLoader::import('cms.component.helper');

/**
 * Akeeba Backup Update application
 */
class AkeebaBackupUpdate extends JApplicationCli
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

    public function flushAssets()
    {
        // This is an empty function since JInstall will try to flush the assets even if we're in CLI (!!!)
        return true;
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


        $debugmessage = '';
        if ($this->input->get('debug', -1, 'int') != -1)
        {
            if (!defined('AKEEBADEBUG'))
            {
                define('AKEEBADEBUG', 1);
            }
            $debugmessage = "*** DEBUG MODE ENABLED ***\n";
            ini_set('display_errors', 1);
        }

        $version		 = AKEEBA_VERSION;
        $date			 = AKEEBA_DATE;

        $phpversion		 = PHP_VERSION;
        $phpenvironment	 = PHP_SAPI;

        if ($this->input->get('quiet', -1, 'int') == -1)
        {
            $year = gmdate('Y');
            echo <<<ENDBLOCK
Akeeba Backup Update $version ($date)
Copyright (C) 2010-$year Nicholas K. Dionysopoulos
-------------------------------------------------------------------------------
Akeeba Backup is Free Software, distributed under the terms of the GNU General
Public License version 3 or, at your option, any later version.
This program comes with ABSOLUTELY NO WARRANTY as per sections 15 & 16 of the
license. See http://www.gnu.org/licenses/gpl-3.0.html for details.
-------------------------------------------------------------------------------
You are using PHP $phpversion ($phpenvironment)
$debugmessage
Checking for new Akeeba Backup versions


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

        /** @var AkeebaModelUpdates $updateModel */
        $updateModel = F0FModel::getTmpInstance('Updates', 'AkeebaModel');

        $result = $updateModel->autoupdate();

        echo implode("\n", $result['message']);

        $this->close(0);
    }
}
// Load the version file
require_once JPATH_ADMINISTRATOR . '/components/com_akeeba/version.php';

// Instanciate and run the application
$app = JApplicationCli::getInstance('AkeebaBackupUpdate');
JFactory::$application = $app;
$app->execute();