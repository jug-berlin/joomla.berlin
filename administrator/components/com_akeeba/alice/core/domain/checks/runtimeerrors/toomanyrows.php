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
 * Checks if the user is trying to backup tables with too many rows, causing the system to fail
 */
class AliceCoreDomainChecksRuntimeerrorsToomanyrows extends AliceCoreDomainChecksAbstract
{
	public function __construct($logFile = null)
	{
		parent::__construct(50, JText::_('ALICE_ANALYZE_RUNTIME_ERRORS_TOOMANYROWS'), $logFile);
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
		$tables    = array();
		$row_limit = 1000000;

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

			// Let's save every scanned table
			preg_match_all('#Continuing dump of (.*?) from record \#(\d+)#i', $data, $matches);

			if (isset($matches[1]) && $matches[1])
			{
				for ($i = 0; $i < count($matches[1]); $i++)
				{
					if ($matches[2][$i] >= $row_limit)
					{
						$table          = trim($matches[1][$i]);
						$tables[$table] = $matches[2][$i];
					}
				}

			}
		}

		fclose($handle);

		if (count($tables))
		{
			$errorMsg = array();

			foreach ($tables as $table => $rows)
			{
				$errorMsg[] = JText::_('ALICE_ANALYZE_RUNTIME_ERRORS_TOOMANYROWS_TABLE') . ' ' . $table . ' ' .
					number_format((float)$rows) . ' ' . JText::_('ALICE_ANALYZE_RUNTIME_ERRORS_TOOMANYROWS_ROWS');
			}

			// Let's raise only a warning, maybe the server is powerful enough to dumb huge tables and the problem is somewhere else
			$this->setResult(0);

			AliceUtilLogger::WriteLog(_AE_LOG_INFO, $this->checkName . ' Test failed, user is trying to backup huge tables (more than 1M of rows).');

			throw new Exception(JText::sprintf('ALICE_ANALYZE_RUNTIME_ERRORS_TOOMANYROWS_ERROR', '<br/>' . implode('<br/>', $errorMsg)));
		}

		AliceUtilLogger::WriteLog(_AE_LOG_INFO, $this->checkName . ' Test passed, there are no issues while creating the backup archive ');

		return true;
	}

	public function getSolution()
	{
		return JText::_('ALICE_ANALYZE_RUNTIME_ERRORS_TOOMANYROWS_SOLUTION');
	}
}
