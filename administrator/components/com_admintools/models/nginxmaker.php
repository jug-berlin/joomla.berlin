<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

JLoader::import('joomla.application.component.model');

/**
 * NginX Configuration Maker
 */
class AdmintoolsModelNginxmaker extends F0FModel
{
	var $defaultConfig = array(
		// == System configuration ==
		// Host name for HTTPS requests (without https://)
		'httpshost'      => '',
		// Host name for HTTP requests (without http://)
		'httphost'       => '',
		// Base directory of your site (/ for domain's root)
		'rewritebase'    => '',
		// Follow symlinks (may cause a blank page or 500 Internal Server Error)
		'symlinks'       => 0,
		// fastcgi_pass setting
		'fastcgi_pass'	 => '127.0.0.1:9000',

		// == Optimization and utility ==
		// Force index.php parsing before index.html
		'fileorder'      => 1,
		// Set default expiration time to 1 hour
		'exptime'        => 0,
		// Automatically compress static resources
		'autocompress'   => 0,
		// Redirect www and non-www addresses
		'wwwredir'       => 0,
		// Redirect old to new domain
		'olddomain'      => '',
		// HSTS Header (for HTTPS-only sites)
		'hstsheader'     => 0,
		// Forbid displaying in FRAME (for HTTPS-only sites)
		'noframe'        => 0,
		// Disable HTTP methods TRACE and TRACK (protect against XST)
		'notracetrack'   => 0,

		// == Basic security ==
		// Disable directory listings
		'nodirlists'     => 1,
		// Protect against common file injection attacks
		'fileinj'        => 1,
		// Disable PHP Easter Eggs
		'phpeaster'      => 1,
		// Block access from specific user agents
		'nohoggers'      => 0,
		// Block access to configuration.php-dist and htaccess.txt
		'leftovers'      => 1,
		// User agents to block (one per line)
		'hoggeragents'   => array(
			'WebBandit',
			'webbandit',
			'Acunetix',
			'binlar',
			'BlackWidow',
			'Bolt 0',
			'Bot mailto:craftbot@yahoo.com',
			'BOT for JCE',
			'casper',
			'checkprivacy',
			'ChinaClaw',
			'clshttp',
			'cmsworldmap',
			'comodo',
			'Custo',
			'Default Browser 0',
			'diavol',
			'DIIbot',
			'DISCo',
			'dotbot',
			'Download Demon',
			'eCatch',
			'EirGrabber',
			'EmailCollector',
			'EmailSiphon',
			'EmailWolf',
			'Express WebPictures',
			'extract',
			'ExtractorPro',
			'EyeNetIE',
			'feedfinder',
			'FHscan',
			'FlashGet',
			'flicky',
			'GetRight',
			'GetWeb!',
			'Go-Ahead-Got-It',
			'Go!Zilla',
			'grab',
			'GrabNet',
			'Grafula',
			'harvest',
			'HMView',
			'ia_archiver',
			'Image Stripper',
			'Image Sucker',
			'InterGET',
			'Internet Ninja',
			'InternetSeer.com',
			'jakarta',
			'Java',
			'JetCar',
			'JOC Web Spider',
			'kmccrew',
			'larbin',
			'LeechFTP',
			'libwww',
			'Mass Downloader',
			'Maxthon$',
			'microsoft.url',
			'MIDown tool',
			'miner',
			'Mister PiX',
			'NEWT',
			'MSFrontPage',
			'Navroad',
			'NearSite',
			'Net Vampire',
			'NetAnts',
			'NetSpider',
			'NetZIP',
			'nutch',
			'Octopus',
			'Offline Explorer',
			'Offline Navigator',
			'PageGrabber',
			'Papa Foto',
			'pavuk',
			'pcBrowser',
			'PeoplePal',
			'planetwork',
			'psbot',
			'purebot',
			'pycurl',
			'RealDownload',
			'ReGet',
			'Rippers 0',
			'SeaMonkey$',
			'sitecheck.internetseer.com',
			'SiteSnagger',
			'skygrid',
			'SmartDownload',
			'sucker',
			'SuperBot',
			'SuperHTTP',
			'Surfbot',
			'tAkeOut',
			'Teleport Pro',
			'Toata dragostea mea pentru diavola',
			'turnit',
			'vikspider',
			'VoidEYE',
			'Web Image Collector',
			'Web Sucker',
			'WebAuto',
			'WebCopier',
			'WebFetch',
			'WebGo IS',
			'WebLeacher',
			'WebReaper',
			'WebSauger',
			'Website eXtractor',
			'Website Quester',
			'WebStripper',
			'WebWhacker',
			'WebZIP',
			'Wget',
			'Widow',
			'WWW-Mechanize',
			'WWWOFFLE',
			'Xaldon WebSpider',
			'Yandex',
			'Zeus',
			'zmeu',
			'CazoodleBot',
			'discobot',
			'ecxi',
			'GT::WWW',
			'heritrix',
			'HTTP::Lite',
			'HTTrack',
			'ia_archiver',
			'id-search',
			'id-search.org',
			'IDBot',
			'Indy Library',
			'IRLbot',
			'ISC Systems iRc Search 2.1',
			'LinksManager.com_bot',
			'linkwalker',
			'lwp-trivial',
			'MFC_Tear_Sample',
			'Microsoft URL Control',
			'Missigua Locator',
			'panscient.com',
			'PECL::HTTP',
			'PHPCrawl',
			'PleaseCrawl',
			'SBIder',
			'Snoopy',
			'Steeler',
			'URI::Fetch',
			'urllib',
			'Web Sucker',
			'webalta',
			'WebCollage',
			'Wells Search II',
			'WEP Search',
			'zermelo',
			'ZyBorg',
			'Indy Library',
			'libwww-perl',
			'Go!Zilla',
			'TurnitinBot',
		),
		// Block common exploits
		'blockcommon'    => 1,
		// Enable SEF URLs
		'enablesef'      => 1,

		// == Server protection ==
		// -- Toggle protection
		// Back-end protection
		'backendprot'    => 1,
		// Front-end protection
		'frontendprot'   => 1,
		// -- Fine-tuning
		// Back-end directories where file type exceptions are allowed
		'bepexdirs'      => array('components', 'modules', 'templates', 'images', 'plugins'),
		// Back-end file types allowed in selected directories
		'bepextypes'     => array(
			'jpe', 'jpg', 'jpeg', 'jp2', 'jpe2', 'png', 'gif', 'bmp', 'css', 'js',
			'swf', 'html', 'mpg', 'mp3', 'mpeg', 'mp4', 'avi', 'wav', 'ogg', 'ogv',
			'xls', 'xlsx', 'doc', 'docx', 'ppt', 'pptx', 'zip', 'rar', 'pdf', 'xps',
			'txt', '7z', 'svg', 'odt', 'ods', 'odp', 'flv', 'mov', 'htm', 'ttf',
			'woff', 'eot',
			'JPG', 'JPEG', 'PNG', 'GIF', 'CSS', 'JS', 'TTF', 'WOFF', 'EOT'
		),
		// Front-end directories where file type exceptions are allowed
		'fepexdirs'      => array('components', 'modules', 'templates', 'images', 'plugins', 'media', 'libraries', 'media/jui/fonts'),
		// Front-end file types allowed in selected directories
		'fepextypes'     => array(
			'jpe', 'jpg', 'jpeg', 'jp2', 'jpe2', 'png', 'gif', 'bmp', 'css', 'js',
			'swf', 'html', 'mpg', 'mp3', 'mpeg', 'mp4', 'avi', 'wav', 'ogg', 'ogv',
			'xls', 'xlsx', 'doc', 'docx', 'ppt', 'pptx', 'zip', 'rar', 'pdf', 'xps',
			'txt', '7z', 'svg', 'odt', 'ods', 'odp', 'flv', 'mov', 'ico', 'htm',
			'ttf', 'woff', 'eot',
			'JPG', 'JPEG', 'PNG', 'GIF', 'CSS', 'JS', 'TTF', 'WOFF', 'EOT'
		),
		// -- Exceptions
		// Allow direct access to these files
		'exceptionfiles' => array(
			"administrator/components/com_akeeba/restore.php",
			"administrator/components/com_admintools/restore.php",
			"administrator/components/com_joomlaupdate/restore.php"
		),
		// Allow direct access, except .php files, to these directories
		'exceptiondirs'  => array(),
		// Allow direct access, including .php files, to these directories
		'fullaccessdirs' => array(
			"templates/your_template_name_here"
		),

		// == The Kitchen Sink ==
		// Cloudflare IP forwarding
		'cfipfwd'        => 0,
		// Optimise timeout handling
		'opttimeout'     => 0,
		// Optimise socket settings
		'optsockets'     => 0,
		// Optimise TCP performance
		'opttcpperf'     => 0,
		// Optimise output buffering
		'optoutbuf'      => 0,
		// Optimise file handle cache
		'optfhndlcache'  => 0,
		// Set the default character encoding to utf-8
		'encutf8'        => 0,
		// Tighten NginX security settings
		'nginxsecurity'  => 1,
		// Set maximum client body size to 1G
		'maxclientbody'  => 1,
	);

	private $config = null;

	/**
	 * Initialise the class, setting the correct default host names and base paths for this site
	 *
	 * @param   array $config
	 */
	public function  __construct($config = array())
	{
		parent::__construct($config);

		$myURI = JURI::getInstance();
		$path = $myURI->getPath();
		$path_parts = explode('/', $path);
		$path_parts = array_slice($path_parts, 0, count($path_parts) - 2);
		$path = implode('/', $path_parts);
		$myURI->setPath($path);
		// Unset any query parameters
		$myURI->setQuery('');

		$host = $myURI->toString();
		$host = substr($host, strpos($host, '://') + 3);

		$path = trim($path, '/');

		if (!empty($path))
		{
			$this->defaultConfig['rewritebase'] = $path;
		}
		else
		{
			$this->defaultConfig['rewritebase'] = '/';
		}
		$this->defaultConfig['httphost'] = $host;
		$this->defaultConfig['httpshost'] = $host;
		$this->defaultConfig = (object)$this->defaultConfig;
	}

	/**
	 * Loads the NginX Conf Maker's configuration from the database
	 *
	 * @return array|null|object
	 */
	public function loadConfiguration()
	{
		if (is_null($this->config))
		{

			if (interface_exists('JModel'))
			{
				$params = JModelLegacy::getInstance('Storage', 'AdmintoolsModel');
			}
			else
			{
				$params = JModel::getInstance('Storage', 'AdmintoolsModel');
			}

			$savedConfig = $params->getValue('nginxconfig', '');

			if (!empty($savedConfig))
			{
				if (function_exists('base64_encode') && function_exists('base64_encode'))
				{
					$savedConfig = base64_decode($savedConfig);
				}

				$savedConfig = json_decode($savedConfig, true);
			}
			else
			{
				$savedConfig = array();
			}

			$config = $this->defaultConfig;

			if (!empty($savedConfig))
			{
				foreach ($savedConfig as $key => $value)
				{
					$config->$key = $value;
				}
			}

			$this->config = $config;
		}

		return $this->config;
	}

	/**
	 * Save the NginX Conf Maker's configuration to the database
	 *
	 * @param   array $data
	 * @param   bool  $isConfigInput
	 */
	public function saveConfiguration($data, $isConfigInput = false)
	{
		if ($isConfigInput)
		{
			$config = $data;
		}
		else
		{
			$config = $this->defaultConfig;

			if (!empty($data))
			{
				$ovars = get_object_vars($config);
				$okeys = array_keys($ovars);

				foreach ($data as $key => $value)
				{
					if (in_array($key, $okeys))
					{
						// Clean up array types coming from textareas
						if (in_array($key, array(
							'hoggeragents', 'bepexdirs',
							'bepextypes', 'fepexdirs', 'fepextypes',
							'exceptionfiles', 'exceptionfolders', 'exceptiondirs', 'fullaccessdirs',
							'httpsurls'
						))
						)
						{
							if (empty($value))
							{
								$value = array();
							}
							else
							{
								$value = trim($value);
								$value = explode("\n", $value);

								if (!empty($value))
								{
									$ret = array();

									foreach ($value as $v)
									{
										$vv = trim($v);

										if (!empty($vv))
										{
											$ret[] = $vv;
										}
									}

									if (!empty($ret))
									{
										$value = $ret;
									}
									else
									{
										$value = array();
									}
								}
							}
						}

						$config->$key = $value;
					}
				}
			}
		}

		$this->config = $config;
		$config = json_encode($config);

		// This keeps JRegistry from happily corrupting our data :@
		if (function_exists('base64_encode') && function_exists('base64_encode'))
		{
			$config = base64_encode($config);
		}

		if (interface_exists('JModel'))
		{
			$params = JModelLegacy::getInstance('Storage', 'AdmintoolsModel');
		}
		else
		{
			$params = JModel::getInstance('Storage', 'AdmintoolsModel');
		}

		$params->setValue('nginxconfig', $config);
		$params->save();
	}

	/**
	 * Compile and return the contents of the NginX configuration file
	 *
	 * @return string
	 */
	public function makeNginXConf()
	{
		JLoader::import('joomla.utilities.date');
		$date = new JDate();
		$d = $date->format('Y-m-d H:i:s', true);
		$version = ADMINTOOLS_VERSION;

		$config = $this->loadConfiguration();

		// Load the fastcgi_pass setting
		$fastcgi_pass = $config->fastcgi_pass;

		if (empty($fastcgi_pass))
		{
			$fastcgi_pass = '$fastcgi_pass';
		}

		$fastcgi_pass = trim($fastcgi_pass);

		// Get the directory to the site's root
		$rewritebase = $config->rewritebase;
		$rewritebaseSlash = '/' . trim($rewritebase, '/ ');
		$rewritebaseSlash = ($rewritebaseSlash == '/') ? '' : $rewritebaseSlash;
		$rewritebase = '/' . trim($rewritebase, '/ ');

		$nginxConf = <<<END
### ===========================================================================
### Security Enhanced & Highly Optimized NginX Configuration File for Joomla!
### automatically generated by Admin Tools $version on $d GMT
### ===========================================================================
###
### Admin Tools is Free Software, distributed under the terms of the GNU
### General Public License version 3 or, at your option, any later version
### published by the Free Software Foundation.
###
### !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! IMPORTANT !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
### !!                                                                       !!
### !!  If you get an Internal Server Error 500 or a blank page when trying  !!
### !!  to access your site, remove this file and try tweaking its settings  !!
### !!  in the back-end of the Admin Tools component.                        !!
### !!                                                                       !!
### !!  Remember to include this file in your site's configuration file.     !!
### !!  Also remember to reload or restart NginX after making any change to  !!
### !!  this file.                                                           !!
### !!                                                                       !!
### !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
###

### Prevent access to this file
location = $rewritebaseSlash/nginx.conf {
	log_not_found off;
	access_log off;
	return 404;
	break;
}

location = $rewritebaseSlash/nginx.conf.admintools {
	log_not_found off;
	access_log off;
	return 404;
	break;
}

END;

		if (($config->nodirlists == 1) && ($config->enablesef != 1))
		{
			$nginxConf .= <<< CONFDATA
######################################################################
## Disable directory listings
######################################################################
location $rewritebase {
	autoindex off;
}

CONFDATA;
		}

		// Protect against common file injection attacks?
		if ($config->fileinj == 1)
		{
			$nginxConf .= <<< CONFDATA
######################################################################
## Protect against common file injection attacks
######################################################################
set \$file_injection 0;
if (\$query_string ~ "[a-zA-Z0-9_]=http://") {
	set \$file_injection 1;
}
if (\$query_string ~ "[a-zA-Z0-9_]=(\.\.//?)+") {
	set \$file_injection 1;
}
if (\$query_string ~ "[a-zA-Z0-9_]=/([a-z0-9_.]//?)+") {
	set \$file_injection 1;
}
if (\$file_injection = 1) {
	return 403;
	break;
}

CONFDATA;
		}

		if ($config->phpeaster == 1)
		{
			$nginxConf .= <<<END
######################################################################
## Disable PHP Easter Eggs
######################################################################
if (\$query_string ~ "\=PHP[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}") {
	return 403;
	break;
}

END;
		}

		if ($config->leftovers == 1)
		{
			$nginxConf .= <<<END
######################################################################
## Block access to configuration.php-dist and htaccess.txt
######################################################################
location = $rewritebaseSlash/configuration.php-dist {
	log_not_found off;
	access_log off;
	return 404;
	break;
}

location = $rewritebaseSlash/htaccess.txt {
	log_not_found off;
	access_log off;
	return 404;
	break;
}

location = $rewritebaseSlash/web.config {
	log_not_found off;
	access_log off;
	return 404;
	break;
}

location = $rewritebaseSlash/configuration.php {
	log_not_found off;
	access_log off;
	return 404;
	break;
}

location = $rewritebaseSlash/CONTRIBUTING.md {
	log_not_found off;
	access_log off;
	return 404;
	break;
}

location = $rewritebaseSlash/joomla.xml {
	log_not_found off;
	access_log off;
	return 404;
	break;
}

location = $rewritebaseSlash/LICENSE.txt {
	log_not_found off;
	access_log off;
	return 404;
	break;
}

location = $rewritebaseSlash/phpunit.xml {
	log_not_found off;
	access_log off;
	return 404;
	break;
}

location = $rewritebaseSlash/README.txt {
	log_not_found off;
	access_log off;
	return 404;
	break;
}

location = $rewritebaseSlash/web.config.txt {
	log_not_found off;
	access_log off;
	return 404;
	break;
}

END;
		}

		if (!empty($config->hoggeragents) && ($config->nohoggers == 1))
		{
			$nginxConf .= <<< ENDCONF
######################################################################
## Block access from specific user agents
######################################################################
set \$bad_ua 0;

ENDCONF;

			foreach ($config->hoggeragents as $agent)
			{
				$nginxConf .= <<< ENDCONF
if (\$http_user_agent ~ "$agent") {
	set \$bad_ua 1;
}

ENDCONF;
			}

			$nginxConf .= <<< ENDCONF
if (\$bad_ua = 1) {
	return 403;
}

ENDCONF;
		}

		if ($config->fileorder == 1)
		{
			$nginxConf .= <<<ENDCONF
######################################################################
## Directory indices
## Forces index.php to be read before the index.htm(l) files
######################################################################
index index.php index.html index.htm;

ENDCONF;
		}

		if ($config->symlinks != 0)
		{
			$nginxConf .= <<<ENDCONF
######################################################################
## Disable following symlinks
######################################################################
ENDCONF;
			switch ($config->symlinks)
			{
				case 1:
					$nginxConf .= "disable_symlinks on;\n";
					break;

				case 2:
					$nginxConf .= "disable_symlinks if_not_owner;\n";
					break;
			}
		}

		if ($config->exptime == 1)
		{
			$nginxConf .= <<<ENDCONF
######################################################################
## Set default expiration time
######################################################################
 # CSS and JavaScript : 1 week
location ~* \.(css|js)$ {
		access_log off; log_not_found off;
		expires 1w;
}

# Image files : 1 month
location ~* \.(bmp|gif|jpg|jpeg|jp2|png|svg|tif|tiff|ico|wbmp|wbxml|smil)$ {
		access_log off; log_not_found off;
		expires 1M;
}

# Document files : 1 month
location ~* \.(pdf|txt|xml)$ {
		access_log off; log_not_found off;
		expires 1M;
}

# Audio files : 1 month
location ~* \.(mid|midi|mp3|m4a|m4r|aif|aiff|ra|wav|voc|ogg)$ {
		access_log off; log_not_found off;
		expires 1M;
}

# Video files : 1 month
location ~* \.(swf|vrml|avi|mkv|mpg|mpeg|mp4|m4v|mov|asf)$ {
		access_log off; log_not_found off;
		expires 1M;
}
ENDCONF;
		}

		if ($config->autocompress == 1)
		{
			$nginxConf .= <<<ENDCONF
######################################################################
## Automatic compression of static resources
## Compress text, html, javascript, css, xml and other static resources
## May kill access to your site for old versions of Internet Explorer
######################################################################
# The following is the actual automatic compression setup
gzip            on;
gzip_vary		on;
gzip_comp_level 6;
gzip_proxied	expired no-cache no-store private auth;
gzip_min_length 1000;
gzip_http_version 1.1;
gzip_types      text/plain text/css application/xhtml+xml application/xml+rss application/rss+xml application/x-javascript application/javascript text/javascript application/json text/xml application/xml;
gzip_buffers    16 8k;
gzip_disable "MSIE [1-6]\.(?!.*SV1)";

ENDCONF;
		}

		$host = strtolower($config->httphost);

		if (substr($host, 0, 4) == 'www.')
		{
			$wwwHost = $host;
			$noWwwHost = substr($host, 4);
		}
		else
		{
			$noWwwHost = $host;
			$wwwHost = 'www.' . $host;
		}

		switch ($config->wwwredir)
		{
			case 1:
				// non-www to www
				$nginxConf .= <<<END
######################################################################
## Redirect non-www to www
######################################################################
if (\$host = '$noWwwHost' ) {
	rewrite ^/(.*)$ \$scheme://$wwwHost/$1 permanent;
}

END;
				break;

			case 2:
				// www to non-www
				$nginxConf .= <<<END
######################################################################
## Redirect www to non-www
######################################################################
if (\$host = '$wwwHost' ) {
	rewrite ^/(.*)$ \$scheme://$noWwwHost/$1 permanent;
}

END;
				break;
		}

		if (!empty($config->olddomain))
		{
			$nginxConf .= <<<END
######################################################################
## Redirect old to new domains
######################################################################

END;
			$domains = trim($config->olddomain);
			$domains = explode(',', $domains);
			$newdomain = $config->httphost;

			foreach ($domains as $olddomain)
			{
				$olddomain = trim($olddomain);

				if (empty($olddomain))
				{
					continue;
				}

				$olddomain = $this->escape_string_for_regex($olddomain);
				$nginxConf .= <<<END
if (\$host ~ "$olddomain$" ) {
	rewrite ^/(.*)$ \$scheme://$newdomain/$1 permanent;
}

END;
			}
		}

		if ($config->hstsheader == 1)
		{
			$nginxConf .= <<<END
## HSTS Header - See http://en.wikipedia.org/wiki/HTTP_Strict_Transport_Security
add_header Strict-Transport-Security max-age=31536000;

END;
		}

		if ($config->noframe == 1)
		{
			$nginxConf .= <<<END
## Forbid displaying in FRAME (for HTTPS-only sites)
add_header X-Frame-Options SAMEORIGIN;

END;
		}

		if ($config->notracetrack == 1)
		{
			$nginxConf .= <<<END
## Disable HTTP methods TRACE and TRACK (protect against XST)
if (\$request_method ~ ^(TRACE|TRACK)$ ) {
	return 405;
}

END;
		}

		if ($config->cfipfwd == 1)
		{
			$nginxConf .= <<<END
######################################################################
## CloudFlare support - see https://support.cloudflare.com/hc/en-us/articles/200170706-Does-CloudFlare-have-an-IP-module-for-Nginx-
######################################################################
set_real_ip_from   199.27.128.0/21;
set_real_ip_from   173.245.48.0/20;
set_real_ip_from   103.21.244.0/22;
set_real_ip_from   103.22.200.0/22;
set_real_ip_from   103.31.4.0/22;
set_real_ip_from   141.101.64.0/18;
set_real_ip_from   108.162.192.0/18;
set_real_ip_from   190.93.240.0/20;
set_real_ip_from   188.114.96.0/20;
set_real_ip_from   197.234.240.0/22;
set_real_ip_from   198.41.128.0/17;
set_real_ip_from   162.158.0.0/15;
set_real_ip_from   104.16.0.0/12;
set_real_ip_from   2400:cb00::/32;
set_real_ip_from   2606:4700::/32;
set_real_ip_from   2803:f800::/32;
set_real_ip_from   2405:b500::/32;
set_real_ip_from   2405:8100::/32;
real_ip_header     X-Forwarded-For;

END;
		}

		if ($config->opttimeout == 1)
		{
			$nginxConf .= <<<END
# -- Timeout handling, see http://wiki.nginx.org/HttpCoreModule
client_header_timeout 10;
client_body_timeout   10;
send_timeout          30;
keepalive_timeout     30s;

END;
		}

		if ($config->optsockets == 1)
		{
			$nginxConf .= <<<END
# -- Socket settings, see http://wiki.nginx.org/HttpCoreModule
connection_pool_size        8192;
client_header_buffer_size   4k;
large_client_header_buffers 8 8k;
request_pool_size           8k;

END;
		}

		if ($config->opttcpperf == 1)
		{
			$nginxConf .= <<<END
# -- Performance, see http://wiki.nginx.org/HttpCoreModule
sendfile on;
sendfile_max_chunk 1m;
postpone_output 0;
tcp_nopush on;
tcp_nodelay on;

END;
		}

		if ($config->optoutbuf == 1)
		{
			$nginxConf .= <<<END
# -- Output buffering, see http://wiki.nginx.org/HttpCoreModule
output_buffers 8 32k;

END;
		}

		if ($config->optfhndlcache == 1)
		{
			$nginxConf .= <<<END
# -- Filehandle Cache, useful when serving a large number of static files (Joomla! sites do that)
open_file_cache max=2000 inactive=20s;
open_file_cache_valid 30s;
open_file_cache_min_uses 2;
open_file_cache_errors on;

END;
		}

		if ($config->encutf8 == 1)
		{
			$nginxConf .= <<<END
# -- Character encoding, see http://wiki.nginx.org/HttpCharsetModule
charset                 utf-8;
source_charset          utf-8;

END;
		}

		if ($config->nginxsecurity == 1)
		{
			$nginxConf .= <<<END
# -- Security options, see http://wiki.nginx.org/HttpCoreModule
server_name_in_redirect off;
server_tokens off;
ignore_invalid_headers on;

END;
		}

		if ($config->maxclientbody == 1)
		{
			$nginxConf .= <<<END
# -- Maximum client body size set to 1 Gigabyte
client_max_body_size 1G;

END;
		}

		if ($config->blockcommon == 1)
		{
			$nginxConf .= <<<END
set \$common_exploit 0;
if (\$query_string ~ "proc/self/environ") {
	set \$common_exploit 1;
}
if (\$query_string ~ "mosConfig_[a-zA-Z_]{1,21}(=|\%3D)") {
	set \$common_exploit 1;
}
if (\$query_string ~ "base64_(en|de)code\(.*\)") {
	set \$common_exploit 1;
}
if (\$query_string ~ "(<|%3C).*script.*(>|%3E)") {
	set \$common_exploit 1;
}
if (\$query_string ~ "GLOBALS(=|\[|\%[0-9A-Z]{0,2})") {
	set \$common_exploit 1;
}
if (\$query_string ~ "_REQUEST(=|\[|\%[0-9A-Z]{0,2})") {
	set \$common_exploit 1;
}
if (\$common_exploit = 1) {
	return 403;
}

END;
		}

		if ($config->enablesef == 1)
		{
			$disableDirListing = $config->nodirlists ? "## Disable directory listings\n\tautoindex off;" : '';
			$nginxConf .= <<<END
## Enable SEF URLs
location / {
	try_files \$uri \$uri/ /index.php?\$args;
	$disableDirListing
}
location ~* /index.php$ {
	fastcgi_pass $fastcgi_pass;
	break;
}

END;
		}

		$nginxConf .= <<< END
######################################################################
## Advanced server protection rules exceptions
######################################################################

END;

		if (!empty($config->exceptionfiles))
		{
			foreach ($config->exceptionfiles as $file)
			{
				$file = $this->escape_string_for_regex($file);
				if (substr($file, -4) == '.php')
				{
					$nginxConf .= <<<END
location = $rewritebaseSlash/$file {
	fastcgi_pass $fastcgi_pass;
	break;
}

END;
				}
				else
				{
					$nginxConf .= <<<END
location = $rewritebaseSlash/$file {
	break;
}

END;
				}
			}
		}

		if (!empty($config->exceptiondirs))
		{
			foreach ($config->exceptiondirs as $dir)
			{
				$dir = trim($dir, '/');
				$dir = $this->escape_string_for_regex($dir);
				$nginxConf .= <<<END
location ~* ^$rewritebaseSlash/$dir/.*\.php$
{
	break;
}
location ~* ^$rewritebaseSlash/$dir/.*$
{
	break;
}

END;
			}
		}

		if (!empty($config->fullaccessdirs))
		{
			foreach ($config->fullaccessdirs as $dir)
			{
				$dir = trim($dir, '/');
				$dir = $this->escape_string_for_regex($dir);
				$nginxConf .= <<<END
location ~* ^$rewritebaseSlash/$dir/.*$
{
	break;
}

END;
			}
		}

		$nginxConf .= <<< END
######################################################################
## Advanced server protection
######################################################################

END;

		if ($config->backendprot == 1)
		{
			$bedirs = implode('|', $config->bepexdirs);
			$betypes = implode('|', $config->bepextypes);
			$nginxConf .= <<<END
# Allow media files in select back-end directories
location ~* ^$rewritebaseSlash/administrator/($bedirs)/.*.($betypes)$ {
	break;
}

# Allow access to the back-end index.php file
location = $rewritebaseSlash/administrator/index.php {
	fastcgi_pass $fastcgi_pass;
	break;
}
location ~* $rewritebaseSlash/administrator$ {
	return 301 $rewritebaseSlash/administrator/index.php;
}
location ~* $rewritebaseSlash/administrator/ {
	return 301 $rewritebaseSlash/administrator/index.php;
}

# Disable access to everything else.
location ~* $rewritebaseSlash/administrator.*$ {
	# If it is a file, directory or symlink and I haven't deliberately
	# enabled access to it, forbid any access to it!
	if (-e \$request_filename) {
		return 403;
	}
	# In any other case, just treat as a SEF URL
	try_files \$uri \$uri/ /administrator/index.php?\$args;
}

END;
		}

		if ($config->frontendprot == 1)
		{
			$fedirs = implode('|', $config->fepexdirs);
			$fetypes = implode('|', $config->fepextypes);
			$nginxConf .= <<<END
# Allow media files in select front-end directories
location ~* ^$rewritebaseSlash/($fedirs)/.*.($fetypes)$ {
	break;
}

## Disallow front-end access for certain Joomla! system directories (unless access to their files is allowed above)
location ~* ^$rewritebaseSlash/includes/js/ {
	return 403;
}
location ~* ^$rewritebaseSlash/(cache|includes|language|logs|log|tmp)/ {
	return 403;
}

END;
			if ($config->enablesef != 1)
			{
				$nginxConf .= <<<END
# Allow access to the front-end index.php file
location ~* $rewritebaseSlash/ {
	return 301 $rewritebaseSlash/index.php;
}
location $rewritebaseSlash/index.php {
	fastcgi_pass $fastcgi_pass;
	break;
}

END;
			}

			$nginxConf .= <<<END
# Allow access to /
location ~* $rewritebaseSlash/$ {
	return 301 $rewritebaseSlash/index.php;
}

# Disable access to everything else.
location ~* $rewritebaseSlash/.*$ {
	# If it is a file, directory or symlink and I haven't deliberately
	# enabled access to it, forbid any access to it!
	if (-e \$request_filename) {
		return 403;
	}
	# In any other case, just treat as a SEF URL
	try_files \$uri \$uri/ /index.php?\$args;
}

END;
		}

		$nginxConf .= "##### Advanced server protection -- END\n\n";

		return $nginxConf;
	}

	public function writeNginXConf()
	{
		$nginxConf = $this->makeNginXConf();

		JLoader::import('joomla.filesystem.file');

		if (@file_exists(JPATH_ROOT . '/nginx.conf'))
		{
			JFile::copy('nginx.conf', 'nginx.conf.admintools', JPATH_ROOT);
		}

		return JFile::write(JPATH_ROOT . DIRECTORY_SEPARATOR . 'nginx.conf', $nginxConf);
	}

	private function escape_string_for_regex($str)
	{
		//All regex special chars (according to arkani at iol dot pt below):
		// \ ^ . $ | ( ) [ ]
		// * + ? { } , -

		$patterns = array(
			'/\//', '/\^/', '/\./', '/\$/', '/\|/',
			'/\(/', '/\)/', '/\[/', '/\]/', '/\*/', '/\+/',
			'/\?/', '/\{/', '/\}/', '/\,/', '/\-/'
		);
		$replace = array(
			'\/', '\^', '\.', '\$', '\|', '\(', '\)',
			'\[', '\]', '\*', '\+', '\?', '\{', '\}', '\,', '\-'
		);

		return preg_replace($patterns, $replace, $str);
	}
}