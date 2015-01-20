<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

JLoader::import('joomla.application.plugin');

if (!function_exists('fnmatch'))
{
	function fnmatch($pattern, $string)
	{
		return @preg_match(
			'/^' . strtr(addcslashes($pattern, '/\\.+^$(){}=!<>|'),
				array('*' => '.*', '?' => '.?')) . '$/i', $string
		);
	}
}

class plgSystemAdmintoolsCore extends JPlugin
{
	/* When set to true, the cache files will be regenerated every time */
	const DEBUG = false;

	/** @var AdminToolsModelStorage The component parameters store */
	private $cparams = null;

	/** @var string The absolute base URL of the site */
	private $baseURL = null;

	static public $myself = null;

	public static function &fetchMyself()
	{
		return self::$myself;
	}

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

		self::$myself = $this;
	}

	public function onBeforeRender()
	{
		$app = JFactory::getApplication();
		$app->registerEvent('onAfterRender', 'AdminToolsLateBoundAfterRender');
	}

	public function onAfterRenderLatebound()
	{
		$app = JFactory::getApplication();
		if (in_array($app->getName(), array('administrator', 'admin')))
		{
			return;
		}

		// Link Migration - rewrite links pointing to the old domain name of the site
		if ($this->cparams->getValue('linkmigration', 0) == 1)
		{
			$this->linkMigration();
		}

		// HTTPSizer - convert all links to HTTPS
		if ($this->cparams->getValue('httpsizer', 0) == 1)
		{
			$this->httpsizer();
		}
	}

	/**
	 * Provides link migration services. All absolute links pointing to any of the old domain names
	 * are being rewritten to point to the current domain name. This runs a full page replacement
	 * using Regular Expressions, so even menus with absolute URLs will be migrated!
	 */
	private function linkMigration()
	{
		$buffer = JResponse::getBody();

		$pattern = '/(href|src)=\"([^"]*)\"/i';
		$number_of_matches = preg_match_all($pattern, $buffer, $matches, PREG_OFFSET_CAPTURE);

		if ($number_of_matches > 0)
		{
			$substitutions = $matches[2];
			$last_position = 0;
			$temp = '';

			// Loop all URLs
			foreach ($substitutions as &$entry)
			{
				// Copy unchanged part, if it exists
				if ($entry[1] > 0)
				{
					$temp .= substr($buffer, $last_position, $entry[1] - $last_position);
				}
				// Add the new URL
				$temp .= $this->replaceDomain($entry[0]);
				// Calculate next starting offset
				$last_position = $entry[1] + strlen($entry[0]);
			}
			// Do we have any remaining part of the string we have to copy?
			if ($last_position < strlen($buffer))
			{
				$temp .= substr($buffer, $last_position);
			}
			// Replace content with the processed one
			unset($buffer);
			JResponse::setBody($temp);
			unset($temp);
		}
	}

	/**
	 * Replaces a URL's domain name (if it is in the substitution list) with the
	 * current site's domain name
	 *
	 * @param $url string The URL to process
	 *
	 * @return string The processed URL
	 */
	private function replaceDomain($url)
	{
		static $old_domains;
		static $mydomain;

		if (empty($old_domains))
		{
			$temp = explode("\n", $this->cparams->getValue('migratelist', ''));
			if (!empty($temp))
			{
				foreach ($temp as $entry)
				{
					if (substr($entry, -1) == '/')
					{
						$entry = substr($entry, 0, -1);
					}
					if (substr($entry, 0, 7) == 'http://')
					{
						$entry = substr($entry, 7);
					}
					if (substr($entry, 0, 8) == 'https://')
					{
						$entry = substr($entry, 8);
					}
					$old_domains[] = $entry;
				}
			}
		}
		if (empty($mydomain))
		{
			$mydomain = JURI::base(false);
			if (substr($mydomain, -1) == '/')
			{
				$mydomain = substr($mydomain, 0, -1);
			}
			if (substr($mydomain, 0, 7) == 'http://')
			{
				$mydomain = substr($mydomain, 7);
			}
			if (substr($mydomain, 0, 8) == 'https://')
			{
				$mydomain = substr($mydomain, 8);
			}
		}

		if (!empty($old_domains))
		{
			foreach ($old_domains as $domain)
			{
				if (substr($url, 0, strlen($domain)) == $domain)
				{
					return $mydomain . substr($url, strlen($domain));
				}
				elseif (substr($url, 0, strlen($domain) + 7) == 'http://' . $domain)
				{
					return 'http://' . $mydomain . substr($url, strlen($domain) + 7);
				}
				elseif (substr($url, 0, strlen($domain) + 8) == 'https://' . $domain)
				{
					return 'https://' . $mydomain . substr($url, strlen($domain) + 8);
				}
			}
		}

		return $url;
	}


	/**
	 * Converts all HTTP URLs to HTTPS URLs when the site is accessed over SSL
	 */
	private function httpsizer()
	{
		// Make sure we're accessed over SSL (HTTPS)
		$uri = JURI::getInstance();
		$protocol = $uri->toString(array('scheme'));
		if ($protocol != 'https://')
		{
			return;
		}

		$buffer = JResponse::getBody();
		$buffer = str_replace('http://', 'https://', $buffer);
		JResponse::setBody($buffer);
	}

	/**
	 * Figures out if a URL is internal (comes from this site) or if it is an
	 * external URL, while figuring out the file it refers to as well.
	 *
	 * @param string $url Input: the URL; Output: the file for this URL
	 *
	 * @return bool
	 */
	private function isInternal(&$url)
	{
		if ((strtolower(substr($url, 0, 7)) == 'http://') ||
			(strtolower(substr($url, 0, 8)) == 'https://')
		)
		{
			// Strip the protocol from the URL
			if ((strtolower(substr($url, 0, 7)) == 'http://'))
			{
				$url = substr($url, 7);
			}
			else
			{
				$url = substr($url, 8);
			}
			// Strip the protocol from our own site's URL
			if ((strtolower(substr($this->baseURL, 0, 7)) == 'http://'))
			{
				$base = substr($this->baseURL, 7);
			}
			else
			{
				$base = substr($this->baseURL, 8);
			}
			// Does the domain match?
			if (strtolower(substr($url, 0, strlen($base))) == strtolower($base))
			{
				// Yes, trim the url
				$url = ltrim(substr($url, strlen($base)), '/\\');

				return true;
			}
			else
			{
				// Nope, it's an external URL
				return false;
			}
		}
		else
		{
			// No protocol, ergo we are a relative internal URL

			$app = JFactory::getApplication();
			if ((substr($url, 0, 1) != '/') && ($app->getName() == 'admin'))
			{
				// Relative URL to the administrator directory
				$url = 'administrator/' . $url;
			}

			$url = ltrim($url, '/\\');

			return true;
		}
	}
}

function AdminToolsLateBoundAfterRender()
{
	$subject = array();
	$plugin = plgSystemAdmintoolsCore::fetchMyself();
	$plugin->onAfterRenderLatebound();
}