<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

class AtsystemFeatureEmailonlogin extends AtsystemFeatureAbstract
{
	protected $loadOrder = 220;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		if (!F0FPlatform::getInstance()->isBackend())
		{
			return false;
		}

		if ($this->isAdminAccessAttempt())
		{
			return false;
		}

		$user = JFactory::getUser();

		if ($user->guest)
		{
			return false;
		}

		$email = $this->cparams->getValue('emailonadminlogin', '');

		return !empty($email);
	}

	/**
	 * Sends an email upon accessing an administrator page other than the login screen
	 */
	public function onAfterInitialise()
	{
		$user = JFactory::getUser();

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
		$ip = AtsystemUtilFilter::getIp();

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

		$mailfrom = $config->get('mailfrom');
		$fromname = $config->get('fromname');

		$recipients = explode(',', $this->cparams->getValue('emailonadminlogin', ''));
		$recipients = array_map('trim', $recipients);

		foreach ($recipients as $recipient)
		{
			$mailer->setSender(array($mailfrom, $fromname));
			$mailer->addRecipient($recipient);
			$mailer->setSubject($subject);
			$mailer->setBody($body);
			$mailer->Send();
		}

		// Set the flag to prevent sending more emails
		$session->set('waf.loggedin', 1, 'plg_admintools');
	}
} 