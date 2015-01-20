<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

class AtsystemFeatureTwofactorauth extends AtsystemFeatureAbstract
{
	protected $loadOrder = 80;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		if (!$this->isAdminAccessAttempt())
		{
			return false;
		}

		// Two factor authentication must not be activated in Joomla! 3.2 or later
		if (version_compare(JVERSION, '3.1.9999', 'ge'))
		{
			return false;
		}

		return ($this->cparams->getValue('twofactorauth', 0) == 1);
	}

	/**
	 * Checks if the secret word is set in the URL query, or redirects the user
	 * back to the home page.
	 */
	public function onAfterInitialise()
	{
		if ($this->isAdminAccessAttempt(true))
		{
			$this->verifyTfa();

			return;
		}

		$this->showTfaFields();
	}

	/**
	 * Processes the login form, adding the 2FA field
	 */
	public function onAfterRender()
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

		if (method_exists($this->app, 'getBody'))
		{
			$buffer = $this->app->getBody();
		}
		else
		{
			$buffer = JResponse::getBody();
		}

		$buffer = preg_replace($regex, '\\0 ' . $input, $buffer);

		if (method_exists($this->app, 'setBody'))
		{
			$this->app->setBody($buffer);
		}
		else
		{
			JResponse::setBody($buffer);
		}
	}

	protected function showTfaFields()
	{
		$version = explode('.', JVERSION);
		$version = $version[0] . $version[1];
		$template = $this->app->getTemplate();

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

	protected function verifyTfa()
	{
		// Get the secret key
		$secret = $this->cparams->getValue('twofactorauth_secret', '');

		if (empty($secret))
		{
			return;
		}

		$code = $this->input->get('securitycode', '', 'cmd');

		// Check for the panic code
		$panic = $this->cparams->getValue('twofactorauth_panic', '');
		$panic = preg_replace('#[^0-9]#', '', $panic);
		$code = preg_replace('#[^0-9]#', '', $code);

		if ($code == $panic)
		{
			return;
		}

		$googleAuth = new F0FEncryptTotp();

		if (!$googleAuth->checkCode($secret, $code))
		{
			// Uh oh... Unauthorized access!
			if (!$this->exceptionsHandler->logAndAutoban('securitycode'))
			{
				return;
			}

			$this->redirectAdminToHome();
		}
	}
}