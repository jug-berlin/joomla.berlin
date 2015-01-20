<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 *
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license   GNU GPL version 3 or, at your option, any later version
 * @package   ALICE
 *
 */

// Protection against direct access
defined('AKEEBAENGINE') or die();

/**
 * Checks that the Kettenrad instance is not dead; the number of "Starting step" and "Saving Kettenrad" instance
 * must be the same, plus none of the steps could be repeated (except the first one).
 */
class AliceCoreDomainChecksRuntimeerrorsKettenrad extends AliceCoreDomainChecksAbstract
{
	public function __construct($logFile = null)
	{
		parent::__construct(10, JText::_('ALICE_ANALYZE_RUNTIME_ERRORS_KETTENRAD'), $logFile);
	}

	public function check()
	{
		$handle = @fopen($this->logFile, 'r');

		if ($handle === false)
		{
			AliceUtilLogger::WriteLog(_AE_LOG_ERROR, $this->checkName . ' Test error, could not open backup log file.');

			return false;
		}

		$prev_data = '';
		$buffer    = 65536;
		$starting  = array();
		$saving    = array();

		while ( !feof($handle))
		{
			$data = $prev_data . fread($handle, $buffer);

			// Let's find the last occurrence of a new line
			$newLine = strrpos($data, "\n");

			// I didn't hit any EOL char, let's keep reading
			if ($newLine === false)
			{
				$prev_data = $data;
				continue;
			}
			else
			{
				// Gotcha! Let's roll back to its position
				$prev_data = '';
				$rollback  = strlen($data) - $newLine + 1;
				$len       = strlen($data);

				$data = substr($data, 0, $newLine);

				// I have to rollback only if I read the whole buffer (ie I'm not at the end of the file)
				// Using this trick should be much more faster than calling ftell to know where we are
				if ($len == $buffer)
				{
					fseek($handle, -$rollback, SEEK_CUR);
				}
			}

			preg_match_all('#Starting Step number (\d+)#i', $data, $tmp_matches);

			if (isset($tmp_matches[1]))
			{
				$starting = array_merge($starting, $tmp_matches[1]);
			}

			preg_match_all('#Finished Step number#i', $data, $tmp_matches);

			if (isset($tmp_matches[0]))
			{
				$saving = array_merge($saving, $tmp_matches[0]);
			}
		}

		fclose($handle);

		AliceUtilLogger::WriteLog(_AE_LOG_INFO, $this->checkName . ' Detected starting steps: ' . count($starting));
		AliceUtilLogger::WriteLog(_AE_LOG_INFO, $this->checkName . ' Detected saving steps: ' . count($saving));

		// Check that the number of "Starting step" and "Finished Step number" is the same
		if (count($starting) != count($saving))
		{
			AliceUtilLogger::WriteLog(_AE_LOG_INFO, $this->checkName . ' Test failed : ' . count($starting) . ' starting vs ' . count($saving) . ' savings');

			$this->setResult(-1);
			throw new Exception(JText::sprintf('ALICE_ANALYZE_RUNTIME_ERRORS_KETTENRAD_SAVING_DIFFERS', count($starting), count($saving)));
		}

		// Plus, check that none of "Starting step" number is repeated, EXCEPT for the first one (it's ok)
		foreach ($starting as $stepNumber)
		{
			if ($stepNumber == 1)
			{
				continue;
			}

			if (count(array_keys($starting, $stepNumber)) > 1)
			{
				AliceUtilLogger::WriteLog(_AE_LOG_INFO, $this->checkName . ' Test failed, step ' . $stepNumber . ' ran more once');

				$this->setResult(-1);
				throw new Exception(JText::sprintf('ALICE_ANALYZE_RUNTIME_ERRORS_KETTENRAD_STARTING_MORE_ONCE', $stepNumber));
			}
		}

		AliceUtilLogger::WriteLog(_AE_LOG_INFO, $this->checkName . ' Test passed : ' . count($starting) . ' starting vs ' . count($saving) . ' savings');

		return true;
	}

	public function getSolution()
	{
		return JText::_('ALICE_ANALYZE_RUNTIME_ERRORS_KETTENRAD_SOLUTION_1') .
		JText::_('ALICE_ANALYZE_RUNTIME_ERRORS_KETTENRAD_SOLUTION_2');
	}
}
