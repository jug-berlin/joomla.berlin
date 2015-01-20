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
 * Checks for supported DB type and version
 */
class AliceCoreDomainChecksRequirementsDb extends AliceCoreDomainChecksAbstract
{
	public function __construct($logFile = null)
	{
		parent::__construct(20, JText::_('ALICE_ANALYZE_REQUIREMENTS_DATABASE'), $logFile);
	}

	public function check()
	{
		// Instead of reading the log, I can simply take the JDatabase object and test it
		$connector = JFactory::getDbo()->name;
		$version   = JFactory::getDbo()->getVersion();

		AliceUtilLogger::WriteLog(_AE_LOG_INFO, $this->checkName . ' Detected database connector: ' . $connector);
		AliceUtilLogger::WriteLog(_AE_LOG_INFO, $this->checkName . ' Detected database version: ' . $version);

		if ($connector == 'mysql' || $connector == 'mysqli')
		{
			if (version_compare($version, '5.0.47', 'lt'))
			{
				$this->setResult(-1);
				throw new Exception(JText::sprintf('ALICE_ANALYZE_REQUIREMENTS_DATABASE_VERSION_TOO_OLD', $version));
			}
		}
		elseif ($connector == 'oracle')
		{
			$this->setResult(-1);
			throw new Exception(JText::sprintf('ALICE_ANALYZE_REQUIREMENTS_DATABASE_UNSUPPORTED', 'SqlAzure'));
		}
		elseif ($connector == 'pdo')
		{
			$this->setResult(-1);
			throw new Exception(JText::sprintf('ALICE_ANALYZE_REQUIREMENTS_DATABASE_UNSUPPORTED', 'PDO'));
		}
		elseif ($connector == 'postgresql')
		{
			if (version_compare($version, '8.3.18', 'lt'))
			{
				$this->setResult(-1);
				throw new Exception(JText::sprintf('ALICE_ANALYZE_REQUIREMENTS_DATABASE_VERSION_TOO_OLD', $version));
			}
		}
		elseif ($connector == 'sqlsrv' || $connector == 'sqlzure')
		{
			if (version_compare($version, '10.50.1600.1', 'lt'))
			{
				$this->setResult(-1);
				throw new Exception(JText::sprintf('ALICE_ANALYZE_REQUIREMENTS_DATABASE_VERSION_TOO_OLD', $version));
			}
		}
		elseif ($connector == 'sqlite')
		{
			$this->setResult(-1);
			throw new Exception(JText::sprintf('ALICE_ANALYZE_REQUIREMENTS_DATABASE_UNSUPPORTED', 'SQLite'));
		}
		else
		{
			// Unknown database type, throw exception
			$this->setResult(-1);
			throw new Exception(JText::sprintf('ALICE_ANALYZE_REQUIREMENTS_DATABASE_UNKNOWN'));
		}

		return true;
	}

	public function getSolution()
	{
		return JText::_('ALICE_ANALYZE_REQUIREMENTS_DATABASE_SOLUTION');
	}
}
