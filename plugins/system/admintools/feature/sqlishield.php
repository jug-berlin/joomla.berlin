<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

class AtsystemFeatureSqlishield extends AtsystemFeatureAbstract
{
	protected $loadOrder = 310;

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

		return ($this->cparams->getValue('sqlishield', 0) == 1);
	}

	/**
	 * Fend off most common types of SQLi attacks. See the comments in the code
	 * for more security-minded information.
	 */
	public function onAfterInitialise()
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
			$input = $this->input->$hash;

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
				$this->exceptionsHandler->blockRequest('sqlishield', null, $extraInfo);
			}
		}
	}
} 