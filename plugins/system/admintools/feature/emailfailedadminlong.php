<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

class AtsystemFeatureEmailfailedadminlong extends AtsystemFeatureAbstract
{
	protected $loadOrder = 810;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		if ($this->cparams->getValue('trackfailedlogins', 0) == 1)
		{
			// When track failed logins is enabled we don't send emails through this feature
			return false;
		}

		if (!F0FPlatform::getInstance()->isBackend())
		{
			return false;
		}

		$emailonfailedadmin = $this->cparams->getValue('emailonfailedadminlogin', '');

		if (empty($emailonfailedadmin))
		{
			return false;
		}

		return true;
	}

	/**
	 * Sends an email upon a failed administrator login
	 *
	 * @param JAuthenticationResponse $response
	 */
	public function onUserLoginFailure($response)
	{
		// Make sure we don't fire unless someone is still in the login page
		$user = JFactory::getUser();

		if (!$user->guest)
		{
			return;
		}

		$option = $this->input->getCmd('option');
		$task = $this->input->getCmd('task');

		if (($option != 'com_login') && ($task != 'login'))
		{
			return;
		}

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

		$sitename = $config->get('sitename');

		// Get the IP address
		$ip = AtsystemUtilFilter::getIp();

		if ((strpos($ip, '::') === 0) && (strstr($ip, '.') !== false))
		{
			$ip = substr($ip, strrpos($ip, ':') + 1);
		}

		// Send the email
		$mailer = JFactory::getMailer();

		$mailfrom = $config->get('mailfrom');
		$fromname = $config->get('fromname');

		$recipients = explode(',', $this->cparams->getValue('emailonfailedadminlogin', ''));
		$recipients = array_map('trim', $recipients);

		foreach ($recipients as $recipient)
		{
			$mailer->setSender(array($mailfrom, $fromname));
			$mailer->addRecipient($recipient);
			$mailer->setSubject(JText::sprintf('ATOOLS_LBL_WAF_EMAILADMINFAILEDLOGIN_SUBJECT', $username, $sitename));
			$mailer->setBody(JText::sprintf('ATOOLS_LBL_WAF_EMAILADMINFAILEDLOGIN_BODY', $username, $sitename, $ip, $sitename));
			$mailer->Send();
		}
	}
}