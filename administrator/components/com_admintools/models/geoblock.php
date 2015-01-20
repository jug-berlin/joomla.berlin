<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsModelGeoblock extends F0FModel
{
	private $allContinents = array(
		'AF' => 'Africa',
		'NA' => 'North America',
		'SA' => 'South America',
		'AN' => 'Antartica',
		'AS' => 'Asia',
		'EU' => 'Europe',
		'OC' => 'Oceania'
	);

	private $allCountries = array(
		"A1" => "Anonymous Proxy", "A2" => "Satellite Provider", "O1" => "Other Country", "AD" => "Andorra", "AE" => "United Arab Emirates",
		"AF" => "Afghanistan", "AG" => "Antigua and Barbuda", "AI" => "Anguilla", "AL" => "Albania", "AM" => "Armenia", "AN" => "Netherlands Antilles",
		"AO" => "Angola", "AP" => "Asia/Pacific Region", "AQ" => "Antarctica", "AR" => "Argentina", "AS" => "American Samoa", "AT" => "Austria",
		"AU" => "Australia", "AW" => "Aruba", "AX" => "Aland Islands", "AZ" => "Azerbaijan", "BA" => "Bosnia and Herzegovina", "BB" => "Barbados",
		"BD" => "Bangladesh", "BE" => "Belgium", "BF" => "Burkina Faso", "BG" => "Bulgaria", "BH" => "Bahrain", "BI" => "Burundi", "BJ" => "Benin",
		"BM" => "Bermuda", "BN" => "Brunei Darussalam", "BO" => "Bolivia", "BR" => "Brazil", "BS" => "Bahamas", "BT" => "Bhutan", "BV" => "Bouvet Island",
		"BW" => "Botswana", "BY" => "Belarus", "BZ" => "Belize", "CA" => "Canada", "CC" => "Cocos (Keeling) Islands", "CD" => "Congo, The Democratic Republic of the",
		"CF" => "Central African Republic", "CG" => "Congo", "CH" => "Switzerland", "CI" => "Cote d'Ivoire", "CK" => "Cook Islands", "CL" => "Chile", "CM" => "Cameroon",
		"CN" => "China", "CO" => "Colombia", "CR" => "Costa Rica", "CU" => "Cuba", "CV" => "Cape Verde", "CX" => "Christmas Island", "CY" => "Cyprus",
		"CZ" => "Czech Republic", "DE" => "Germany", "DJ" => "Djibouti", "DK" => "Denmark", "DM" => "Dominica", "DO" => "Dominican Republic", "DZ" => "Algeria",
		"EC" => "Ecuador", "EE" => "Estonia", "EG" => "Egypt", "EH" => "Western Sahara", "ER" => "Eritrea", "ES" => "Spain", "ET" => "Ethiopia",
		"EU" => "Europe", "FI" => "Finland", "FJ" => "Fiji", "FK" => "Falkland Islands (Malvinas)", "FM" => "Micronesia, Federated States of",
		"FO" => "Faroe Islands", "FR" => "France", "GA" => "Gabon", "GB" => "United Kingdom", "GD" => "Grenada", "GE" => "Georgia", "GF" => "French Guiana",
		"GG" => "Guernsey", "GH" => "Ghana", "GI" => "Gibraltar", "GL" => "Greenland", "GM" => "Gambia", "GN" => "Guinea", "GP" => "Guadeloupe", "GQ" => "Equatorial Guinea",
		"GR" => "Greece", "GS" => "South Georgia and the South Sandwich Islands", "GT" => "Guatemala", "GU" => "Guam", "GW" => "Guinea-Bissau", "GY" => "Guyana",
		"HK" => "Hong Kong", "HM" => "Heard Island and McDonald Islands", "HN" => "Honduras", "HR" => "Croatia", "HT" => "Haiti", "HU" => "Hungary",
		"ID" => "Indonesia", "IE" => "Ireland", "IL" => "Israel", "IM" => "Isle of Man", "IN" => "India", "IO" => "British Indian Ocean Territory", "IQ" => "Iraq",
		"IR" => "Iran, Islamic Republic of", "IS" => "Iceland", "IT" => "Italy", "JE" => "Jersey", "JM" => "Jamaica", "JO" => "Jordan", "JP" => "Japan",
		"KE" => "Kenya", "KG" => "Kyrgyzstan", "KH" => "Cambodia", "KI" => "Kiribati", "KM" => "Comoros", "KN" => "Saint Kitts and Nevis",
		"KP" => "Korea, Democratic People's Republic of", "KR" => "Korea, Republic of", "KW" => "Kuwait", "KY" => "Cayman Islands", "KZ" => "Kazakhstan",
		"LA" => "Lao People's Democratic Republic", "LB" => "Lebanon", "LC" => "Saint Lucia", "LI" => "Liechtenstein", "LK" => "Sri Lanka", "LR" => "Liberia",
		"LS" => "Lesotho", "LT" => "Lithuania", "LU" => "Luxembourg", "LV" => "Latvia", "LY" => "Libyan Arab Jamahiriya", "MA" => "Morocco", "MC" => "Monaco",
		"MD" => "Moldova, Republic of", "ME" => "Montenegro", "MG" => "Madagascar", "MH" => "Marshall Islands", "MK" => "Macedonia, Former Yugoslav Republic of",
		"ML" => "Mali", "MM" => "Myanmar", "MN" => "Mongolia", "MO" => "Macao", "MP" => "Northern Mariana Islands", "MQ" => "Martinique", "MR" => "Mauritania",
		"MS" => "Montserrat", "MT" => "Malta", "MU" => "Mauritius", "MV" => "Maldives", "MW" => "Malawi", "MX" => "Mexico", "MY" => "Malaysia", "MZ" => "Mozambique",
		"NA" => "Namibia", "NC" => "New Caledonia", "NE" => "Niger", "NF" => "Norfolk Island", "NG" => "Nigeria", "NI" => "Nicaragua", "NL" => "Netherlands",
		"NO" => "Norway", "NP" => "Nepal", "NR" => "Nauru", "NU" => "Niue", "NZ" => "New Zealand", "OM" => "Oman", "PA" => "Panama", "PE" => "Peru", "PF" => "French Polynesia",
		"PG" => "Papua New Guinea", "PH" => "Philippines", "PK" => "Pakistan", "PL" => "Poland", "PM" => "Saint Pierre and Miquelon", "PN" => "Pitcairn",
		"PR" => "Puerto Rico", "PS" => "Palestinian Territory", "PT" => "Portugal", "PW" => "Palau", "PY" => "Paraguay", "QA" => "Qatar", "RE" => "Reunion",
		"RO" => "Romania", "RS" => "Serbia", "RU" => "Russian Federation", "RW" => "Rwanda", "SA" => "Saudi Arabia", "SB" => "Solomon Islands", "SC" => "Seychelles",
		"SD" => "Sudan", "SE" => "Sweden", "SG" => "Singapore", "SH" => "Saint Helena", "SI" => "Slovenia", "SJ" => "Svalbard and Jan Mayen", "SK" => "Slovakia",
		"SL" => "Sierra Leone", "SM" => "San Marino", "SN" => "Senegal", "SO" => "Somalia", "SR" => "Suriname", "ST" => "Sao Tome and Principe", "SV" => "El Salvador",
		"SY" => "Syrian Arab Republic", "SZ" => "Swaziland", "TC" => "Turks and Caicos Islands", "TD" => "Chad", "TF" => "French Southern Territories",
		"TG" => "Togo", "TH" => "Thailand", "TJ" => "Tajikistan", "TK" => "Tokelau", "TL" => "Timor-Leste", "TM" => "Turkmenistan", "TN" => "Tunisia",
		"TO" => "Tonga", "TR" => "Turkey", "TT" => "Trinidad and Tobago", "TV" => "Tuvalu", "TW" => "Taiwan", "TZ" => "Tanzania, United Republic of",
		"UA" => "Ukraine", "UG" => "Uganda", "UM" => "United States Minor Outlying Islands", "US" => "United States", "UY" => "Uruguay", "UZ" => "Uzbekistan",
		"VA" => "Holy See (Vatican City State)", "VC" => "Saint Vincent and the Grenadines", "VE" => "Venezuela", "VG" => "Virgin Islands, British",
		"VI" => "Virgin Islands, U.S.", "VN" => "Vietnam", "VU" => "Vanuatu", "WF" => "Wallis and Futuna", "WS" => "Samoa", "YE" => "Yemen", "YT" => "Mayotte",
		"ZA" => "South Africa", "ZM" => "Zambia", "ZW" => "Zimbabwe"
	);

	private $countries = array();
	private $continents = array();

	public function getConfig()
	{
		if (interface_exists('JModel'))
		{
			$params = JModelLegacy::getInstance('Storage', 'AdmintoolsModel');
		}
		else
		{
			$params = JModel::getInstance('Storage', 'AdmintoolsModel');
		}

		$countries = $params->getValue('geoblockcountries', '');
		if (empty($countries))
		{
			$this->countries = array();
		}
		else
		{
			if (strstr($countries, ','))
			{
				$this->countries = explode(',', $countries);
			}
			else
			{
				$this->countries = array($countries);
			}
		}

		$continents = $params->getValue('geoblockcontinents', '');
		if (empty($continents))
		{
			$this->continents = array();
		}
		else
		{
			if (strstr($continents, ','))
			{
				$this->continents = explode(',', $continents);
			}
			else
			{
				$this->continents = array($continents);
			}
		}
	}

	public function saveConfig($newConfig)
	{
		if (interface_exists('JModel'))
		{
			$params = JModelLegacy::getInstance('Storage', 'AdmintoolsModel');
		}
		else
		{
			$params = JModel::getInstance('Storage', 'AdmintoolsModel');
		}

		$countries = $newConfig['countries'];
		$continents = $newConfig['continents'];

		$params->setValue('geoblockcountries', $countries);
		$params->setValue('geoblockcontinents', $continents);
		$params->save();
	}

	public function getCountries()
	{
		if (empty($this->countries))
		{
			$this->getConfig();
		}

		$html = '<tr class="row0">';
		$i = 0;
		foreach ($this->allCountries as $code => $name)
		{
			$i++;
			$html .= '<td>';
			$checked = in_array($code, $this->countries) ? 'checked="$checked"' : '';
			$html .= '<input type="checkbox" class="country" name="country[' . $code . ']" id="country' . $code . '" ' . $checked . ' />';
			$html .= '<label for="country' . $code . '">' . $name . '</label><br/>';
			$html .= '</td>';

			if ($i % 3 == 0)
			{
				if ($i % 6 == 0)
				{
					$html .= '</tr><tr class="row0">';
				}
				else
				{
					$html .= '</tr><tr class="row1">';
				}
			}
		}

		return $html;
	}

	public function getContinents()
	{
		if (empty($this->continents))
		{
			$this->getConfig();
		}

		$html = '';
		foreach ($this->allContinents as $code => $name)
		{
			if (empty($name))
			{
				continue;
			}
			$checked = in_array($code, $this->continents) ? 'checked="$checked"' : '';
			$html .= '<input type="checkbox" name="continent[' . $code . ']" id="continent' . $code . '" ' . $checked . ' />';
			$html .= '<label for="continent' . $code . '">' . $name . '</label><br/>';
		}

		return $html;
	}

	/**
	 * Do we have the Akeeba GeoIP provider plugin installed?
	 *
	 * @return  boolean  False = not installed, True = installed
	 */
	public function hasGeoIPPlugin()
	{
		static $result = null;

		if (is_null($result))
		{
			$db = JFactory::getDbo();

			$query = $db->getQuery(true)
				->select('COUNT(*)')
				->from($db->qn('#__extensions'))
				->where($db->qn('type') . ' = ' . $db->q('plugin'))
				->where($db->qn('folder') . ' = ' . $db->q('system'))
				->where($db->qn('element') . ' = ' . $db->q('akgeoip'));
			$db->setQuery($query);
			$result = $db->loadResult();
		}

		return ($result != 0);
	}

	/**
	 * Does the GeoIP database need update?
	 *
	 * @param   integer $maxAge The maximum age of the db in days (default: 15)
	 *
	 * @return  boolean
	 */
	public function dbNeedsUpdate($maxAge = 15)
	{
		$needsUpdate = false;

		if (!$this->hasGeoIPPlugin())
		{
			return $needsUpdate;
		}

		// Get the modification time of the database file
		$filePath = JPATH_ROOT . '/plugins/system/akgeoip/db/GeoLite2-Country.mmdb';
		$modTime = @filemtime($filePath);

		// This is now
		$now = time();

		// Minimum time difference we want (15 days) in seconds
		if ($maxAge <= 0)
		{
			$maxAge = 15;
		}

		$threshold = $maxAge * 24 * 3600;

		// Do we need an update?
		$needsUpdate = ($now - $modTime) > $threshold;

		return $needsUpdate;
	}
}
