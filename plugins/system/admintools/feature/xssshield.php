<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

class AtsystemFeatureXssshield extends AtsystemFeatureAbstract
{
	protected $loadOrder = 320;

	protected static $safe_keys = array();

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		if (!F0FPlatform::getInstance()->isFrontend())
		{
			return false;
		}

		if ($this->skipFiltering)
		{
			return false;
		}

		return ($this->cparams->getValue('xssshield', 0) == 1);
	}

	public function onAfterInitialise()
	{
		// Initialise safe keys

		$parts = explode(',', $this->cparams->getValue('xssshield_safe_keys', ''));

		foreach ($parts as $part)
		{
			// Sanity check to avoid wrong input
			$temp = trim($part);

			if ($temp)
			{
				self::$safe_keys[] = $temp;
			}
		}

		// Do the filtering

		$hashes = array('get', 'post');

		foreach ($hashes as $hash)
		{
			$input = $this->input->$hash;
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
				$this->exceptionsHandler->blockRequest('xssshield', null, $extraInfo);
			}
		}
	}

	protected function match_array_xss($array)
	{
		$result = false;

		if (is_array($array))
		{
			foreach ($array as $key => $value)
			{
				if (in_array($key, self::$safe_keys))
				{
					continue;
				}

				if (!empty($this->exceptions) && in_array($key, $this->exceptions))
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
	 * Tries to figure out if the given query string looks like an XSS attack. It's not watertight,
	 * but it's better than nothing.
	 *
	 * Based largely on CodeIgniter's XSS cleanup code by EllisLab
	 *
	 * @param string $str The string to filter
	 *
	 * @return bool
	 */
	protected function looksLikeXSS($str)
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
} 