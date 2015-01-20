<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

// Compatibility with @securejoomla audit service
if (defined('_BF_AUDIT'))
{
	return;
}

JLoader::import('joomla.application.plugin');

class plgSystemAdmintoolsPro extends JPlugin
{
    /** @var AdmintoolsModelStorage  */
	private $cparams = null;
	private $exceptions = array();
	private $skipFiltering = false;
	private $timestamps = array();

	public function __construct(& $subject, $config = array())
	{
		JLoader::import('joomla.html.parameter');
		JLoader::import('joomla.plugin.helper');
		JLoader::import('joomla.application.component.helper');
		$plugin = JPluginHelper::getPlugin('system', 'admintools');
		$defaultConfig = (array)($plugin);

		$config = array_merge($defaultConfig, $config);

		// Use the parent constructor to create the plugin object
		parent::__construct($subject, $config);

		// Work around non-transparent proxy and reverse proxy IP issues
		include_once JPATH_ADMINISTRATOR . '/components/com_admintools/helpers/ip.php';

		if (class_exists('AdmintoolsHelperIp', false))
		{
			AdmintoolsHelperIp::workaroundIPIssues();
		}

		// Load the components parameters
		JLoader::import('joomla.application.component.model');

		require_once JPATH_ROOT . '/administrator/components/com_admintools/models/storage.php';

		if (interface_exists('JModel'))
		{
			$this->cparams = JModelLegacy::getInstance('Storage', 'AdmintoolsModel');
		}
		else
		{
			$this->cparams = JModel::getInstance('Storage', 'AdmintoolsModel');
		}

		// Load WAF exceptions
		$this->loadExceptions();

		if (empty($this->exceptions))
		{
			$this->exceptions = array();
		}
		else
		{
			if (empty($this->exceptions[0]))
			{
				$this->skipFiltering = true;
			}
		}

		// Load the GeoIP library if it's not already loaded
		if (!class_exists('AkeebaGeoipProvider'))
		{
			if (@file_exists(JPATH_PLUGINS . '/system/akgeoip/lib/akgeoip.php'))
			{
				if (@include_once JPATH_PLUGINS . '/system/akgeoip/lib/vendor/autoload.php')
				{
					@include_once JPATH_PLUGINS . '/system/akgeoip/lib/akgeoip.php';
				}
			}
		}
	}

	/**
	 * Log a security exception coming from a third party application. It's
	 * supposed to be used by 3PD to log security exceptions in Admin Tools'
	 * log.
	 *
	 * @param   string  $message   The blocking reason to show to the administrator. MANDATORY.
	 * @param   string  $message   The message to show to the user being blocked. MANDATORY.
	 * @param   array   $extraInfo Any extra information to record to the log file (hash array).
	 * @param   boolean $autoban   Should we also auto-ban this IP immediately?
	 *
	 * @return  void
	 */
	public function onAdminToolsThirdpartyException($reason, $message, $extraInfo = array(), $autoban = false)
	{
		if (empty($message))
		{
			return;
		}

		// Block the request
		$this->blockRequest('external', $message, $extraInfo, $reason);

		// Automatically ban the IP if auto IP banning is enabled and the $autoban flag is set
		$autobanipenabled = $this->cparams->getValue('tsrenable', 0);

		if ($autobanipenabled && $autoban)
		{
			$this->autoBan('external');
		}
	}

	/**
	 * Hooks to the onAfterInitialize system event, the first time in the
	 * Joomla! page load workflow which fires a plug-in event
	 */
	public function onAfterInitialise()
	{
		// Automatic banning
		$this->AutoIPFiltering();

		// IP Blacklisting
		if ($this->cparams->getValue('ipbl', 0) == 1)
		{
			$this->IPFiltering();
		}

		// GeoBlocking
		$cnt = $this->cparams->getValue('geoblockcountries', '');
		$con = $this->cparams->getValue('geoblockcontinents', '');

		if (!empty($cnt) || !empty($con))
		{
			$this->geoBlocking();
		}

		$this->customAdminFolder();

		// Check for admin access
		if ($this->isAdminAccessAttempt())
		{
			// IP Whitelist filtering for back-end access
			if ($this->cparams->getValue('ipwl', 0) == 1)
			{
				$this->adminIPFiltering();
			}

            $this->checkCustomAdminFolder();

			// Administrator "secret word" protection
			$this->adminPasswordProtection();

            if(!$this->checkAwaySchedule())
            {
                $this->redirectAdminToHome();
            }

			if ($this->cparams->getValue('twofactorauth', 0) == 1)
			{
				$version = explode('.', JVERSION);
				$version = $version[0] . $version[1];
				$template = JFactory::getApplication()->getTemplate();

				$cssAlt = array(
					"login.css",
					"login-$version.css",
					"login-$version-$template.css",
				);
				$paths = array(
					'../media/com_admintools/css',
					"templates/$template/media/com_admintools/css"
				);

				JLoader::import('joomla.filesystem.file');

				foreach ($paths as $path)
				{
					foreach ($cssAlt as $cssFile)
					{
						$url = $path . '/' . $cssFile;
						$filename = JPATH_ADMINISTRATOR . '/' . $url;

						if (JFile::exists($filename))
						{
							JFactory::getDocument()->addStyleSheet($url);
						}
					}
				}
			}
		}

		if ($this->isAdminAccessAttempt(true))
		{
			if ($this->cparams->getValue('twofactorauth', 0) == 1)
			{
				$this->twoFactorAuthentication_verify();
			}
		}


		// Remove inactive users
		if ($this->params->get('deleteinactive', 0) == 1)
		{
			$this->removeInactiveUsers();
		}

		// Remove old log records
		if ($this->params->get('maxlogentries', 0) > 0)
		{
			$this->removeOldLogEntries();
		}

		$app = JFactory::getApplication();
		// Back-end stuff
		if (in_array($app->getName(), array('administrator', 'admin')))
		{
			// Block access to the extensions installer
			if ($this->cparams->getValue('blockinstall', 0) > 0)
			{
				$this->blockInstall();
			}

			// Disable editing of back-end users
			if ($this->cparams->getValue('nonewadmins', 0) == 1)
			{
				$this->noNewAdmins();
			}

			// Email on administrator access
			$emailonadmin = $this->cparams->getValue('emailonadminlogin', '');

			if (!empty($emailonadmin))
			{
				$this->emailOnAdminLogin();
			}

			// If there is an administrator secret word set, upon logout redirect to the site's home page
			$password = $this->cparams->getValue('adminpw', '');

			if (!empty($password))
			{
				$input = JFactory::getApplication()->input;
				$option = $input->getCmd('option', '');
				$task = $input->getCmd('task', '');
				$uid = $input->getInt('uid', 0);

				$loggingMeOut = true;

				if (!empty($uid))
				{
					$myUID = JFactory::getUser()->id;
					$loggingMeOut = $myUID == $uid;
				}

				if (($option == 'com_login') && ($task == 'logout') && $loggingMeOut)
				{
					// Logout and redirect to the homepage
					$result = $app->logout();
					$baseURL = JURI::base();
					$baseURL = str_replace('/administrator', '', $baseURL);
					$app->redirect($baseURL);
				}
			}
		}
		else
			// Front-end stuff
		{
			if (!$this->skipFiltering)
			{
				// HTTP:BL integration
				if ($this->cparams->getValue('httpblenable', 0) == 1)
				{
					$this->ProjectHoneypotHTTPBL();
				}

				// SQL Injection shielding
				if ($this->cparams->getValue('sqlishield', 0) == 1)
				{
					$this->SQLiShield();
				}

				// XSS shielding
				if ($this->cparams->getValue('xssshield', 0) == 1)
				{
					$this->XSSShield();
				}

				// Malicious User Agent shielding
				if ($this->cparams->getValue('muashield', 0) == 1)
				{
					$this->MUAShield();
				}

				// CSRF shield / anti-spam form filtering
				if ($this->cparams->getValue('csrfshield', 0) == 1)
				{
					$this->CSRFShield_BASIC();
				}
				elseif ($this->cparams->getValue('csrfshield', 0) == 2)
				{
					$this->CSRFShield_ADVANCED();
				}

				// RFIShield
				if ($this->cparams->getValue('rfishield', 1) == 1)
				{
					$this->RFIShield();
				}

				// DFIShield
				if ($this->cparams->getValue('dfishield', 1) == 1)
				{
					$this->DFIShield();
				}

				// UploadShield
				if ($this->cparams->getValue('uploadshield', 1) == 1)
				{
					$this->UploadShield();
				}

				// Anti-spam
				if ($this->cparams->getValue('antispam', 0) == 1)
				{
					$this->antiSpam();
				}

				// Disable template switching (tmpl)
				if ($this->cparams->getValue('tmpl', 0) == 1)
				{
					$this->disableTmplSwitch();
				}

				// Disable template switching (template)
				if ($this->cparams->getValue('template', 0) == 1)
				{
					$this->disableTemplateSwitch();
				}
			}

			// Custom URL redirection
			if ($this->cparams->getValue('urlredirection', 1) == 1)
			{
				$this->customRouter();
			}

			// Session optimizer
			if ($this->params->get('sesoptimizer', 0) == 1)
			{
				$this->sessionOptimizer();
			}

			// Session cleaner
			if ($this->params->get('sescleaner', 0) == 1)
			{
				$this->sessionCleaner();
			}

			// Cache cleaner
			if ($this->params->get('cachecleaner', 0) == 1)
			{
				$this->cacheCleaner();
			}

			// Cache expiration
			if ($this->params->get('cacheexpire', 0) == 1)
			{
				$this->cacheExpire();
			}

			// Temp-directory cleaning
			if ($this->params->get('cleantemp', 0) == 1)
			{
				$this->cleanTemp();
			}

			// Log purging
			if ($this->params->get('purgelog', 0) == 1)
			{
				$this->purgeLog();
			}
		}
	}

	public function onAfterRender()
	{
		$app = JFactory::getApplication();

		if ($this->isAdminAccessAttempt())
		{
			if ($this->cparams->getValue('twofactorauth', 0) == 1)
			{
				// Two factor authentication
				$this->twoFactorAuthentication_process();
			}
		}

		// Is it front-end?
		if (!in_array($app->getName(), array('administrator', 'admin')))
		{
			// CSRFShield form processing
			if ($this->cparams->getValue('csrfshield', 0) == 2)
			{
				$this->CSRFShield_PROCESS();
			}

			// Change the HARDCODED(!!!) generator tag in feeds
			if (($this->cparams->getValue('custgenerator', 0) == 1) && (JFactory::getApplication()->input->getCmd('format', 'html') == 'feed'))
			{
				$this->cloakGeneratorFeed();
			}
		}
	}

	public function onAfterRoute()
	{
		// Naughty, naughty trick
		if (JFactory::getSession()->get('block', false, 'com_admintools'))
		{
			// This is an underhanded way to short-circuit Joomla!'s internal router.
			JFactory::getApplication()->input->set('option', 'com_admintools');

			JRequest::set(array(
				'option' => 'com_admintools'
			), 'get', true);
		}
	}

	public function onAfterDispatch()
	{
		$app = JFactory::getApplication();

		// Back-end stuff
		if (in_array($app->getName(), array('administrator', 'admin')))
		{
			// Email on failed administrator access
			if (version_compare(JVERSION, '2.5.0', 'lt'))
			{
				$emailonfailedadmin = $this->cparams->getValue('emailonfailedadminlogin', '');
				if (!empty($emailonfailedadmin))
				{
					$this->emailOnFailedAdminLogin();
				}
			}
		}
		else
		{
			// Front-end stuff
			// Meta generator cloaking
			if ($this->cparams->getValue('custgenerator', 0) == 1)
			{
				$this->cloakGenerator();
			}
		}
	}

	/**
	 * Joomla! 1.5 failed login handler
	 *
	 * @param array $response
	 */
	public function onLoginFailure($response)
	{
		if ($this->cparams->getValue('trackfailedlogins', 0))
		{
			$this->trackFailedLogin();
		}
		else
		{
			$app = JFactory::getApplication();

			// Back-end stuff
			if (in_array($app->getName(), array('administrator', 'admin')))
			{
				// Email on failed administrator access
				$emailonfailedadmin = $this->cparams->getValue('emailonfailedadminlogin', '');

				if (!empty($emailonfailedadmin))
				{
					$this->emailOnFailedAdminLogin(true);
				}
			}
		}
	}

	/**
	 * Joomla! 1.6+ failed login handler
	 *
	 * @param array $response
	 */
	public function onUserLoginFailure($response)
	{
		$this->onLoginFailure($response);
	}

	/**
	 * User login event fired by Joomla! 1.5, redirected to the Joomla! 1.6 event
	 *
	 * @param JUser $user
	 * @param array $options
	 */
	public function onUserLogin($user, $options)
	{
		return $this->onLoginUser($user, $options);
	}

	/**
	 * User login event fired by Joomla! 1.6
	 *
	 * @param string $user
	 * @param array  $options
	 */
	public function onLoginUser($user, $options)
	{
		$app      = JFactory::getApplication();
		$instance = $this->_getUser($user, $options);

        $blockUser    = false;

        $isSuperAdmin = $instance->authorise('core.admin');
        $backendLogin = $instance->authorise('core.login.admin');

		// Disallow front-end Super Administrator login
		if ($this->cparams->getValue('nofesalogin', 0) == 1)
		{
			if (!in_array($app->getName(), array('administrator', 'admin')))
			{
				if($isSuperAdmin)
                {
                    $blockUser = true;
                }
			}
		}

        if(!$this->checkAwaySchedule() && $backendLogin)
        {
            $blockUser = true;
        }

        // Block User
        if ($blockUser)
        {
            $newopts = array();
            $app->logout($instance->id, $newopts);
            // Damn you, Joomla! Since 2.5.5 you have to close the session before throwing
            // an error, otherwise the user isn't logged out. What the hell?!
            $session = JFactory::getSession();
            $session->close();
            // Throw error
            $this->loadLanguage('plg_system_admintools');

            if (version_compare(JVERSION, '3.0', 'ge'))
            {
                throw new Exception(JText::_('JGLOBAL_AUTH_ACCESS_DENIED'), 403);
            }
            else
            {
                JError::raiseError(403, JText::_('JGLOBAL_AUTH_ACCESS_DENIED'));
            }

            return false;
        }

		return true;
	}

	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		// Save the user's signup IP
		if ($this->cparams->getValue('saveusersignupip', 0) == 1)
		{
			$process = true;

			// Only trigger on successful user creation
			if (!$success)
			{
				$process = false;
			}

			// Only trigger on new user creation, not subsequent edits
			if (!$isnew)
			{
				$process = false;
			}

			// Only trigger on front-end user creation.
			if (JFactory::getApplication()->isAdmin())
			{
				$process = false;
			}

			// Create a new user note
			if ($process)
			{
				// Get the user's ID
				$user_id = (int)$user['id'];

				// Get the IP address
				$ip = array_key_exists('REMOTE_ADDR', $_SERVER) ? htmlspecialchars($_SERVER['REMOTE_ADDR']) : '0.0.0.0';

				if ((strpos($ip, '::') === 0) && (strstr($ip, '.') !== false))
				{
					$ip = substr($ip, strrpos($ip, ':') + 1);
				}

				// Get the user agent string
				$user_agent = $_SERVER['HTTP_USER_AGENT'];

				// Get current date and time in database format
				JLoader::import('joomla.utilities.date');
				$now = new JDate();

				if (version_compare(JVERSION, '3.0', 'ge'))
				{
					$now = $now->toSql();
				}
				else
				{
					$now = $now->toSql();
				}

				// Load the component's administrator translation files
				$jlang = JFactory::getLanguage();
				$jlang->load('com_admintools', JPATH_ADMINISTRATOR, 'en-GB', true);
				$jlang->load('com_admintools', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
				$jlang->load('com_admintools', JPATH_ADMINISTRATOR, null, true);

				// Create and save the user note
				$userNote = (object)array(
					'user_id'         => $user_id,
					'catid'           => 0,
					'subject'         => JText::_('ATOOLS_LBL_WAF_SIGNUPIP_SUBJECT'),
					'body'            => JText::sprintf('ATOOLS_LBL_WAF_SIGNUPIP_BODY', $ip, $user_agent),
					'state'           => 1,
					'created_user_id' => 42,
					'created_time'    => $now
				);
				$db = JFactory::getDbo();
				$db->insertObject('#__user_notes', $userNote, 'id');
			}
		}

		// Reset the user's Two Factor Authentication on Joomla! 3.2+ when they reset their password
		$input = JFactory::getApplication()->input;
		$option = $input->getCmd('option', 'com_foobar');
		$task = $input->getCmd('task', 'default');

		if (($this->cparams->getValue('resetjoomlatfa', 0) == 1) && ($option == 'com_users') && ($task == 'complete'))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->update($db->qn('#__users'))
				->set(array(
					$db->qn('otpKey') . ' = ' . $db->q(''),
					$db->qn('otep') . ' = ' . $db->q(''),
				))
				->where($db->qn('id') . ' = ' . $db->q($user['id']));
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				// Do nothing if the query fails
			}
		}
	}

    public function onUserBeforeSave($olduser, $isnew, $user)
    {
        $domains = $this->cparams->getValue('blockedemaildomains', '');

        if(!$domains)
        {
            return true;
        }

        $domains = str_replace("\r", "\n", $domains);
        $domains = str_replace("\n\n", "\n", $domains);
        $domains = explode("\n", $domains);

        foreach($domains as $domain)
        {
            // The user used a blocked domain, let's prevent
            if(strpos($user['email'], trim($domain)) !== false)
            {
                // Load the component's administrator translation files
                $jlang = JFactory::getLanguage();
                $jlang->load('com_admintools', JPATH_ADMINISTRATOR, 'en-GB', true);
                $jlang->load('com_admintools', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
                $jlang->load('com_admintools', JPATH_ADMINISTRATOR, null, true);

                throw new Exception(JText::sprintf('ATOOLS_USER_BLOCKEDEMAILDOMAINS', $domain));
            }
        }

        return true;
    }

	/**
	 * Filters back-end access by IP. If the IP of the visitor is not included
	 * in the whitelist, he gets redirected to the home page
	 */
	private function adminIPFiltering()
	{
		// Let's get a list of allowed IP ranges
		$db = JFactory::getDBO();
		$sql = $db->getQuery(true)
			->select($db->qn('ip'))
			->from($db->qn('#__admintools_adminiplist'));
		$db->setQuery($sql);

		try
		{
			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				$ipTable = $db->loadColumn();
			}
			else
			{
				$ipTable = $db->loadResultArray();
			}
		}
		catch (Exception $e)
		{
			// Do nothing if the query fails
			$ipTable = null;
		}


		if (empty($ipTable))
		{
			return;
		}

		$inList = $this->IPinList($ipTable);

		if ($inList === false)
		{
			if (!$this->logBreaches('ipwl'))
			{
				return;
			}

			$autoban = $this->cparams->getValue('tsrenable', 0);

			if ($autoban)
			{
				$this->autoBan('ipwl');
			}

			$this->redirectAdminToHome();
		}
	}

	/**
	 * Filters visitor access by IP. If the IP of the visitor is included in the
	 * blacklist, he gets a 403 error
	 */
	private function IPFiltering()
	{

		// Let's get a list of blocked IP ranges
		$db = JFactory::getDBO();
		$sql = $db->getQuery(true)
			->select($db->qn('ip'))
			->from($db->qn('#__admintools_ipblock'));
		$db->setQuery($sql);

		try
		{
			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				$ipTable = $db->loadColumn();
			}
			else
			{
				$ipTable = $db->loadResultArray();
			}
		}
		catch (Exception $e)
		{
			// Do nothing if the query fails
			$ipTable = null;
		}

		if (empty($ipTable))
		{
			return;
		}

		$inList = $this->IPinList($ipTable);

		if ($inList === true)
		{

			$message = $this->cparams->getValue('custom403msg', '');

			if (empty($message))
			{
				$message = 'ADMINTOOLS_BLOCKED_MESSAGE';
			}

			// Merge the default translation with the current translation
			$jlang = JFactory::getLanguage();
			// Front-end translation
			$jlang->load('plg_system_admintools', JPATH_ADMINISTRATOR, 'en-GB', true);
			$jlang->load('plg_system_admintools', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
			$jlang->load('plg_system_admintools', JPATH_ADMINISTRATOR, null, true);
			// Do we have an override?
			$langOverride = $this->params->get('language_override', '');

			if (!empty($langOverride))
			{
				$jlang->load('plg_system_admintools', JPATH_ADMINISTRATOR, $langOverride, true);
			}

			if ((JText::_('ADMINTOOLS_BLOCKED_MESSAGE') == 'ADMINTOOLS_BLOCKED_MESSAGE') && ($message == 'ADMINTOOLS_BLOCKED_MESSAGE'))
			{
				$message = "Access Denied";
			}
			else
			{
				$message = JText::_($message);
			}

			// Show the 403 message
			if ($this->cparams->getValue('use403view', 0))
			{
				// Using a view
				if (!JFactory::getSession()->get('block', false, 'com_admintools') || JFactory::getApplication()->isAdmin())
				{
					// This is inside an if-block so that we don't end up in an infinite rediretion loop
					JFactory::getSession()->set('block', true, 'com_admintools');
					JFactory::getSession()->set('message', $message, 'com_admintools');
					JFactory::getSession()->close();

					$base = JURI::base();

					if (JFactory::getApplication()->isAdmin())
					{
						$base = rtrim($base);
						$base = substr($base, -13);
					}

					JFactory::getApplication()->redirect($base);
				}
			}
			else
			{
				if (JFactory::getApplication()->isAdmin())
				{
					// You can't use Joomla!'s error page in the admin area. Improvise!
					header('HTTP/1.1 403 Forbidden');
					echo $message;
					JFactory::getApplication()->close();
				}
				else
				{
					// Using Joomla!'s error page
					if (version_compare(JVERSION, '3.0', 'ge'))
					{
						throw new Exception($message, 403);
					}
					else
					{
						JError::raiseError(403, $message);
					}
				}
			}
		}
	}

	/**
	 * Checks if the secret word is set in the URL query, or redirects the user
	 * back to the home page.
	 */
	private function adminPasswordProtection()
	{
		$password = $this->cparams->getValue('adminpw', '');

		if (empty($password))
		{
			return;
		}

		$myURI = JURI::getInstance();
		// If the "password" query param is not defined, the default value
		// "thisisnotgood" is returned. If it is defined, it will return null or
		// the value after the equal sign.
		$check = $myURI->getVar($password, 'thisisnotgood');

		if ($check == 'thisisnotgood')
		{
			// Uh oh... Unauthorized access! Let's redirect the perp back to the
			// site's home page.
			if (!$this->logBreaches('adminpw'))
			{
				return;
			}

			$autoban = $this->cparams->getValue('tsrenable', 0);

			if ($autoban)
			{
				$this->autoBan('adminpw');
			}

			$this->redirectAdminToHome();
		}
	}

	/**
	 * Cloak the generator meta tag in regular pages
	 */
	private function cloakGenerator()
	{
		// Only do this for the front-end application
		$app = JFactory::getApplication();

		if ($app->getName() != 'site')
		{
			return;
		}

		$generator = $this->cparams->getValue('generator', '');

		if (empty($generator))
		{
			$generator = 'MYOB';
		} // Mind Your Own Business, peeping Tom!

		$document = JFactory::getDocument();
		$document->setGenerator($generator);
	}

	/**
	 * Cloak the generator meta tag in feeds. This method deals with the hardcoded Joomla! reference. Yeah, I know,
	 * hardcoded?
	 */
	private function cloakGeneratorFeed()
	{
		$generator = $this->cparams->getValue('generator', '');

		if (empty($generator))
		{
			$generator = 'MYOB';
		} // Mind Your Own Business, peeping Tom!

		$buffer = JResponse::getBody();

		$buffer = preg_replace('#<generator uri(.*)/generator>#iU', '<generator>' . $generator . '</generator>', $buffer);

		JResponse::setBody($buffer);
	}

	/**
	 * Disable template switching in the URL
	 */
	private function disableTmplSwitch()
	{
		$tmpl = JFactory::getApplication()->input->getCmd('tmpl', null);

		if (empty($tmpl))
		{
			return;
		}

		$whitelist = $this->cparams->getValue('tmplwhitelist', 'component,system');

		if (empty($whitelist))
		{
			$whitelist = 'component,system';
		}

		$temp = explode(',', $whitelist);
		$whitelist = array();

		foreach ($temp as $item)
		{
			$whitelist[] = trim($item);
		}

		$whitelist = array_merge(array('component', 'system'), $whitelist);

		if (!is_null($tmpl) && !in_array($tmpl, $whitelist))
		{
			if (!$this->logBreaches('tmpl'))
			{
				return;
			}

			$autoban = $this->cparams->getValue('tsrenable', 0);

			if ($autoban)
			{
				$this->autoBan('tmpl');
			}

			JRequest::setVar('tmpl', null);
			JFactory::getApplication()->input->set('tmpl', null);
		}
	}

	/**
	 * Disable template switching in the URL
	 */
	private function disableTemplateSwitch()
	{
		static $siteTemplates = array();

		$template = JFactory::getApplication()->input->getCmd('template', null);
		$block = true;

		if (!empty($template))
		{
			// Exception: existing site templates are allowed
			if (JFactory::getApplication()->input->getCmd('option', '') == 'com_mailto')
			{
				// com_email URLs in Joomla! 1.7 and later have template= defined; force $allowsitetemplate in this case
				$allowsitetemplate = true;
			}
			else
			{
				// Otherwise, allow only of the switch is set
				$allowsitetemplate = $this->cparams->getValue('allowsitetemplate', 0);
			}

			if ($allowsitetemplate)
			{
				if (empty($siteTemplates))
				{
					JLoader::import('joomla.filesystem.folder');
					$siteTemplates = JFolder::folders(JPATH_SITE . '/templates');
				}

				$block = !in_array($template, $siteTemplates);
			}

			if ($block)
			{
				if (!$this->logBreaches('template'))
				{
					return;
				}

				$autoban = $this->cparams->getValue('tsrenable', 0);

				if ($autoban)
				{
					$this->autoBan('template');
				}

				JRequest::setVar('template', null);

				if (version_compare(JVERSION, '3.2.0', 'ge'))
				{
					$template = JFactory::getApplication()->getTemplate(false);
					JFactory::getApplication()->input->set('template', $template);
				}
			}
		}
	}

	/**
	 * Fend off most common types of SQLi attacks. See the comments in the code
	 * for more security-minded information.
	 */
	private function SQLiShield()
	{
		// We filter all hashes separately to guard against underhanded injections.
		// For example, if the parameter registration to the $_REQUEST array is
		// GPCS, a GET variable will "hide" a POST variable during a POST request.
		// If the vulnerable component is, however, *explicitly* asking for the
		// POST variable, if we only check the $_REQUEST superglobal array we will
		// miss the attack: we will see the innocuous GET variable which is
		// registered to the $_REQUEST array due to higher precedence, while the
		// malicious POST payload makes it through to the component. When you are
		// talking about security you can leave NOTHING in the hands of Fate, or
		// it will come back to bite your sorry ass.
		$hashes = array('get', 'post');
		// Removing the jos_/#__ filter as it throws false positives on posts regarding SQL commands
		//$regex = '#[^\s]*([\s]|/\*(.*)\*/|;|\'|"|%22){1,}(union([\s]{1,}|/\*(.*)\*/){1,}select|select(([\s]{1,}|/\*(.*)\*/|`){1,}([\w]|_|-|\.|\*){1,}([\s]{1,}|/\*(.*)\*/|`){1,}(,){0,})*from([\s]{1,}|/\*(.*)\//){1,}[a-z0-9]{1,}_|(insert|replace)(([\s]{1,}|/\*(.*)\*/){1,})((low_priority|delayed|high_priority|ignore)([\s]{1,}|/\*(.*)\*/){1,}){0,}into|drop([\s]{1,}|/\*(.*)\*/){1,}(database|schema|event|procedure|function|trigger|view|index|server|(temporary([\s]{1,}|/\*(.*)\*/){1,}){0,1}table){1,1}([\s]{1,}|/\*(.*)\*/){1,}|update([\s]{1,}|/\*[^\w]*\/){1,}(low_priority([\s]{1,}|/\*[^\w]*\/){1,}|ignore([\s]{1,}|/\*[^\w]*\/){1,})?`?[\w]*_.*set|delete([\s]{1,}|/\*(.*)\*/){1,}((low_priority|quick|ignore)([\s]{1,}|/\*(.*)\*/){1,}){0,}from|benchmark([\s]{1,}|/\*(.*)\*/){0,}\(([\s]{1,}|/\*(.*)\*/){0,}[0-9]{1,}|\#__|jos_){1}#i';
		//$regex = '#[^\s]*([\s]|/\*(.*)\*/|;|\'|"|%22){1,}(union([\s]{1,}|/\*(.*)\*/){1,}select|select(([\s]{1,}|/\*(.*)\*/|`){1,}([\w]|_|-|\.|\*){1,}([\s]{1,}|/\*(.*)\*/|`){1,}(,){0,})*from([\s]{1,}|/\*(.*)\//){1,}[a-z0-9]{1,}_|(insert|replace)(([\s]{1,}|/\*(.*)\*/){1,})((low_priority|delayed|high_priority|ignore)([\s]{1,}|/\*(.*)\*/){1,}){0,}into|drop([\s]{1,}|/\*(.*)\*/){1,}(database|schema|event|procedure|function|trigger|view|index|server|(temporary([\s]{1,}|/\*(.*)\*/){1,}){0,1}table){1,1}([\s]{1,}|/\*(.*)\*/){1,}|update([\s]{1,}|/\*[^\w]*\/){1,}(low_priority([\s]{1,}|/\*[^\w]*\/){1,}|ignore([\s]{1,}|/\*[^\w]*\/){1,})?`?[\w]*_.*set|delete([\s]{1,}|/\*(.*)\*/){1,}((low_priority|quick|ignore)([\s]{1,}|/\*(.*)\*/){1,}){0,}from|benchmark([\s]{1,}|/\*(.*)\*/){0,}\(([\s]{1,}|/\*(.*)\*/){0,}[0-9]{1,}){1}#i';
		$regex = '#[^\s]*([\s]|/\*(.*)\*/|;|\'|"|%22){1,}(union([\s]{1,}|/\*(.*)\*/){1,}(all([\s]{1,}|/\*(.*)\*/){1,})?select|select(([\s]{1,}|/\*(.*)\*/|`){1,}([\w]|_|-|\.|\*){1,}([\s]{1,}|/\*(.*)\*/|`){1,}(,){0,})*from([\s]{1,}|/\*(.*)\//){1,}[a-z0-9]{1,}_|(insert|replace)(([\s]{1,}|/\*(.*)\*/){1,})((low_priority|delayed|high_priority|ignore)([\s]{1,}|/\*(.*)\*/){1,}){0,}into|drop([\s]{1,}|/\*(.*)\*/){1,}(database|schema|event|procedure|function|trigger|view|index|server|(temporary([\s]{1,}|/\*(.*)\*/){1,}){0,1}table){1,1}([\s]{1,}|/\*(.*)\*/){1,}|update([\s]{1,}|/\*[^\w]*\/){1,}(low_priority([\s]{1,}|/\*[^\w]*\/){1,}|ignore([\s]{1,}|/\*[^\w]*\/){1,})?`?[\w]*_.*set|delete([\s]{1,}|/\*(.*)\*/){1,}((low_priority|quick|ignore)([\s]{1,}|/\*(.*)\*/){1,}){0,}from|benchmark([\s]{1,}|/\*(.*)\*/){0,}\(([\s]{1,}|/\*(.*)\*/){0,}[0-9]{1,}){1}#i';

		foreach ($hashes as $hash)
		{
			switch ($hash)
			{
				case 'get':
					$source = $_GET;
					break;
				case 'post':
					$source = $_POST;
					break;
			}

			$input = new JInput($source);
			$ref = new ReflectionProperty($input, 'data');
			$ref->setAccessible(true);
			$allVars = $ref->getValue($input);

			if (empty($allVars))
			{
				continue;
			}

			if ($this->match_array($regex, $allVars))
			{
				$extraInfo = "Hash      : $hash\n";
				$extraInfo .= "Variables :\n";
				$extraInfo .= print_r($allVars, true);
				$extraInfo .= "\n";
				$this->blockRequest('sqlishield', null, $extraInfo);
			}
		}
	}

	/**
	 * Runs a RegEx match against a string or recursively against an array.
	 * In the case of an array, the first positive match against any level element
	 * of the array returns true and breaks the RegEx matching loop. If you pass
	 * any other data type except an array or string, it returns false.
	 *
	 * @param string $regex The regular expressions to feed to preg_match
	 * @param mixed  $array
	 *
	 * @return <type>
	 */
	private function match_array($regex, $array, $striptags = false)
	{
		$result = false;

		if (is_array($array))
		{
			foreach ($array as $key => $value)
			{
				if (is_array($value))
				{
					$result = $this->match_array($regex, $value, $striptags);
				}
				else
				{
					$v = $striptags ? strip_tags($value) : $value;
					$result = preg_match($regex, $v);
				}

				if ($result)
				{
					break;
				}
			}
		}
		elseif (is_string($array))
		{
			$v = $striptags ? strip_tags($array) : $array;
			$result = preg_match($regex, $v);
		}

		return $result;
	}

	/**
	 * The simplest anti-spam solution imagineable. Just blocks a request if a prohibited word is found.
	 */
	private function antiSpam()
	{
		$db = JFactory::getDBO();
		$sql = $db->getQuery(true)
			->select($db->qn('word'))
			->from($db->qn('#__admintools_badwords'))
			->group($db->qn('word'));
		$db->setQuery($sql);

		try
		{
			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				$badwords = $db->loadColumn();
			}
			else
			{
				$badwords = $db->loadResultArray();
			}
		}
		catch (Exception $e)
		{
			// Do nothing if the query fails
			$badwords = null;
		}

		if (empty($badwords))
		{
			return;
		}

		$hashes = array('get', 'post');

		foreach ($hashes as $hash)
		{
			switch ($hash)
			{
				case 'get':
					$source = $_GET;
					break;
				case 'post':
					$source = $_POST;
					break;
			}

			$input = new JInput($source);
			$ref = new ReflectionProperty($input, 'data');
			$ref->setAccessible(true);
			$allVars = $ref->getValue($input);

			if (empty($allVars))
			{
				continue;
			}

			foreach ($badwords as $word)
			{
				$regex = '#\b' . $word . '\b#i';

				if ($this->match_array($regex, $allVars, true))
				{
					$extraInfo = "Hash      : $hash\n";
					$extraInfo .= "Variables :\n";
					$extraInfo .= print_r($allVars, true);
					$extraInfo .= "\n";
					$this->blockRequest('antispam', null, $extraInfo);
				}
			}
		}
	}

	/**
	 * Performs custom redirections defined in the back-end of the component.
	 * It doesn't even require SEF to be turned on, he he!
	 */
	private function customRouter()
	{
		// Get the base path
		$basepath = ltrim(JURI::base(true), '/');

		$myURL = JURI::getInstance();
		$fullurl = ltrim($myURL->toString(array('path', 'query', 'fragment')), '/');
		$path = ltrim($myURL->getPath(), '/');

		$pathLength = strlen($path);
		$baseLength = strlen($basepath);

		if ($baseLength != 0)
		{
			if ($pathLength > $baseLength)
			{
				$path = ltrim(substr($path, $baseLength), '/');
			}
			elseif ($pathLength = $baseLength)
			{
				$path = '';
			}
		}

		$pathLength = strlen($fullurl);

		if ($baseLength != 0)
		{
			if ($pathLength > $baseLength)
			{
				$fullurl = ltrim(substr($fullurl, $baseLength), '/');
			}
			elseif ($pathLength = $baseLength)
			{
				$fullurl = '';
			}
		}

		$db = JFactory::getDBO();

		$sql = $db->getQuery(true)
			->select(array($db->qn('source'), $db->qn('keepurlparams')))
			->from($db->qn('#__admintools_redirects'))
			->where(
				'((' . $db->qn('dest') . ' = ' . $db->q($path) . ')' .
				' OR ' .
				'(' . $db->qn('dest') . ' = ' . $db->q($fullurl) . '))'
			)->where($db->qn('published') . ' = ' . $db->q('1'))
			->order($db->qn('ordering') . ' DESC');
		$db->setQuery($sql, 0, 1);

		try
		{
			$newURLStruct = $db->loadRow();
		}
		catch (Exception $e)
		{
			$newURLStruct = null;
		}

		if (!empty($newURLStruct))
		{
			list ($newURL, $keepQueryParams) = $newURLStruct;

			$new = JURI::getInstance($newURL);
			$host = $new->getHost();
			$fragment = $new->getFragment();
			$query = $new->getQuery();

			if (empty($host))
			{
				$base = JURI::getInstance(JURI::base());
				$new->setHost($base->getHost());
				$new->setPort($base->getPort());
				$new->setScheme($base->getScheme());
			}

			if ($keepQueryParams)
			{
				if (empty($query))
				{
					$new->setQuery($myURL->getQuery());
				}

				if (empty($fragment))
				{
					$new->setFragment($myURL->getFragment());
				}

				$new->setScheme($myURL->getScheme());
			}

			$path = $new->getPath();

			if (!empty($path))
			{
				if (substr($path, 0, 1) != '/')
				{
					$new->setPath('/' . $path);
				}
			}

			$targetURL = $new->toString();
			$app = JFactory::getApplication();
			$app->redirect($targetURL, '', 'message', true);
		}
	}

	private function sessionOptimizer()
	{
		$minutes = (int)$this->params->get('sesopt_freq', 0);

		if ($minutes <= 0)
		{
			return;
		}

		$lastJob = $this->getTimestamp('session_optimize');
		$nextJob = $lastJob + $minutes * 60;

		JLoader::import('joomla.utilities.date');
		$now = new JDate();

		if ($now->toUnix() >= $nextJob)
		{
			$this->setTimestamp('session_optimize');
			$this->sessionOptimize();
		}
	}

	/**
	 * Run the session cleaner (garbage collector) on a schedule
	 */
	private function sessionCleaner()
	{
		$minutes = (int)$this->params->get('ses_freq', 0);

		if ($minutes <= 0)
		{
			return;
		}

		$lastJob = $this->getTimestamp('session_clean');
		$nextJob = $lastJob + $minutes * 60;

		JLoader::import('joomla.utilities.date');
		$now = new JDate();

		if ($now->toUnix() >= $nextJob)
		{
			$this->setTimestamp('session_clean');
			$this->purgeSession();
		}
	}

	private function cacheCleaner()
	{
		$minutes = (int)$this->params->get('cache_freq', 0);

		if ($minutes <= 0)
		{
			return;
		}

		$lastJob = $this->getTimestamp('cache_clean');
		$nextJob = $lastJob + $minutes * 60;

		JLoader::import('joomla.utilities.date');
		$now = new JDate();

		if ($now->toUnix() >= $nextJob)
		{
			$this->setTimestamp('cache_clean');
			$this->purgeCache();
		}
	}

	private function cacheExpire()
	{
		$minutes = (int)$this->params->get('cacheexp_freq', 0);

		if ($minutes <= 0)
		{
			return;
		}

		$lastJob = $this->getTimestamp('cache_expire');
		$nextJob = $lastJob + $minutes * 60;

		JLoader::import('joomla.utilities.date');
		$now = new JDate();

		if ($now->toUnix() >= $nextJob)
		{
			$this->setTimestamp('cache_expire');
			$this->expireCache();
		}
	}

	private function cleanTemp()
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
	 * Checks if a non logged in user is trying to access the administrator
	 * application
	 *
	 * @param $onlySubmit bool Return true only if the form is submitted
	 */
	private function isAdminAccessAttempt($onlySubmit = false)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		if (in_array($app->getName(), array('administrator', 'admin')))
		{
			if ($user->guest)
			{
				$input = JFactory::getApplication()->input;
				$option = $input->getCmd('option', null);
				$task = $input->getCmd('task', null);

				if (($option == 'com_login') && ($task == 'login'))
				{
					// Check for malicious direct post without a valid token
					// In this case, we "cheat" by pretending that it is a
					// login attempt we need to filter. If it's a legitimate
					// login request (username & password posted) we stop
					// filtering so as to allow Joomla! to parse the login
					// request.
					JLoader::import('joomla.utiltiites.utility');
					$token = null;

					if (class_exists('JUtility'))
					{
						if (method_exists('JUtility', 'getToken'))
						{
							$token = JUtility::getToken();
						}
					}

					if (is_null($token))
					{
						$token = JFactory::getSession()->getToken();
					}

					$token = JFactory::getApplication()->input->get($token, false, 'raw');

					if (!$onlySubmit)
					{
						if (($token === false) && method_exists('JSession', 'checkToken'))
						{
							return !JSession::checkToken('request');
						}
						else
						{
							return $token === false;
						}
					}
					else
					{
						if (($token === false) && method_exists('JSession', 'checkToken'))
						{
							return JSession::checkToken('request');
						}
						else
						{
							return $token !== false;
						}
					}
				}
				else
				{
					// Back-end login attempt
					if ($onlySubmit)
					{
						return false;
					}

					return true;
				}
			}
			else
			{
				// Logged in admin user
				return false;
			}
		}
		else
		{
			// The request doesn't belong to the Administrator application
			return false;
		}
	}

	/**
	 * Checks if the user's IP is contained in a list of IPs or IP expressions
	 *
	 * @param array $ipTable The list of IP expressions
	 *
	 * @return null|bool True if it's in the list, null if the filtering can't proceed
	 */
	private function IPinList($ipTable = array())
	{
		// Get our IP address
		$ip = array_key_exists('REMOTE_ADDR', $_SERVER) ? htmlspecialchars($_SERVER['REMOTE_ADDR']) : '0.0.0.0';

		return AdmintoolsHelperIp::IPinList($ip, $ipTable);
	}

	/**
	 * Blocks access to com_install
	 */
	private function blockInstall()
	{
		$option = JFactory::getApplication()->input->getCmd('option', '');

		if (!in_array($option, array('com_installer', 'com_plugins')))
		{
			return;
		}

		$jlang = JFactory::getLanguage();
		$jlang->load('lib_joomla', JPATH_ADMINISTRATOR, 'en-GB', true);
		$jlang->load('lib_joomla', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
		$jlang->load('lib_joomla', JPATH_ADMINISTRATOR, null, true);

		$blockSetting = $this->cparams->getValue('blockinstall', 0);

		if ($blockSetting == 0)
		{
			return;
		}

		$user = JFactory::getUser();

		if (!$user->guest)
		{
			// Joomla! 1.6 -- Only Super Users have the core.admin global privilege
			$coreAdmin = $user->authorise('core.admin');

			if (!empty($coreAdmin) && ($coreAdmin === true))
			{
				$coreAdmin = true;
			}
			else
			{
				$coreAdmin = false;
			}

			if (($blockSetting == 1) && ($coreAdmin))
			{
				return;
			}

			$this->loadLanguage('plg_system_admintools');

			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				throw new Exception(JText::_('JGLOBAL_AUTH_ACCESS_DENIED'), 403);
			}
			else
			{
				JError::raiseError(403, JText::_('JGLOBAL_AUTH_ACCESS_DENIED'));
			}
		}
	}

	/**
	 * Disabled creating new admins or updating new ones
	 */
	private function noNewAdmins()
	{
		$input = JFactory::getApplication()->input;
		$option = $input->getCmd('option', '');
		$task = $input->getCmd('task', '');
		$gid = $input->getInt('gid', 0);

		if ($option != 'com_users')
		{
			return;
		}

		$jform = JFactory::getApplication()->input->get('jform', array(), 'array');

		if (($task == 'save') || ($task == 'apply') || ($task == 'user.apply'))
		{
			// Joomla! 1.6
			if (empty($jform))
			{
				// Not editing, just core devs using the same task throughout the component, dammit
				return;
			}

			$groups = $jform['groups'];

			$user = JFactory::getUser((int)$jform['id']);

			if (!empty($user->groups))
			{
				foreach ($user->groups as $title => $gid)
				{
					if (!in_array($gid, $groups))
					{
						$groups[] = $gid;
					}
				}
			}

			$isAdmin = false;

			if (!empty($groups))
			{
				foreach ($groups as $group)
				{
					// First try to see if the group has explicit backend login privileges
					$backend = JAccess::checkGroup($group, 'core.login.admin', 1);

					// If not, is it a Super Admin (ergo inherited privileges)?
					if (is_null($backend))
					{
						$backend = JAccess::checkGroup($group, 'core.admin', 1);
					}

					$isAdmin |= $backend;
				}
			}

			if ($isAdmin)
			{
				$jlang = JFactory::getLanguage();
				$jlang->load('joomla', JPATH_ROOT, 'en-GB', true);
				$jlang->load('joomla', JPATH_ROOT, $jlang->getDefault(), true);
				$jlang->load('joomla', JPATH_ROOT, null, true);

				$this->loadLanguage('plg_system_admintools');

				if (version_compare(JVERSION, '3.0', 'ge'))
				{
					throw new Exception(JText::_('JGLOBAL_AUTH_ACCESS_DENIED'), '403');
				}
				else
				{
					JError::raiseError(403, JText::_('JGLOBAL_AUTH_ACCESS_DENIED'));
				}
			}
		}
	}

	/**
	 * Redirects an administrator request back to the home page
	 */
	private function redirectAdminToHome()
	{
		// Get the current URI
		$myURI = JURI::getInstance();
		$path = $myURI->getPath();
		// Pop the administrator from the URI path
		$path_parts = explode('/', $path);
		$path_parts = array_slice($path_parts, 0, count($path_parts) - 2);
		$path = implode('/', $path_parts);
		$myURI->setPath($path);
		// Unset any query parameters
		$myURI->setQuery('');
		// Redirect
		$app = JFactory::getApplication();
		$app->redirect($myURI->toString());
	}

	/**
	 * Blocks the request in progress and, optionally, logs the details of the
	 * blocked request for the admin to review later
	 *
	 * @param string $reason                   Block reason code
	 * @param string $message                  The message to be shown to the user
	 * @param string $extraLogInformation      Extra information to be written to the text log file
	 * @param string $extraLogTableInformation Extra information to be written to the extradata field of the log table (useful for JSON format)
	 */
	private function blockRequest($reason = 'other', $message = '', $extraLogInformation = '', $extraLogTableInformation = '')
	{
		if (empty($message))
		{
			$customMessage = $this->cparams->getValue('custom403msg', '');

			if (!empty($customMessage))
			{
				$message = $customMessage;
			}
			else
			{
				$message = 'ADMINTOOLS_BLOCKED_MESSAGE';
			}
		}

		$r = $this->logBreaches($reason, $extraLogInformation, $extraLogTableInformation);

		if (!$r)
		{
			return;
		}

		$autoban = $this->cparams->getValue('tsrenable', 0);

		if ($autoban)
		{
			$this->autoBan($reason);
		}

		// Merge the default translation with the current translation
		$jlang = JFactory::getLanguage();
		// Front-end translation
		$jlang->load('plg_system_admintools', JPATH_ADMINISTRATOR, 'en-GB', true);
		$jlang->load('plg_system_admintools', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
		$jlang->load('plg_system_admintools', JPATH_ADMINISTRATOR, null, true);

		if ((JText::_('ADMINTOOLS_BLOCKED_MESSAGE') == 'ADMINTOOLS_BLOCKED_MESSAGE') && ($message == 'ADMINTOOLS_BLOCKED_MESSAGE'))
		{
			$message = "Access Denied";
		}
		else
		{
			$message = JText::_($message);
		}

		// Show the 403 message
		if ($this->cparams->getValue('use403view', 0))
		{
			// Using a view
			if (!JFactory::getSession()->get('block', false, 'com_admintools'))
			{
				// This is inside an if-block so that we don't end up in an infinite rediretion loop
				JFactory::getSession()->set('block', true, 'com_admintools');
				JFactory::getSession()->set('message', $message, 'com_admintools');
				JFactory::getSession()->close();
				JFactory::getApplication()->redirect(JURI::base());
			}
		}
		else
		{
			// Using Joomla!'s error page
			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				throw new Exception($message, 403);
			}
			else
			{
				JError::raiseError(403, $message);
			}
		}
	}

	private function logBreaches($reason, $extraLogInformation = '', $extraLogTableInformation = '')
	{
		$reasons_nolog = $this->cparams->getValue('reasons_nolog', 'geoblocking');
		$reasons_noemail = $this->cparams->getValue('reasons_noemail', 'geoblocking');
		$whitelist_domains = $this->cparams->getValue('whitelist_domains', '.googlebot.com,.search.msn.com');

		$reasons_nolog = explode(',', $reasons_nolog);
		$reasons_noemail = explode(',', $reasons_noemail);
		$whitelist_domains = explode(',', $whitelist_domains);

		// === SANITY CHECK - BEGIN ===
		// Get our IP address
		$ip = array_key_exists('REMOTE_ADDR', $_SERVER) ? htmlspecialchars($_SERVER['REMOTE_ADDR']) : '0.0.0.0';

		if ((strpos($ip, '::') === 0) && (strstr($ip, '.') !== false))
		{
			$ip = substr($ip, strrpos($ip, ':') + 1);
		}

		// No point continuing if we can't get an address, right?
		if (empty($ip) || ($ip == '0.0.0.0'))
		{
			return false;
		}

		// Make sure it's not an IP in the safe list
		$safeIPs = $this->cparams->getValue('neverblockips', '');

		if (!empty($safeIPs))
		{
			$safeIPs = explode(',', $safeIPs);

			if (!empty($safeIPs))
			{
				if ($this->IPinList($safeIPs))
				{
					return false;
				}
			}
		}

		// Make sure we don't have a list in the administrator white list
		if ($this->cparams->getValue('ipwl', 0) == 1)
		{
			$db = JFactory::getDBO();
			$sql = $db->getQuery(true)
				->select($db->qn('ip'))
				->from($db->qn('#__admintools_adminiplist'));
			$db->setQuery($sql);

			try
			{
				if (version_compare(JVERSION, '3.0', 'ge'))
				{
					$ipTable = $db->loadColumn();
				}
				else
				{
					$ipTable = $db->loadResultArray();
				}
			}
			catch (Exception $e)
			{
				$ipTable = null;
			}

			if (!empty($ipTable))
			{
				if ($this->IPinList($ipTable))
				{
					return false;
				}
			}
		}

		// Make sure this IP doesn't resolve to a whitelisted domain
		if (!empty($whitelist_domains))
		{
			$remote_domain = @gethostbyaddr($ip);

			if (!empty($remote_domain))
			{
				foreach ($whitelist_domains as $domain)
				{
					$domain = trim($domain);

					if (strrpos($remote_domain, $domain) !== false)
					{
						return true;
					}
				}
			}
		}

		// === SANITY CHECK - END ===


		// DO I have any kind of log? Let's get some extra info
		if (
			($this->cparams->getValue('logbreaches', 0) && !in_array($reason, $reasons_nolog)) ||
			($this->cparams->getValue('emailbreaches', '') && !in_array($reason, $reasons_noemail))
		)
		{
			$uri = JURI::getInstance();
			$url = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment'));

			JLoader::import('joomla.utilities.date');
			$date = new JDate();

			$user = JFactory::getUser();

			if ($user->guest)
			{
				$username = 'Guest';
			}
			else
			{
				$username = $user->username . ' (' . $user->name . ' <' . $user->email . '>)';
			}

			$country = '';
			$continent = '';

			if (class_exists('AkeebaGeoipProvider'))
			{
				$geoip = new AkeebaGeoipProvider();
				$country = $geoip->getCountryCode($ip);
				$continent = $geoip->getContinent($ip);
			}

			if (empty($country))
			{
				$country = '(unknown country)';
			}

			if (empty($continent))
			{
				$continent = '(unknown continent)';
			}
		}

		if ($this->cparams->getValue('logbreaches', 0) && !in_array($reason, $reasons_nolog))
		{
			// Logging to file
			$config = JFactory::getConfig();

			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				$logpath = $config->get('log_path');
			}
			else
			{
				$logpath = $config->getValue('log_path');
			}

			$fname = $logpath . DIRECTORY_SEPARATOR . 'admintools_breaches.log';

			// -- Check the file size. If it's over 1Mb, archive and start a new log.
			if (@file_exists($fname))
			{
				$fsize = filesize($fname);

				if ($fsize > 1048756)
				{
					if (@file_exists($fname . '.1'))
					{
						unlink($fname . '.1');
					}

					@copy($fname, $fname . '.1');
					@unlink($fname);
				}
			}

			// -- Log the exception
			$fp = @fopen($fname, 'at');

			if ($fp !== false)
			{
				fwrite($fp, str_repeat('-', 79) . "\n");
				fwrite($fp, "Blocking reason: " . $reason . "\n" . str_repeat('-', 79) . "\n");
				fwrite($fp, 'Date/time : ' . gmdate('Y-m-d H:i:s') . " GMT\n");
				fwrite($fp, 'URL       : ' . $url . "\n");
				fwrite($fp, 'User      : ' . $username . "\n");
				fwrite($fp, 'IP        : ' . $ip . "\n");
				fwrite($fp, 'Country   : ' . $country . "\n");
				fwrite($fp, 'Continent : ' . $continent . "\n");
				fwrite($fp, 'UA        : ' . $_SERVER['HTTP_USER_AGENT'] . "\n");

				if (!empty($extraLogInformation))
				{
					fwrite($fp, $extraLogInformation . "\n");
				}

				fwrite($fp, "\n\n");
				fclose($fp);
			}

			// ...and write a record to the log table
			$db = JFactory::getDBO();
			$logEntry = (object)array(
				'logdate'   => $date->toSql(),
				'ip'        => $ip,
				'url'       => $url,
				'reason'    => $reason,
				'extradata' => $extraLogTableInformation,
			);

			try
			{
				$db->insertObject('#__admintools_log', $logEntry);
			}
			catch (Exception $e)
			{
				// Do nothing if the query fails
			}
		}

		$emailbreaches = $this->cparams->getValue('emailbreaches', '');

		if (!empty($emailbreaches) && !in_array($reason, $reasons_noemail))
		{
			// Load the component's administrator translation files
			$jlang = JFactory::getLanguage();
			$jlang->load('com_admintools', JPATH_ADMINISTRATOR, 'en-GB', true);
			$jlang->load('com_admintools', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
			$jlang->load('com_admintools', JPATH_ADMINISTRATOR, null, true);

			// Get the site name
			$config = JFactory::getConfig();

			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				$sitename = $config->get('sitename');
			}
			else
			{
				$sitename = $config->getValue('config.sitename');
			}

			// Create a link to lookup the IP
			$ip_link = $this->cparams->getValue('iplookupscheme', 'http') . '://' . $this->cparams->getValue('iplookup', 'ip-lookup.net/index.php?ip={ip}');
			$ip_link = str_replace('{ip}', $ip, $ip_link);

			// Get the reason in human readable format
			$txtReason = JText::_('ATOOLS_LBL_REASON_' . strtoupper($reason));

			// Get extra information
			if ($extraLogTableInformation)
			{
				list($logReason,) = explode('|', $extraLogTableInformation);
				$txtReason .= " ($logReason)";
			}

			// Send the email
			$mailer = JFactory::getMailer();

			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				$mailfrom = $config->get('mailfrom');
				$fromname = $config->get('fromname');
			}
			else
			{
				$mailfrom = $config->getValue('config.mailfrom');
				$fromname = $config->getValue('config.fromname');
			}

			// Let's get the most suitable email template
			$template = $this->getEmailTemplate($reason);

			// Got no template, the user didn't published any email template, or the template doesn't want us to
			// send a notification email. Anyway, let's stop here
			if (!$template)
			{
				return true;
			}
			else
			{
				$subject = $template[0];
				$body = $template[1];
			}

			$tokens = array(
				'[SITENAME]'  => $sitename,
				'[REASON]'    => $txtReason,
				'[DATE]'      => gmdate('Y-m-d H:i:s') . " GMT",
				'[URL]'       => $url,
				'[USER]'      => $username,
				'[IP]'        => $ip,
				'[LOOKUP]'    => '<a href="' . $ip_link . '">IP Lookup</a>',
				'[COUNTRY]'   => $country,
				'[CONTINENT]' => $continent,
				'[UA]'        => $_SERVER['HTTP_USER_AGENT']
			);

			$subject = str_replace(array_keys($tokens), array_values($tokens), $subject);
			$body    = str_replace(array_keys($tokens), array_values($tokens), $body);

			$mailer->isHtml(true);
			$mailer->setSender(array($mailfrom, $fromname));
			$mailer->addRecipient($this->cparams->getValue('emailbreaches', ''));
			$mailer->setSubject($subject);
			$mailer->setBody($body);
			$mailer->Send();
		}

		return true;
	}

	/**
	 * Optimizes the session table. The idea is that as users log in and out,
	 * vast amounts of records are created and deleted, slowly fragmenting the
	 * underlying database file and slowing down user session operations. At
	 * some point, your site might even crash. By doing a periodic optimization
	 * of the sessions table this is prevented. An optimization per hour should
	 * be adequate, even for huge sites.
	 *
	 * Note: this is not necessary if you're not using the database to save
	 * session data. Using disk files, memcache, APC or other alternative caches
	 * has no impact on your database performance. In this case you should not
	 * enable this option, as you have nothing to gain.
	 */
	private function sessionOptimize()
	{
		$db = JFactory::getDBO();

		// First, make sure this is MySQL!
		$dbClass = get_class($db);

		if (substr($dbClass, 0, 15) == 'JDatabaseDriver')
		{
			$dbClass = substr($dbClass, 15);
		}
		else
		{
			$dbClass = str_replace('JDatabase', '', $dbClass);
		}

		if (!in_array(strtolower($dbClass), array('mysql', 'mysqli')))
		{
			return;
		}

		$db->setQuery('CHECK TABLE ' . $db->quoteName('#__session'));
		$result = $db->loadObjectList();

		$isOK = false;

		if (!empty($result))
		{
			foreach ($result as $row)
			{
				if (($row->Msg_type == 'status') && (
						($row->Msg_text == 'OK') ||
						($row->Msg_text == 'Table is already up to date')
					)
				)
				{
					$isOK = true;
				}
			}
		}

		// Run a repair only if it is required
		if (!$isOK)
		{
			// The table needs repair
			$db->setQuery('REPAIR TABLE ' . $db->quoteName('#__session'));
			$db->execute();
		}

		// Finally, optimize
		$db->setQuery('OPTIMIZE TABLE ' . $db->quoteName('#__session'));
		$db->execute();
	}

	/**
	 * Purges expired sessions
	 */
	private function purgeSession()
	{
		JLoader::import('joomla.session.session');

		$options = array();

		$conf = JFactory::getConfig();

		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$handler = $conf->get('session_handler', 'none');
		}
		else
		{
			$handler = $conf->getValue('config.session_handler', 'none');
		}

		// config time is in minutes
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$options['expire'] = ($conf->get('lifetime')) ? $conf->get('lifetime') * 60 : 900;
		}
		else
		{
			$options['expire'] = ($conf->getValue('config.lifetime')) ? $conf->getValue('config.lifetime') * 60 : 900;
		}

		$storage = JSessionStorage::getInstance($handler, $options);
		$storage->gc($options['expire']);
	}

	/**
	 * Completely purges the cache
	 */
	private function purgeCache()
	{
		// Site client
		$client = JApplicationHelper::getClientInfo(0);

		$er = @error_reporting(0);
		$cache = JFactory::getCache('');
		$cache->clean('sillylongnamewhichcantexistunlessyouareacompletelyparanoiddeveloperinwhichcaseyoushouldnotbewritingsoftwareokay', 'notgroup');
		@error_reporting($er);
	}

	/**
	 * Expires cache items
	 */
	private function expireCache()
	{
		$er = @error_reporting(0);
		$cache = JFactory::getCache('');
		$cache->gc();
		@error_reporting($er);
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

	/**
	 * Sets the timestamp for a specific scheduling task
	 *
	 * @param $key string The scheduling task key to set the timestamp parameter for
	 */
	private function setTimestamp($key)
	{
		JLoader::import('joomla.utilities.date');
		$date = new JDate();

		$pk = 'timestamp_' . $key;
		$timestamp = $date->toUnix();
		$oldTimestamp = $this->getTimestamp($key); // Make sure the array is populated
		$db = JFactory::getDbo();

		// This is necessary because using an UPDATE query results in Joomla!
		// throwing a JLIB_APPLICATION_ERROR_COMPONENT_NOT_LOADING or blank
		// page. HUH!!!!!!
		$query = $db->getQuery(true)
			->delete($db->qn('#__admintools_storage'))
			->where($db->qn('key') . ' = ' . $db->q($pk));
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			// If that failed, sorry, we can't set the timestamp :(
			return;
		}

		$query = $db->getQuery(true)
			->insert($db->qn('#__admintools_storage'))
			->columns(array(
				$db->qn('key'),
				$db->qn('value'),
			))->values(
				$db->q($pk) . ', ' . $db->q($timestamp)
			);
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			// If that failed, sorry, we can't set the timestamp :(
			return;
		}

		$this->timestamps[$pk] = $timestamp;
	}

	/**
	 * Gets the last recorded timestamp for a specific scheduling task
	 *
	 * @param $key string The scheduling task key to retrieve the timestamp parameter
	 *
	 * @return int UNIX timestamp
	 */
	private function getTimestamp($key)
	{
		if (empty($this->timestamps))
		{
			$this->loadTimestamps();
		}

		JLoader::import('joomla.utilities.date');
		$pk = 'timestamp_' . $key;

		if (!array_key_exists($pk, $this->timestamps))
		{
			return 0;
		}

		return $this->timestamps[$pk];
	}

	/**
	 * Sends an email upon accessing an administrator page other than the login screen
	 */
	private function emailOnAdminLogin()
	{
		// Make sure we don't fire when someone is still in the login page
		if ($this->isAdminAccessAttempt())
		{
			return;
		}

		// Double check
		$user = JFactory::getUser();

		if ($user->guest)
		{
			return;
		}

		// Check if the session flag is set (avoid sending thousands of emails!)
		$session = JFactory::getSession();
		$flag = $session->get('waf.loggedin', 0, 'plg_admintools');

		if ($flag == 1)
		{
			return;
		}

		// Load the component's administrator translation files
		$jlang = JFactory::getLanguage();
		$jlang->load('com_admintools', JPATH_ADMINISTRATOR, 'en-GB', true);
		$jlang->load('com_admintools', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
		$jlang->load('com_admintools', JPATH_ADMINISTRATOR, null, true);

		// Get the username
		$username = $user->username;
		// Get the site name
		$config = JFactory::getConfig();

		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$sitename = $config->get('sitename');
		}
		else
		{
			$sitename = $config->getValue('config.sitename');
		}

		// Get the IP address
		$ip = array_key_exists('REMOTE_ADDR', $_SERVER) ? htmlspecialchars($_SERVER['REMOTE_ADDR']) : '0.0.0.0';

		if ((strpos($ip, '::') === 0) && (strstr($ip, '.') !== false))
		{
			$ip = substr($ip, strrpos($ip, ':') + 1);
		}

		$country = '';
		$continent = '';

		if (class_exists('AkeebaGeoipProvider'))
		{
			$geoip = new AkeebaGeoipProvider();
			$country = $geoip->getCountryCode($ip);
			$continent = $geoip->getContinent($ip);
		}

		if (empty($country))
		{
			$country = '(unknown country)';
		}

		if (empty($continent))
		{
			$continent = '(unknown continent)';
		}

		// Construct the replacement table
		$substitutions = array(
			'[SITENAME]'  => $sitename,
			'[USERNAME]'  => $username,
			'[IP]'        => $ip,
			'[UASTRING]'  => $_SERVER['HTTP_USER_AGENT'],
			'[COUNTRY]'   => $country,
			'[CONTINENT]' => $continent
		);

		$subject = JText::_('ATOOLS_LBL_WAF_EMAILADMINLOGIN_SUBJECT_21');
		$body = JText::_('ATOOLS_LBL_WAF_EMAILADMINLOGIN_BODY_21');

		foreach ($substitutions as $k => $v)
		{
			$subject = str_replace($k, $v, $subject);
			$body = str_replace($k, $v, $body);
		}

		// Send the email
		$mailer = JFactory::getMailer();

		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$mailfrom = $config->get('mailfrom');
			$fromname = $config->get('fromname');
		}
		else
		{
			$mailfrom = $config->getValue('config.mailfrom');
			$fromname = $config->getValue('config.fromname');
		}

		$mailer->setSender(array($mailfrom, $fromname));
		$mailer->addRecipient($this->cparams->getValue('emailonadminlogin', ''));
		$mailer->setSubject($subject);
		$mailer->setBody($body);
		$mailer->Send();

		// Set the flag to prevent sending more emails
		$session->set('waf.loggedin', 1, 'plg_admintools');
	}

	/**
	 * Sends an email upon a failed administrator login
	 *
	 * @param $forcedFailure bool Wt to true to force a login failure trigger
	 */
	private function emailOnFailedAdminLogin($forcedFailure = false)
	{
		// Make sure we don't fire unless someone is still in the login page
		$user = JFactory::getUser();

		if (!$user->guest)
		{
			return;
		}

		$input = JFactory::getApplication()->input;
		$option = $input->getCmd('option');
		$task = $input->getCmd('task');

		if (($option != 'com_login') && !$forcedFailure)
		{
			return;
		}

		if (($task == 'login') || $forcedFailure)
		{
			// If we are STILL in the login task WITHOUT a valid user, we had a login failure.
			// Load the component's administrator translation files
			$jlang = JFactory::getLanguage();
			$jlang->load('com_admintools', JPATH_ADMINISTRATOR, 'en-GB', true);
			$jlang->load('com_admintools', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
			$jlang->load('com_admintools', JPATH_ADMINISTRATOR, null, true);

			// Fetch the username
			$username = JFactory::getApplication()->input->getString('username');

			// Get the site name
			$config = JFactory::getConfig();

			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				$sitename = $config->get('sitename');
			}
			else
			{
				$sitename = $config->getValue('config.sitename');
			}

			// Get the IP address
			$ip = array_key_exists('REMOTE_ADDR', $_SERVER) ? htmlspecialchars($_SERVER['REMOTE_ADDR']) : '0.0.0.0';

			if ((strpos($ip, '::') === 0) && (strstr($ip, '.') !== false))
			{
				$ip = substr($ip, strrpos($ip, ':') + 1);
			}

			// Send the email
			$mailer = JFactory::getMailer();

			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				$mailfrom = $config->get('mailfrom');
				$fromname = $config->get('fromname');
			}
			else
			{
				$mailfrom = $config->getValue('config.mailfrom');
				$fromname = $config->getValue('config.fromname');
			}

			$mailer->setSender(array($mailfrom, $fromname));
			$mailer->addRecipient($this->cparams->getValue('emailonfailedadminlogin', ''));
			$mailer->setSubject(JText::sprintf('ATOOLS_LBL_WAF_EMAILADMINFAILEDLOGIN_SUBJECT', $username, $sitename));
			$mailer->setBody(JText::sprintf('ATOOLS_LBL_WAF_EMAILADMINFAILEDLOGIN_BODY', $username, $sitename, $ip, $sitename));
			$mailer->Send();
		}
	}

	private function trackFailedLogin()
	{
		$user = JFactory::getApplication()->input->getCmd('username', null);
		$pass = JFactory::getApplication()->input->getCmd('password', null);

		if (empty($pass))
		{
			$pass = JFactory::getApplication()->input->getCmd('passwd', null);
		}

		$extraInfo = null;

		if (!empty($user))
		{
			if ($this->cparams->getValue('showpwonloginfailure', 1))
			{
				$extraInfo = 'Username: ' . $user . ' -- Password: ' . $pass;
			}
			else
			{
				$extraInfo = 'Username: ' . $user;
			}
		}

		$this->logBreaches('loginfailure', $user, $extraInfo);

        $this->deactivateUser($user);

		$autoban = $this->cparams->getValue('tsrenable', 0);

		if ($autoban)
		{
			$this->autoBan('loginfailure');
		}
	}

	/**
	 * Protects against a malicious User Agent string
	 */
	private function MUAShield()
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
			$this->blockRequest('muashield');
		}
	}

	private function CSRFShield_BASIC()
	{
		// Do not activate on GET, HEAD and TRACE requests
		$method = strtoupper($_SERVER['REQUEST_METHOD']);

		if (in_array($method, array('GET', 'HEAD', 'TRACE')))
		{
			return;
		}

		// Check the referer, if available
		$valid = true;
		$referer = array_key_exists('HTTP_REFERER', $_SERVER) ? $_SERVER['HTTP_REFERER'] : '';

		if (!empty($referer))
		{
			$jRefURI = JURI::getInstance($referer);
			$refererURI = $jRefURI->toString(array('host', 'port'));

			$jSiteURI = JURI::getInstance();
			$siteURI = $jSiteURI->toString(array('host', 'port'));

			$valid = ($siteURI == $refererURI);
		}

		if (!$valid)
		{
			$this->blockRequest('csrfshield');
		}
	}

	/**
	 * Applies basic HTTP referer filtering to POST, PUT, DELETE etc HTTP requests,
	 * usually associated with form submission.
	 */
	private function CSRFShield_GetFieldName()
	{
		static $fieldName = null;

		if (empty($fieldName))
		{
			$config = JFactory::getConfig();

			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				$sitename = $config->get('sitename');
				$secret = $config->get('secret');
			}
			else
			{
				$sitename = $config->getValue('config.sitename');
				$secret = $config->getValue('config.secret');
			}

			$fieldName = md5($sitename . $secret);
		}

		return $fieldName;
	}

	/**
	 * Applies advanced reverse CAPTCHA checks to POST, PUT, DELETE etc HTTP
	 * requests, usually associated with form submission.
	 */
	private function CSRFShield_ADVANCED()
	{
		// Do not activate on GET, HEAD and TRACE requests
		$method = strtoupper($_SERVER['REQUEST_METHOD']);

		if (in_array($method, array('GET', 'HEAD', 'TRACE')))
		{
			return;
		}

		// Check for the existence of a hidden field
		$valid = true;
		$hashes = array('get', 'post');
		$hiddenFieldName = $this->CSRFShield_GetFieldName();

		foreach ($hashes as $hash)
		{
			$source = ($hash == 'get') ? $_GET : $_POST;
			$input = new JInput($source);
			$ref = new ReflectionProperty($input, 'data');
			$ref->setAccessible(true);
			$allVars = $ref->getValue($input);

			if (!array_key_exists($hiddenFieldName, $allVars))
			{
				continue;
			}

			if (!empty($allVars[$hiddenFieldName]))
			{
				$this->blockRequest('csrfshield');
			}
		}
	}

	/**
	 * Processes all forms on the page, adding a reverse CAPTCHA field
	 * for advanced filtering
	 */
	private function CSRFShield_PROCESS()
	{
		$hiddenFieldName = $this->CSRFShield_GetFieldName();

		$buffer = JResponse::getBody();
		$buffer = preg_replace('#<[\s]*/[\s]*form[\s]*>#iU', '<input type="text" name="' . $hiddenFieldName . '" value="" style="float: left; position: absolute; z-index: 1000000; left: -10000px; top: -10000px;" /></form>', $buffer);
		JResponse::setBody($buffer);
	}

	/**
	 * Processes all forms on the page, adding a reverse CAPTCHA field
	 * for advanced filtering
	 */
	private function twoFactorAuthentication_process()
	{
		// Load the component's administrator translation files
		$jlang = JFactory::getLanguage();
		$jlang->load('com_admintools', JPATH_ADMINISTRATOR, 'en-GB', true);
		$jlang->load('com_admintools', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
		$jlang->load('com_admintools', JPATH_ADMINISTRATOR, null, true);

		$label = JText::_('COM_ADMINTOOLS_LOGIN_TWOFACTOR_LABEL');
		$title = JText::_('COM_ADMINTOOLS_LOGIN_TWOFACTOR_TITLE');

		// The are the "plain vanilla", catch-all settings
		$regex = '#<[\s]*form.*>#iU';
		$input = <<<ENDINPUT

<div class="admintools-security-code">
	<label for="admintools-securitycode" title="$title">$label</label>
	<input name="securitycode" id="admintools-securitycode" type="password" size="6" autocomplete="off" autofocus="autofocus" title="$title" />
	<div class="clear"></div>
</div>
ENDINPUT;


		if (version_compare(JVERSION, '3.0', 'lt'))
		{
			$template = JFactory::getApplication()->getTemplate();

			if ($template == 'hathor')
			{
				// Joomla! 2.5, Hathor
				$regex = '#<[\s]*fieldset[\s]*class[\s]*=[\s]*"loginform">#';
			}
			elseif ($template == 'rt_missioncontrol')
			{
				// Joomla! 2.5 Mission Control by RocketTheme
				$regex = '#<[\s]*input[\s]*name[\s]*=[\s]*"passwd".*>#';
				$input = <<<ENDINPUT

	<input name="securitycode" id="admintools-securitycode" type="password" size="6" autocomplete="off" title="$title" placeholder="$label" class="inputbox" />
ENDINPUT;
			}
		}
		else
		{
			// Joomla! 3.0 template conventions (Bootstrap FTW!)
			$regex = '#<[\s]*fieldset[\s]*class[\s]*=[\s]*"loginform">#';
			$input = <<<ENDINPUT

<div class="control-group">
	<div class="controls">
		<div class="input-prepend input-append">
			<span class="add-on">
				<i class="icon-puzzle" rel="tooltip" data-placement="left" data-original-title="$title"></i>
				<label for="admintools-securitycode" class="element-invisible">$label</label>
			</span><input tabindex="1" name="securitycode" id="admintools-securitycode" type="password" class="input-medium" size="6" autocomplete="off" title="$title" placeholder="$label" autofocus="autofocus" />
		</div>
	</div>
</div>
ENDINPUT;
		}

		$buffer = JResponse::getBody();
		$buffer = preg_replace($regex, '\\0 ' . $input, $buffer);

		JResponse::setBody($buffer);
	}

	private function twoFactorAuthentication_verify()
	{
		// Get the secret key
		$secret = $this->cparams->getValue('twofactorauth_secret', '');

		if (empty($secret))
		{
			return;
		}

		// Include Google Authenticator library
		include_once dirname(__FILE__) . '/gaphp/googleauthenticator.php';

		if (!class_exists('GoogleAuthenticator'))
		{
			return;
		}

		if (!class_exists('FixedBitNotation'))
		{
			return;
		}

		$code = JFactory::getApplication()->input->get('securitycode', '', 'cmd');

		// Check for the panic code
		$panic = $this->cparams->getValue('twofactorauth_panic', '');
		$panic = preg_replace('#[^0-9]#', '', $panic);
		$code = preg_replace('#[^0-9]#', '', $code);

		if ($code == $panic)
		{
			return;
		}

		$googleAuth = new GoogleAuthenticator();

		if (!$googleAuth->checkCode($secret, $code))
		{
			// Uh oh... Unauthorized access!
			if (!$this->logBreaches('securitycode'))
			{
				return;
			}

			$autoban = $this->cparams->getValue('tsrenable', 0);

			if ($autoban)
			{
				$this->autoBan('securitycode');
			}

			$this->redirectAdminToHome();
		}
	}

	/**
	 * Simple Remote Files Inclusion block. If any query string parameter contains a reference to an http[s]:// or ftp[s]://
	 * address it will be scanned. If the remote file looks like a PHP script, we block access.
	 */
	private function RFIShield()
	{
		$hashes = array('get', 'post');
		$regex = '#(http|ftp){1,1}(s){0,1}://.*#i';

		foreach ($hashes as $hash)
		{
			switch ($hash)
			{
				case 'get':
					$source = $_GET;
					break;
				case 'post':
					$source = $_POST;
					break;
			}

			$input = new JInput($source);
			$ref = new ReflectionProperty($input, 'data');
			$ref->setAccessible(true);
			$allVars = $ref->getValue($input);

			if (empty($allVars))
			{
				continue;
			}

			if ($this->match_array_and_scan($regex, $allVars))
			{
				$extraInfo = "Hash      : $hash\n";
				$extraInfo .= "Variables :\n";
				$extraInfo .= print_r($allVars, true);
				$extraInfo .= "\n";
				$this->blockRequest('rfishield', null, $extraInfo);
			}
		}
	}

	private function match_array_and_scan($regex, $array)
	{
		$result = false;

		if (is_array($array))
		{
			foreach ($array as $key => $value)
			{
				if (in_array($key, $this->exceptions))
				{
					continue;
				}

				if (is_array($value))
				{
					$result = $this->match_array_and_scan($regex, $value);
				}
				else
				{
					$result = preg_match($regex, $value);
				}

				if ($result)
				{
					// Can we fetch the file directly?
					$fContents = @file_get_contents($value);

					if (!empty($fContents))
					{
						$result = (strstr($fContents, '<?php') !== false);

						if ($result)
						{
							break;
						}
					}
					else
					{
						$result = false;
					}
				}
			}
		}
		elseif (is_string($array))
		{
			$result = preg_match($regex, $array);

			if ($result)
			{
				// Can we fetch the file directly?
				$fContents = @file_get_contents($array);

				if (!empty($fContents))
				{
					$result = (strstr($fContents, '<?php') !== false);

					if ($result)
					{
						return $result;
					}
				}
				else
				{
					$result = false;
				}
			}
		}

		return $result;
	}

	/**
	 * Runs the Project Honeypot HTTP:BL integration
	 */
	private function ProjectHoneypotHTTPBL()
	{
		// Load parameters
		$httpbl_key = $this->cparams->getValue('bbhttpblkey', '');
		$minthreat = $this->cparams->getValue('httpblthreshold', 25);
		$maxage = $this->cparams->getValue('httpblmaxage', 30);
		$suspicious = $this->cparams->getValue('httpblblocksuspicious', 0);

		// Make sure we have an HTTP:BL  key set
		if (empty($httpbl_key))
		{
			return;
		}

		// Get the IP address
		$reqip = array_key_exists('REMOTE_ADDR', $_SERVER) ? htmlspecialchars($_SERVER['REMOTE_ADDR']) : '0.0.0.0';

		if ($reqip == '0.0.0.0')
		{
			return false;
		}

		if (strpos($reqip, '::') === 0)
		{
			$reqip = substr($reqip, strrpos($reqip, ':') + 1);
		}

		// No point continuing if we can't get an address, right?
		if (empty($reqip))
		{
			return false;
		}

		// IPv6 addresses are not supported by HTTP:BL yet
		if (strpos($reqip, ":"))
		{
			return false;
		}

		$find = implode('.', array_reverse(explode('.', $reqip)));
		$result = gethostbynamel($httpbl_key . ".${find}.dnsbl.httpbl.org.");

		if (!empty($result))
		{
			$ip = explode('.', $result[0]);

			if ($ip[0] != 127)
			{
				return;
			} // Make sure it's a valid response

			if ($ip[3] == 0)
			{
				return;
			} // Do not block search engines

			$block = ($ip[3] & 2) || ($ip[3] & 4); // Block harvesters and comment spammers

			if (!$suspicious && ($ip[3] & 1))
			{
				$block = false;
			} // Do not block "suspicious" (not confirmed) IPs unless asked so

			$block = $block && ($ip[1] <= $maxage);
			$block = $block && ($ip[2] >= $minthreat);

			if ($block)
			{
				$classes = array();

				if ($ip[3] & 1)
				{
					$classes[] = 'Suspicious';
				}

				if ($ip[3] & 2)
				{
					$classes[] = 'Email Harvester';
				}

				if ($ip[3] & 4)
				{
					$classes[] = 'Comment Spammer';
				}

				$class = implode(', ', $classes);
				$extraInfo = <<<ENDINFO
HTTP:BL analysis for blocked spammer's IP address $reqip
	Attacker class		: $class
	Last activity		: $ip[1] days ago
	Threat level		: $ip[2] --> see http://is.gd/mAwMTo for more info

ENDINFO;
				$this->blockRequest('httpbl', '', $extraInfo);
			}
		}
	}

	private function geoBlocking()
	{
		if (!isset($_SERVER['REMOTE_ADDR']))
		{
			return;
		}

		$ip = $_SERVER['REMOTE_ADDR'];

		$continents = $this->cparams->getValue('geoblockcontinents', '');
		$continents = empty($continents) ? array() : explode(',', $continents);
		$countries = $this->cparams->getValue('geoblockcountries', '');
		$countries = empty($countries) ? array() : explode(',', $countries);

		$country = '';
		$continent = '';

		if (class_exists('AkeebaGeoipProvider'))
		{
			$geoip = new AkeebaGeoipProvider();
			$country = $geoip->getCountryCode($ip);
			$continent = $geoip->getContinent($ip);
		}

		if (empty($country))
		{
			$country = '(unknown country)';
		}

		if (empty($continent))
		{
			$continent = '(unknown continent)';
		}

		if (($continent) && !empty($continents))
		{
			if (in_array($continent, $continents))
			{
				$extraInfo = 'Continent : ' . $continent;
				$this->blockRequest('geoblocking', null, $extraInfo);
			}
		}

		if (($country) && !empty($countries))
		{
			if (in_array($country, $countries))
			{
				$extraInfo = 'Country : ' . $country;
				$this->blockRequest('geoblocking', null, $extraInfo);
			}
		}
	}

	/**
	 * Blocks visitors coming from an automatically banned IP. These suckers are repeat
	 * offenders. No courtesy from our part.
	 */
	private function AutoIPFiltering()
	{
		// We need to be able to get our own IP, right?
		if (!function_exists('inet_pton'))
		{
			return;
		}

		// Get our IP address
		$ip = array_key_exists('REMOTE_ADDR', $_SERVER) ? htmlspecialchars($_SERVER['REMOTE_ADDR']) : '0.0.0.0';

		if (empty($ip) || ($ip == '0.0.0.0'))
		{
			return;
		}

		$myIP = @inet_pton($ip);

		if ($myIP === false)
		{
			return;
		}

		$myIP = inet_ntop($myIP);

		// Let's get a list of blocked IP ranges
		$db = JFactory::getDBO();
		$sql = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__admintools_ipautoban'))
			->where($db->qn('ip') . ' = ' . $db->q($myIP));
		$db->setQuery($sql);

		try
		{
			$record = $db->loadObject();
		}
		catch (Exception $e)
		{
			$record = null;
		}

		if (empty($record))
		{
			return;
		}

		// Is this record expired?
		JLoader::import('joomla.utilities.date');
		$jNow = new JDate();
		$jUntil = new JDate($record->until);
		$now = $jNow->toUnix();
		$until = $jUntil->toUnix();

		if ($now > $until)
		{
			// Ban expired. Move the entry and allow the request to proceed.
			$history = clone $record;
			$history->id = null;

			try
			{
				$db->insertObject('#__admintools_ipautobanhistory', $history, 'id');
			}
			catch (Exception $e)
			{
				// Oops...
			}

			$sql = $db->getQuery(true)
				->delete($db->qn('#__admintools_ipautoban'))
				->where($db->qn('ip') . ' = ' . $db->q($myIP));
			$db->setQuery($sql);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				// Oops...
			}

			return;
		}

		// Move old entries - The fastest way is to create a INSERT with a SELECT statement
		$sql = 'INSERT INTO ' . $db->qn('#__admintools_ipautobanhistory') . ' (' . $db->qn('id') . ', ' . $db->qn('ip') . ', ' . $db->qn('reason') . ', ' . $db->qn('until') . ')' .
			' SELECT NULL, ' . $db->qn('ip') . ', ' . $db->qn('reason') . ', ' . $db->qn('until') .
			' FROM ' . $db->qn('#__admintools_ipautoban') .
			' WHERE ' . $db->qn('until') . ' < ' . $db->q($jNow->toSql());
		try
		{
			$r = $db->setQuery($sql)->execute();
		}
		catch (Exception $e)
		{
			// Oops...
		}

		$sql = $db->getQuery(true)
			->delete($db->qn('#__admintools_ipautoban'))
			->where($db->qn('until') . ' < ' . $db->q($jNow->toSql()));
		$db->setQuery($sql);
		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			// Oops...
		}

		@ob_end_clean();
		header("HTTP/1.0 403 Forbidden");

		$spammerMessage = $this->cparams->getValue('spammermessage', '');
		$spammerMessage = str_replace('[IP]', $myIP, $spammerMessage);

		echo $spammerMessage;

		JFactory::getApplication()->close();
	}

	private function autoBan($reason = 'other')
	{
		// We need to be able to get our own IP, right?
		if (!function_exists('inet_pton'))
		{
			return;
		}

		// Get the IP
		$ip = array_key_exists('REMOTE_ADDR', $_SERVER) ? htmlspecialchars($_SERVER['REMOTE_ADDR']) : '0.0.0.0';
		// No point continuing if we can't get an address, right?
		if (empty($ip) || ($ip == '0.0.0.0'))
		{
			return;
		}

		// Check for repeat offenses
		$db = JFactory::getDBO();
		$strikes = $this->cparams->getValue('tsrstrikes', 3);
		$numfreq = $this->cparams->getValue('tsrnumfreq', 1);
		$frequency = $this->cparams->getValue('tsrfrequency', 'hour');
		$mindatestamp = 0;

		switch ($frequency)
		{
			case 'second':
				break;

			case 'minute':
				$numfreq *= 60;
				break;

			case 'hour':
				$numfreq *= 3600;
				break;

			case 'day':
				$numfreq *= 86400;
				break;

			case 'ever':
				$mindatestamp = 946706400; // January 1st, 2000
				break;
		}

		JLoader::import('joomla.utilities.date');
		$jNow = new JDate();

		if ($mindatestamp == 0)
		{
			$mindatestamp = $jNow->toUnix() - $numfreq;
		}

		$jMinDate = new JDate($mindatestamp);
		$minDate = $jMinDate->toSql();

		$sql = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->qn('#__admintools_log'))
			->where($db->qn('logdate') . ' >= ' . $db->q($minDate))
			->where($db->qn('ip') . ' = ' . $db->q($ip));
		$db->setQuery($sql);
		try
		{
			$numOffenses = $db->loadResult();
		}
		catch (Exception $e)
		{
			$numOffenses = 0;
		}

		if ($numOffenses < $strikes)
		{
			return;
		}

		// Block the IP
		$myIP = @inet_pton($ip);

		if ($myIP === false)
		{
			return;
		}

		$myIP = inet_ntop($myIP);

		$until = $jNow->toUnix();
		$numfreq = $this->cparams->getValue('tsrbannum', 1);
		$frequency = $this->cparams->getValue('tsrbanfrequency', 'hour');

		switch ($frequency)
		{
			case 'second':
				$until += $numfreq;
				break;

			case 'minute':
				$numfreq *= 60;
				$until += $numfreq;
				break;

			case 'hour':
				$numfreq *= 3600;
				$until += $numfreq;
				break;

			case 'day':
				$numfreq *= 86400;
				$until += $numfreq;
				break;

			case 'ever':
				$until = 2145938400; // January 1st, 2038 (mind you, UNIX epoch runs out on January 19, 2038!)
				break;
		}

		JLoader::import('joomla.utilities.date');

		$jMinDate = new JDate($until);
		$minDate = $jMinDate->toSql();

		$record = (object)array(
			'ip'     => $myIP,
			'reason' => $reason,
			'until'  => $minDate
		);

		// If I'm here it means that we have to ban the user. Let's see if this is a simple autoban or
		// we have to issue a permaban as a result of several attacks
		if ($this->cparams->getValue('permaban', 0))
		{
			// Ok I have to check the number of autoban
			$query = $db->getQuery(true)
				->select('COUNT(*)')
				->from($db->qn('#__admintools_ipautobanhistory'))
				->where($db->qn('ip') . ' = ' . $db->q($myIP));

			try
			{
				$bans = $db->setQuery($query)->loadResult();
			}
			catch (Exception $e)
			{
				$bans = 0;
			}

			$limit = (int)$this->cparams->getValue('permabannum', 0);

			if ($limit && ($bans > $limit))
			{
				$block = (object)array(
					'id'          => null,
					'ip'          => $myIP,
					'description' => 'IP automatically blocked after being banned automatically ' . $bans . ' times'
				);

				$db->insertObject('#__admintools_ipblock', $block);
			}
		}

		$db->insertObject('#__admintools_ipautoban', $record);

		// Send an optional email
		if ($this->cparams->getValue('emailafteripautoban', ''))
		{
			// Get the site name
			$config = JFactory::getConfig();

			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				$sitename = $config->get('sitename');
			}
			else
			{
				$sitename = $config->getValue('config.sitename');
			}

			$substitutions = array(
				'[SITENAME]' => $sitename,
				'[IP]'       => $myIP,
				'[UNTIL]'    => $minDate
			);

			// Load the component's administrator translation files
			$jlang = JFactory::getLanguage();
			$jlang->load('com_admintools', JPATH_ADMINISTRATOR, 'en-GB', true);
			$jlang->load('com_admintools', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
			$jlang->load('com_admintools', JPATH_ADMINISTRATOR, null, true);

			$subject = JText::_('ATOOLS_LBL_WAF_AUTOIPBLOCKEMAIL_SUBJECT');
			$body = JText::_('ATOOLS_LBL_WAF_AUTOIPBLOCKEMAIL_BODY');

			foreach ($substitutions as $k => $v)
			{
				$subject = str_replace($k, $v, $subject);
				$body = str_replace($k, $v, $body);
			}

			// Send the email
			$mailer = JFactory::getMailer();

			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				$mailfrom = $config->get('mailfrom');
				$fromname = $config->get('fromname');
			}
			else
			{
				$mailfrom = $config->getValue('config.mailfrom');
				$fromname = $config->getValue('config.fromname');
			}

			$mailer->setSender(array($mailfrom, $fromname));
			$mailer->addRecipient($this->cparams->getValue('emailafteripautoban', ''));
			$mailer->setSubject($subject);
			$mailer->setBody($body);
			$mailer->Send();
		}
	}

	/**
	 * Simple Direct Files Inclusion block.
	 */
	private function DFIShield()
	{
		$input = JFactory::getApplication()->input;
		$option = $input->getCmd('option', '');
		$view = $input->getCmd('view', '');
		$layout = $input->getCmd('layout', '');

		// Special case: JCE
		if (($option == 'com_jce') && ($view == 'editor') && ($layout == 'plugin'))
		{
			return;
		}

		$hashes = array('get', 'post');

		foreach ($hashes as $hash)
		{
			switch ($hash)
			{
				case 'get':
					$source = $_GET;
					break;
				case 'post':
					$source = $_POST;
					break;
			}

			$input = new JInput($source);
			$ref = new ReflectionProperty($input, 'data');
			$ref->setAccessible(true);
			$allVars = $ref->getValue($input);

			if (empty($allVars))
			{
				continue;
			}

			if ($this->match_array_dfi($allVars))
			{
				$extraInfo = "Hash      : $hash\n";
				$extraInfo .= "Variables :\n";
				$extraInfo .= print_r($allVars, true);
				$extraInfo .= "\n";
				$this->blockRequest('dfishield', null, $extraInfo);
			}
		}
	}

	private function match_array_dfi($array)
	{
		$result = false;

		if (is_array($array))
		{
			foreach ($array as $key => $value)
			{
				if (in_array($key, $this->exceptions))
				{
					continue;
				}

				// If there's a null byte in the key, break
				if (strstr($key, "\u0000"))
				{
					$result = true;
					break;
				}

				// If there's no value, treat the key as a value
				if (empty($value))
				{
					$value = $key;
				}

				// Scan the value
				if (is_array($value))
				{
					$result = $this->match_array_dfi($value);
				}
				else
				{
					// If there's a null byte, break
					if (strstr($value, "\u0000"))
					{
						$result = true;
						break;
					}

					// If the value starts with a /, ../ or [a-z]{1,2}:, block
					if (preg_match('#^(/|\.\.|[a-z]{1,2}:\\\)#i', $value))
					{
						// Fix 2.0.1: Check that the file exists
						$result = @file_exists($value);

						if (!$result)
						{
							$sillyParts = explode('../', $value);
							$realParts = array();

							foreach ($sillyParts as $p)
							{
								if (!empty($p))
								{
									$realParts[] = $p;
								}
							}

							$path = implode('/', $realParts);
							$result = @file_exists($path);
						}
						break;
					}

					if ($result)
					{
						break;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Scans all uploaded files for PHP tags. This prevents uploading PHP files or crafted
	 * images with raw PHP code in them which may lead to arbitrary code execution under
	 * several common circumstances. It will also block files with null bytes in their
	 * filenames or with double extensions which include PHP in them (e.g. .php.jpg).
	 */
	private function UploadShield()
	{
		// Do we have uploaded files?
		$input = new JInput($_FILES);

		$ref = new ReflectionProperty($input, 'data');
		$ref->setAccessible(true);
		$filesHash = $ref->getValue($input);

		if (empty($filesHash))
		{
			return;
		}

		$extraInfo = '';
		foreach ($filesHash as $key => $descriptor)
		{
			if (is_array($descriptor) && !array_key_exists('tmp_name', $descriptor))
			{
				$descriptors = $descriptor;
			}
			else
			{
				$descriptors[] = $descriptor;
			}

			unset($descriptor);

			foreach ($descriptors as $descriptor)
			{
				$files = array();

				if (is_array($descriptor['tmp_name']))
				{
					foreach ($descriptor['tmp_name'] as $key => $value)
					{
						$files[] = array(
							'name'     => $descriptor['name'][$key],
							'type'     => $descriptor['type'][$key],
							'tmp_name' => $descriptor['tmp_name'][$key],
							'error'    => $descriptor['error'][$key],
							'size'     => $descriptor['size'][$key],
						);
					}
				}
				else
				{
					$files[] = $descriptor;
				}

				foreach ($files as $fileDescriptor)
				{
					$tempNames = $fileDescriptor['tmp_name'];
					$intendedNames = $fileDescriptor['name'];

					if (!is_array($tempNames))
					{
						$tempNames = array($tempNames);
					}

					if (!is_array($intendedNames))
					{
						$intendedNames = array($intendedNames);
					}

					$len = count($tempNames);

					for ($i = 0; $i < $len; $i++)
					{
						$tempName = array_shift($tempNames);
						$intendedName = array_shift($intendedNames);

						$extraInfo = "File descriptor :\n";
						$extraInfo .= print_r($fileDescriptor, true);
						$extraInfo .= "\n";

						// 1. Null byte check
						if (strstr($intendedName, "\u0000"))
						{
							$this->blockRequest('uploadshield', null, $extraInfo);

							return;
						}

						// 2. PHP-in-extension check
						$explodedName = explode('.', $intendedName);
						array_reverse($explodedName);

						// 2a. File extension is .php
						if ((count($explodedName) > 1) && (strtolower($explodedName[0]) == 'php'))
						{
							$this->blockRequest('uploadshield', null, $extraInfo);

							return;
						}

						// 2a. File extension is php.xxx
						if ((count($explodedName) > 2) && (strtolower($explodedName[1]) == 'php'))
						{
							$this->blockRequest('uploadshield', null, $extraInfo);

							return;
						}

						// 2b. File extensions is php.xxx.yyy
						if ((count($explodedName) > 3) && (strtolower($explodedName[2]) == 'php'))
						{
							$this->blockRequest('uploadshield', null, $extraInfo);

							return;
						}

						// 3. Contents scanner
						$fp = @fopen($tempName, 'r');

						if ($fp !== false)
						{
							$data = '';
							$extension = strtolower($explodedName[0]);

							while (!feof($fp))
							{
								$buffer = @fread($fp, 131072);
								$data .= $buffer;

								if (strstr($buffer, '<?php'))
								{
									$this->blockRequest('uploadshield', null, $extraInfo);

									return;
								}

								if (in_array($extension, array('inc', 'phps', 'class', 'php3', 'php4', 'txt', 'dat', 'tpl', 'tmpl')))
								{
									// These are suspicious text files which may have the short tag (<?) in them
									if (strstr($buffer, '<?'))
									{
										$this->blockRequest('uploadshield', null, $extraInfo);

										return;
									}
								}

								$data = substr($data, -4);
							}
							fclose($fp);
						}
					} // end for
				} // end foreach
			}
		}
	}

	/**
	 * Tries to figure out if the given query string looks like an XSS attack. It's not watertight,
	 * but it's better than nothing.
	 *
	 * Based largely on CodeIgniter's XSS cleanup code by EllisLab
	 *
	 * @param string $str The string to filter
	 *
	 * @return bool
	 */
	private function looksLikeXSS($str)
	{
		// 1. Non-displayable character filtering
		static $non_displayables = null;

		if (is_null($non_displayables))
		{
			// All control characters except newline, carriage return, and horizontal tab (dec 09)
			$non_displayables = array(
				'/%0[0-8bcef]/', // url encoded 00-08, 11, 12, 14, 15
				'/%1[0-9a-f]/', // url encoded 16-31
				'/[\x00-\x08]/', // 00-08
				'/\x0b/', '/\x0c/', // 11, 12
				'/[\x0e-\x1f]/' // 14-31
			);
		}

		foreach ($non_displayables as $pattern)
		{
			$result = preg_match($pattern, $str);

			if ($result)
			{
				return true;
			}
		}

		// 2. Partial standard character entities
		$test = preg_replace('#(&\#?[0-9a-z]{2,})([\x00-\x20])*;?#i', "\\1;\\2", $str);

		if ($test != $str)
		{
			return true;
		}

		// 3. Partial UTF16 two byte encoding
		$test = preg_replace('#(&\#x?)([0-9A-F]+);?#i', "\\1\\2;", $str);

		if ($test != $str)
		{
			return true;
		}

		// 4. Conditioning
		// In this step we try to unwrap commonly encoded payloads for the next steps to work
		// 4a. URL decoding, in case an attacker tries to use URL-encoded payloads
		// Note: rawurldecode() is used to avoid decoding plus signs
		$str = rawurldecode($str);

		// 4b. Convert character entities to ASCII, as they are used a lot in XSS attacks
		$str = preg_replace_callback("/[a-z]+=([\'\"]).*?\\1/si", array($this, 'attribute_callback'), $str);
		$str = preg_replace_callback("/<\w+.*?(?=>|<|$)/si", array($this, 'html_entity_decode_callback'), $str);

		// 5. Non-displayable character filtering (second pass, now that we decoded some more entities!)
		foreach ($non_displayables as $pattern)
		{
			$result = preg_match($pattern, $str);

			if ($result)
			{
				return true;
			}
		}

		// 6. Convert tab to spaces. Attackers may use ja	vascript to pass malicious code to us.
		if (strpos($str, "\t") !== false)
		{
			$str = str_replace("\t", ' ', $str);
		}

		// Store the converted string for later comparison
		$converted_string = $str;

		// 7. Filter out unsafe strings from list
		static $never_allowed_str = null;

		if (is_null($never_allowed_str))
		{
			$never_allowed_str = array(
				'document.cookie',
				'document.write',
				'.parentNode',
				'.innerHTML',
				'window.location',
				'-moz-binding',
				'<!--',
				'-->',
				'<![CDATA['
			);
		}

		foreach ($never_allowed_str as $never)
		{
			if (strstr($str, $never) !== false)
			{
				return true;
			}
		}

		// 8. Filter out unsafe strings from list of regular expressions
		static $never_allowed_regex = null;

		if (empty($never_allowed_regex))
		{
			$never_allowed_regex = array(
				"javascript\s*:",
				"expression\s*(\(|&\#40;)",
				"vbscript\s*:",
				"Redirect\s+302",
			);
		}

		foreach ($never_allowed_regex as $pattern)
		{
			if (preg_match('#' . $pattern . '#i', $str))
			{
				return true;
			}
		}

		// 9. PHP filtering
		// Let's make sure that PHP tags (<? or <?php) are not present, while ensuring that
		// XML tags (<?xml) are not touched
		if ($this->cparams->getValue('xssshield_allowphp', 0) != 1)
		{
			$safe = str_replace('<?xml', '--xml', $str);

			if (strstr($safe, '<?'))
			{
				return true;
			}
		}

		// 10. Compact exploded words like j a v a s c r i p t => javascript
		static $words = null;

		if (is_null($words))
		{
			$words = array('javascript', 'expression', 'vbscript', 'script', 'applet', 'alert', 'document', 'write', 'cookie', 'window');
		}

		foreach ($words as $word)
		{
			$temp = '';

			for ($i = 0, $wordlen = strlen($word); $i < $wordlen; $i++)
			{
				$temp .= substr($word, $i, 1) . "\s*";
			}

			// We only want to do this when it is followed by a non-word character
			$str = preg_replace_callback('#(' . substr($temp, 0, -3) . ')(\W)#is', array($this, 'compact_exploded_words_callback'), $str);
		}

		// 11. Check for disallowed Javascript in links or img tags
		$original = $str;

		if (preg_match("/<a/i", $str))
		{
			$str = preg_replace_callback("#<a\s+([^>]*?)(>|$)#si", array($this, 'js_link_removal'), $str);
		}

		if ($str != $original)
		{
			return true;
		}

		if (preg_match("/<img/i", $str))
		{
			$str = preg_replace_callback("#<img\s+([^>]*?)(\s?/?>|$)#si", array($this, 'js_img_removal'), $str);
		}

		if ($str != $original)
		{
			return true;
		}

		if (preg_match("/script/i", $str) OR preg_match("/xss/i", $str))
		{
			$str = preg_replace("#<(/*)(script|xss)(.*?)\>#si", '[removed]', $str);
		}

		if ($str != $original)
		{
			return true;
		}

		// 11. Detect Javascript event handlers
		$event_handlers = array('[^a-z_\-]on\w*', 'xmlns');
		$str = preg_replace("#<([^><]+?)(" . implode('|', $event_handlers) . ")(\s*=\s*[^><]*)([><]*)#i", "<\\1\\4", $str);

		if ($str != $original)
		{
			return true;
		}

		// 12. Detect naughty PHP and Javascript code commonly used in exploits
		$result = preg_match('#(alert|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', $str);

		if ($result)
		{
			return true;
		}

		// -- At this point, the string has passed all XSS filters. We hope it contains nothing malicious
		// -- so we will report it as non-XSS.
		return false;
	}

	private function attribute_callback($match)
	{
		return str_replace(array('>', '<', '\\'), array('&gt;', '&lt;', '\\\\'), $match[0]);
	}

	private function html_entity_decode_callback($match)
	{
		$str = $match[0];

		if (stristr($str, '&') === false)
		{
			return $str;
		}

		$str = html_entity_decode($str, ENT_COMPAT, 'UTF-8');
		$str = preg_replace('~&#x(0*[0-9a-f]{2,5})~ei', 'chr(hexdec("\\1"))', $str);

		return preg_replace('~&#([0-9]{2,4})~e', 'chr(\\1)', $str);
	}

	private function compact_exploded_words_callback($matches)
	{
		return preg_replace('/\s+/s', '', $matches[1]) . $matches[2];
	}

	private function js_link_removal($match)
	{
		$attributes = $this->filter_attributes(str_replace(array('<', '>'), '', $match[1]));

		return str_replace($match[1], preg_replace("#href=.*?(alert\(|alert&\#40;|javascript\:|charset\=|window\.|document\.|\.cookie|<script|<xss|base64\s*,)#si", "", $attributes), $match[0]);
	}

	function js_img_removal($match)
	{
		$attributes = $this->filter_attributes(str_replace(array('<', '>'), '', $match[1]));

		return str_replace($match[1], preg_replace("#src=.*?(alert\(|alert&\#40;|javascript\:|charset\=|window\.|document\.|\.cookie|<script|<xss|base64\s*,)#si", "", $attributes), $match[0]);
	}

	function filter_attributes($str)
	{
		$out = '';

		$matches = array();

		if (preg_match_all('#\s*[a-z\-]+\s*=\s*(\042|\047)([^\\1]*?)\\1#is', $str, $matches))
		{
			foreach ($matches[0] as $match)
			{
				$out .= preg_replace("#/\*.*?\*/#s", '', $match);
			}
		}

		return $out;
	}

	private function match_array_xss($array)
	{
		// Safe keys, i.e. keys which may contain stuff which looks like an XSS attack
		static $safe_keys = array();

		if (!$safe_keys)
		{
			$parts = explode(',', $this->cparams->getValue('xssshield_safe_keys', ''));

			foreach ($parts as $part)
			{
				// Sanity check to avoid wrong input
				$temp = trim($part);

				if ($temp)
				{
					$safe_keys[] = $temp;
				}
			}
		}

		$result = false;

		if (is_array($array))
		{
			foreach ($array as $key => $value)
			{
				if (in_array($key, $safe_keys))
				{
					continue;
				}

				if (!in_array($key, $this->exceptions))
				{
					continue;
				}

				// If there's no value, treat the key as a value
				if (empty($value))
				{
					$value = $key;
				}

				// Make sure the key is not an XSS attack
				// if($this->looksLikeXSS($key)) return true;
				// Scan the value
				if (is_array($value))
				{
					$result = $this->match_array_xss($value);
				}
				else
				{
					$result = $this->looksLikeXSS($value);

					if ($result)
					{
						break;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Simple XSS attack block.
	 */
	private function XSSShield()
	{
		$hashes = array('get', 'post');

		foreach ($hashes as $hash)
		{
			switch ($hash)
			{
				case 'get':
					$source = $_GET;
					break;
				case 'post':
					$source = $_POST;
					break;
			}

			$input = new JInput($source);
			$ref = new ReflectionProperty($input, 'data');
			$ref->setAccessible(true);
			$allVars = $ref->getValue($input);

			if (empty($allVars))
			{
				continue;
			}

			if ($this->match_array_xss($allVars))
			{
				$extraInfo = "Hash      : $hash\n";
				$extraInfo .= "Variables :\n";
				$extraInfo .= print_r($allVars, true);
				$extraInfo .= "\n";
				$this->blockRequest('xssshield', null, $extraInfo);
			}
		}
	}

	/**
	 * Purges old log entries
	 */
	private function purgeLog()
	{
		$minutes = (int)$this->params->get('purgelog_freq', 0);

		if ($minutes <= 0)
		{
			return;
		}

		$lastJob = $this->getTimestamp('purge_log');
		$nextJob = $lastJob + $minutes * 60;

		JLoader::import('joomla.utilities.date');
		$now = new JDate();

		if ($now->toUnix() >= $nextJob)
		{
			$this->setTimestamp('purge_log');

			$maxage = (int)$this->params->get('purgelog_age', 0);
			$maxage = 24 * 3600 * $maxage;

			if ($maxage > 0)
			{
				$now = time();
				$oldest = $now - $maxage;
				$jOldest = new JDate($oldest);
				$mOldest = $jOldest->toSql();

				$db = JFactory::getDBO();
				$sql = $db->getQuery(true)
					->delete($db->qn('#__admintools_log'))
					->where($db->qn('logdate') . ' < ' . $db->q($mOldest));
				$db->setQuery($sql);
				$db->execute();
			}
		}
	}

	function &_getUser($user, $options = array())
	{
		JLoader::import('joomla.user.helper');
		$instance = new JUser();

		if ($id = intval(JUserHelper::getUserId($user['username'])))
		{
			$instance->load($id);

			return $instance;
		}

		JLoader::import('joomla.application.component.helper');
		$config = JComponentHelper::getParams('com_users');
		$defaultUserGroup = $config->get('new_usertype', 2);

		$acl = JFactory::getACL();

		$instance->set('id', 0);
		$instance->set('name', $user['fullname']);
		$instance->set('username', $user['username']);
		$instance->set('password_clear', $user['password_clear']);
		$instance->set('email', $user['email']); // Result should contain an email (check)
		$instance->set('usertype', 'deprecated');
		$instance->set('groups', array($defaultUserGroup));

		return $instance;
	}

	private function loadExceptions()
	{
		// Now, proceed
		$input = JFactory::getApplication()->input;
		$option = $input->getCmd('option', '');
		$view = $input->getCmd('view', '');

		$db = JFactory::getDBO();

		$sql = $db->getQuery(true)
			->select($db->qn('query'))
			->from($db->qn('#__admintools_wafexceptions'));

		if (empty($option))
		{
			$sql->where(
				'(' . $db->qn('option') . ' IS NULL OR ' .
				$db->qn('option') . ' = ' . $db->q('')
				. ')'
			);
		}
		else
		{
			$sql->where(
				'(' . $db->qn('option') . ' IS NULL OR ' .
				$db->qn('option') . ' = ' . $db->q('') . ' OR ' .
				$db->qn('option') . ' = ' . $db->q($option)
				. ')'
			);
		}

		if (empty($view))
		{
			$sql->where(
				'(' . $db->qn('view') . ' IS NULL OR ' .
				$db->qn('view') . ' = ' . $db->q('')
				. ')'
			);
		}
		else
		{
			$sql->where(
				'(' . $db->qn('view') . ' IS NULL OR ' .
				$db->qn('view') . ' = ' . $db->q('') . ' OR ' .
				$db->qn('view') . ' = ' . $db->q($view)
				. ')'
			);
		}

		$sql->group($db->qn('query'))
			->order($db->qn('query') . ' ASC');

		$db->setQuery($sql);

		try
		{
			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				$this->exceptions = $db->loadColumn();
			}
			else
			{
				$this->exceptions = $db->loadResultArray();
			}
		}
		catch (Exception $e)
		{
		}
	}

	private function removeInactiveUsers()
	{
		// If the days are not at least 1, bail out
		$filtertype = (int)$this->params->get('deleteinactive', 1);
		$days = (int)$this->params->get('deleteinactive_days', 0);

		if ($days <= 0)
		{
			return;
		}

		// Get up to 5 ids of users to remove
		$db = JFactory::getDbo();

		$sql = $db->getQuery(true)
			->select($db->qn('id'))
			->from($db->qn('#__users'))
			->where($db->qn('lastvisitDate') . ' = ' . $db->q($db->getNullDate()))
			->where($db->qn('registerDate') . ' <= ' . "DATE_SUB(NOW(), INTERVAL $days DAY)");

		switch ($filtertype)
		{
			case 1:
				// Only users not yet activated
				$sql->where($db->qn('activation') . ' != ' . $db->quote(''));
				break;

			case 2:
				// Only users already activated
				$sql->where($db->qn('activation') . ' = ' . $db->quote(''));
				break;

			case 3:
				// All users who haven't logged in
				break;
		}


		$db->setQuery($sql, 0, 5);

		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$ids = $db->loadColumn();
		}
		else
		{
			$ids = $db->loadResultArray();
		}

		// Remove those inactive users
		if (!empty($ids))
		{
			foreach ($ids as $id)
			{
				$userToKill = JFactory::getUser($id);
				$userToKill->delete();
			}
		}
	}

	private function loadTimestamps()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__admintools_storage'))
			->where($db->quoteName('key') . ' LIKE ' . $db->quote('timestamp_%'));
		$db->setQuery($query);
		$temp = $db->loadAssocList();

		$this->timestamps = array();

		if (!empty($temp))
		{
			foreach ($temp as $item)
			{
				$this->timestamps[$item['key']] = $item['value'];
			}
		}
	}

	private function removeOldLogEntries()
	{
		// Delete up to 100 old entries
		$maxEntries = $this->params->get('maxlogentries', 0);
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->qn('id'))
			->from($db->qn('#__admintools_log'))
			->order($db->qn('id') . ' DESC');
		$db->setQuery($query, $maxEntries, 100);
		$ids = $db->loadColumn(0);

		if (!count($ids))
		{
			return;
		}

		$temp = array();

		foreach ($ids as $id)
		{
			$temp = $db->q($id);
		}

		$ids = implode(',', $ids);

		$query = $db->getQuery(true)
			->delete($db->qn('#__admintools_log'))
			->where($db->qn('id') . ' IN(' . $ids . ')');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $exc)
		{
			// Do nothing on DB exception
		}
	}

	private function getEmailTemplate($reason)
	{
		// Let's get the subject and the body from email templates
		$jlang = JFactory::getLanguage();
		$db = JFactory::getDbo();
		$languages = array($db->q('*'), $db->q('en-GB'), $db->q($jlang->getDefault()));
		$stack = array();

		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__admintools_waftemplates'))
			->where($db->qn('reason') . ' IN(' . $db->q($reason) . ', ' . $db->q('all') . ')')
			->where($db->qn('language') . ' IN(' . implode(',', $languages) . ')')
			->where($db->qn('enabled') . ' = ' . $db->q('1'));

		try
		{
			$templates = $db->setQuery($query)->loadObjectList();
		}
		catch (Exception $e)
		{
			return false;
		}

		foreach ($templates as $template)
		{
			$score = 0;

			if ($template->reason == $reason)
			{
				$score += 10;
			}

			if ($template->language == $jlang->getDefault())
			{
				$score += 10;
			}
			elseif ($template->language == '*')
			{
				$score += 5;
			}
			elseif ($template->language == 'en-GB')
			{
				$score += 1;
			}

			$stack[$score] = $template;
		}

		ksort($stack);
		$best = array_pop($stack);

		if (!$best)
		{
			return array();
		}

		if ($this->cparams->getValue('email_throttle', 1))
		{
			// Ok I found out the best template, HOWEVER, should I really send out an email? Let's do some checks vs frequency limits
			$emails = $best->email_num ? $best->email_num : 5;
			$numfreq = $best->email_numfreq ? $best->email_numfreq : 1;
			$frequency = $best->email_freq ? $best->email_freq : 'hour';
			$mindatestamp = 0;

			switch ($frequency)
			{
				case 'second':
					break;

				case 'minute':
					$numfreq *= 60;
					break;

				case 'hour':
					$numfreq *= 3600;
					break;

				case 'day':
					$numfreq *= 86400;
					break;

				case 'ever':
					$mindatestamp = 946706400; // January 1st, 2000
					break;
			}

			JLoader::import('joomla.utilities.date');
			$jNow = new JDate();

			if ($mindatestamp == 0)
			{
				$mindatestamp = $jNow->toUnix() - $numfreq;
			}

			$jMinDate = new JDate($mindatestamp);
			$minDate = $jMinDate->toSql();

			$sql = $db->getQuery(true)
				->select('COUNT(*)')
				->from($db->qn('#__admintools_log'))
				->where($db->qn('logdate') . ' >= ' . $db->q($minDate))
				->where($db->qn('reason') . ' = ' . $db->q($reason));
			$db->setQuery($sql);
			try
			{
				$numOffenses = $db->loadResult();
			}
			catch (Exception $e)
			{
				$numOffenses = 0;
			}

			if ($numOffenses > $emails)
			{
				return array();
			}
		}

		return array(
			$best->subject,
			$best->template
		);
	}

    private function deactivateUser($username)
    {
        $userParams = JComponentHelper::getParams('com_users');

        // User registration disabled or no user activation - Let's stop here
        if(!$userParams->get('allowUserRegistration') || ($userParams->get('useractivation') == 0))
        {
            return;
        }

        $ip = $this->detectIP();

        // If I can't detect the IP there's not point in continuing
        if(!$ip)
        {
            return;
        }

        $limit     = $this->cparams->getValue('deactivateusers_num', 3);
        $numfreq   = $this->cparams->getValue('deactivateusers_numfreq', 1);
        $frequency = $this->cparams->getValue('deactivateusers_frequency', 'hour');

        // The user didn't set any limit nor frequency value, let's stop here
        if(!$limit || !$numfreq)
        {
            return;
        }

        $userid = JUserHelper::getUserId($username);

        // The user doesn't exists, let's stop here
        if(!$userid)
        {
            return;
        }

        $user = JFactory::getUser($userid);

        // Username doesn't match, the user is blocked or is not active? Let's stop here
        if($user->username != $username || $user->block || !(empty($user->activation)))
        {
            return;
        }

        // If I'm here, it means that this is a valid user, let's see if I have to deactivate him
        $where = array(
            'ip'     => $ip,
            'reason' => 'loginfailure'
        );

        $deactivate = $this->checkLogFrequency($limit, $numfreq, $frequency, $where);

        if(!$deactivate)
        {
            return;
        }

        JPluginHelper::importPlugin('user');
        $db     = JFactory::getDbo();

        $data['activation'] = JApplication::getHash(JUserHelper::genRandomPassword());
        $data['block'] = 1;
        $data['lastvisitDate'] = $db->getNullDate();

        // If an admin needs to activate the user, I have to set the activate flag
        if($userParams->get('useractivation') == 2)
        {
            $user->setParam('activate', 1);
        }

        if(!$user->bind($data))
        {
            return;
        }

        if(!$user->save())
        {
            return;
        }

        // Ok, now it's time to send the activation email again
        $template = $this->getEmailTemplate('user-reactivate');

        // Well, this should never happen...
        if(!$template)
        {
            return;
        }

        $subject = $template[0];
        $body    = $template[1];

        $config = JFactory::getConfig();
        $mailer = JFactory::getMailer();

        $sitename = $config->get('sitename');
        $mailfrom = $config->get('mailfrom');
        $fromname = $config->get('fromname');

        $uri      = JUri::getInstance();
        $base     = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
        $activate = $base . JRoute::_('index.php?option=com_users&task=registration.activate&token=' . $data['activation'], false);

        // Send e-mail to the user
        if($userParams->get('useractivation') == 1)
        {
            $mailer->addRecipient($user->email);
        }
        // Send e-mail to Super Users
        elseif($userParams->get('useractivation') == 2)
        {
            // get all admin users
            $query = $db->getQuery(true)
                        ->select($db->qn(array('name', 'email', 'sendEmail', 'id')))
                        ->from($db->qn('#__users'))
                        ->where($db->qn('sendEmail') . ' = ' . 1);

            $rows = $db->setQuery($query)->loadObjectList();

            // Send mail to all users with users creating permissions and receiving system emails
            foreach ($rows as $row)
            {
                $usercreator = JFactory::getUser($row->id);

                if ($usercreator->authorise('core.create', 'com_users'))
                {
                    $mailer->addRecipient($usercreator->email);
                }
            }
        }
        else
        {
            // Future-proof check
            return;
        }

        $tokens = array(
            '[SITENAME]'  => $sitename,
            '[DATE]'      => gmdate('Y-m-d H:i:s') . " GMT",
            '[USER]'      => $username,
            '[IP]'        => $ip,
            '[ACTIVATE]'  => '<a href="' . $activate . '">'.$activate.'</a>',
        );

        $subject = str_replace(array_keys($tokens), array_values($tokens), $subject);
        $body    = str_replace(array_keys($tokens), array_values($tokens), $body);

        $mailer->isHtml(true);
        $mailer->setSender(array($mailfrom, $fromname));
        $mailer->setSubject($subject);
        $mailer->setBody($body);
        $mailer->Send();
    }

    private function checkLogFrequency($limit, $numfreq, $frequency, array $extraWhere)
    {
        JLoader::import('joomla.utilities.date');
        $db = JFactory::getDbo();

        $mindatestamp = 0;

        switch ($frequency)
        {
            case 'second':
                break;

            case 'minute':
                $numfreq *= 60;
                break;

            case 'hour':
                $numfreq *= 3600;
                break;

            case 'day':
                $numfreq *= 86400;
                break;

            case 'ever':
                $mindatestamp = 946706400; // January 1st, 2000
                break;
        }

        $jNow = new JDate();

        if ($mindatestamp == 0)
        {
            $mindatestamp = $jNow->toUnix() - $numfreq;
        }

        $jMinDate = new JDate($mindatestamp);
        $minDate = $jMinDate->toSql();

        $sql = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->qn('#__admintools_log'))
            ->where($db->qn('logdate') . ' >= ' . $db->q($minDate));

        foreach($extraWhere as $column => $value)
        {
            $sql->where($db->qn($column).' = '.$db->q($value));
        }

        $db->setQuery($sql);

        try
        {
            $numOffenses = $db->loadResult();
        }
        catch (Exception $e)
        {
            $numOffenses = 0;
        }

        if ($numOffenses < $limit)
        {
            return false;
        }

        return true;
    }

    /**
     * Detects the IP of the current visitor
     *
     * @return bool|string  Visitor IP or false if we can't detect it
     */
    private function detectIP()
    {
        // We need to be able to get our own IP, right?
        if (!function_exists('inet_pton'))
        {
            return false;
        }

        $ip = array_key_exists('REMOTE_ADDR', $_SERVER) ? htmlspecialchars($_SERVER['REMOTE_ADDR']) : '0.0.0.0';

        if (empty($ip) || ($ip == '0.0.0.0'))
        {
            return false;
        }

        $myIP = @inet_pton($ip);

        if ($myIP === false)
        {
            return false;
        }

        return inet_ntop($myIP);
    }

    /**
     * Checks if the request is inside the Away Schedule range
     *
     * @return bool
     */
    private function checkAwaySchedule()
    {
        // Disabled or missing configuration values
        if(!$this->cparams->getValue('awayschedule_from') || !$this->cparams->getValue('awayschedule_to'))
        {
            return true;
        }

        $timezone = JFactory::getConfig()->get('offset', 'UTC');
        $now  = new JDate('now', $timezone);
        $from = new JDate($this->cparams->getValue('awayschedule_from'), $timezone);
        $to   = new JDate($this->cparams->getValue('awayschedule_to'), $timezone);

        // Login attempt, while we set the away schedule, let's ban the user
        if($now > $from && $now < $to)
        {
            return false;
        }

        return true;
    }

	private function customAdminFolder()
	{
        $config = JFactory::getConfig();
		$folder = $this->cparams->getValue('adminlogindir');

        // Custom admin folder is disabled
		if(!$folder || !$config->get('sef') || !$config->get('sef_rewrite'))
		{
			return;
		}

        $ip = $this->detectIP();

        // I couldn't detect the ip, let's stop here
        if($ip === false)
        {
            return;
        }

        // Some user agents don't set a UA string at all
        if (!array_key_exists('HTTP_USER_AGENT', $_SERVER))
        {
            return;
        }

		if (version_compare(JVERSION, '3.2.0', 'ge'))
		{
			$ua = JFactory::getApplication()->client;
			$uaString = $ua->userAgent;
			$browserVersion = $ua->browserVersion;
			$uaShort = str_replace($browserVersion, 'abcd', $uaString);
		}
		else
		{
			JLoader::import('joomla.environment.browser');
			$browser = JBrowser::getInstance();
			$uaString = $browser->getAgentString();
			$browserVersion = $browser->getVersion();
			$uaShort = str_replace($browserVersion, 'abcd', $uaString);
		}

		$uri = JURI::getInstance();
        $db  = JFactory::getDbo();

        // We're not trying to access to the custom folder
        if (str_replace($uri->root(), '', trim($uri->current(), '/')) != $folder)
        {
            return;
        }

		JLoader::import('joomla.user.helper');

		if (version_compare(JVERSION, '3.2.1', 'ge'))
		{
			$hash = JUserHelper::hashPassword($ip . $uaShort);
		}
		else
		{
			$hash = md5($ip . $uaShort);
		}

        $data = (object) array(
            'series'      => JUserHelper::genRandomPassword(64),
            'client_hash' => $hash,
            'valid_to'    => date('Y-m-d H:i:s', time() + 180)
        );

        $db->insertObject('#__admintools_cookies', $data);

		$config = JFactory::getConfig();
		$cookie_domain = $config->get('cookie_domain', '');
		$cookie_path = $config->get('cookie_path', '/');
		$isSecure = $config->get('force_ssl', 0) ? true : false;
        setcookie('admintools', $data->series, time() + 180, $cookie_path, $cookie_domain, $isSecure, true);

        $uri->setPath(str_replace($folder, 'administrator', $uri->getPath()));
        JFactory::getApplication()->redirect($uri->toString());
	}

    private function checkCustomAdminFolder()
    {
        $folder = $this->cparams->getValue('adminlogindir');

        // Custom admin folder is disabled
        if(!$folder || !JFactory::getConfig()->get('sef') || !JFactory::getConfig()->get('sef_rewrite'))
        {
            return;
        }

		// Initialise
		$isValid = true;
		$seriesFound = false;
		$db = JFactory::getDbo();

		// Get the series number from the cookie
		$series = JFactory::getApplication()->input->cookie->get('admintools', null);

		// Do we have a series?
		$isValid = !empty($series);

		// Does the series exist in the db? If so, load it
		if ($isValid)
		{
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__admintools_cookies'))
				->where($db->qn('series') . ' = ' . $db->q($series));
			$db->setQuery($query);
			$storedData = $db->loadObject();

			if (!is_object($storedData))
			{
				$isValid = false;
			}
			else
			{
				$seriesFound = true;
			}
		}

		// Is the series still valid or did someone manipulate the cookie expiration?
		if ($isValid)
		{
			$jValid = strtotime($storedData->valid_to);

			if ($jValid < time())
			{
				$isValid = false;
			}
		}

		// Does the UA match the stored series?
		if ($isValid)
		{
			$ip = $this->detectIP();

			if (version_compare(JVERSION, '3.2.0', 'ge'))
			{
				$ua = JFactory::getApplication()->client;
				$uaString = $ua->userAgent;
				$browserVersion = $ua->browserVersion;
				$uaShort = str_replace($browserVersion, 'abcd', $uaString);
			}
			else
			{
				JLoader::import('joomla.environment.browser');
				$browser = JBrowser::getInstance();
				$uaString = $browser->getAgentString();
				$browserVersion = $browser->getVersion();
				$uaShort = str_replace($browserVersion, 'abcd', $uaString);
			}

			$notSoSecret = $ip . $uaShort;

			JLoader::import('joomla.user.helper');

			if (version_compare(JVERSION, '3.2.1', 'ge'))
			{
				$isValid = JUserHelper::verifyPassword($notSoSecret, $storedData->client_hash);
			}
			else
			{
				$hash = md5($ip . $uaShort);
				$isValid = $hash == $storedData->client_hash;
			}
		}


		// Last check: session state variable
		if (JFactory::getSession()->get('adminlogindir', 0, 'com_admintools'))
		{
			$isValid = true;
		}

		// Delete the series cookie if found
		if ($seriesFound)
		{
			$query = $db->getQuery(true)
				->delete($db->qn('#__admintools_cookies'))
				->where($db->qn('series') . ' = ' . $db->q($series));
			$db->setQuery($query);
			$db->execute();
		}

		// Log an exception and redirect to homepage if we can't validate the user's cookie / session parameter
		if (!$isValid)
		{
			if(!$this->logBreaches('admindir'))
			{
				return;
			}

			$autoban = $this->cparams->getValue('tsrenable', 0);

			if ($autoban)
			{
				$this->autoBan('admindir');
			}

			$this->redirectAdminToHome();
		}
		// Otherwise set the session parameter
		elseif ($seriesFound)
		{
			JFactory::getSession()->set('adminlogindir', 1, 'com_admintools');
		}
    }
}