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
 * Checks for database permissions (SHOW permissions)
 */
class AliceCoreDomainChecksRequirementsDbpermissions extends AliceCoreDomainChecksAbstract
{
	public function __construct($logFile = null)
	{
		parent::__construct(40, JText::_('ALICE_ANALYZE_REQUIREMENTS_DBPERMISSIONS'), $logFile);
	}

	public function check()
	{
		$db = \Akeeba\Engine\Factory::getDatabase();

		// Can I execute SHOW statements?
		try
		{
			$result = $db->setQuery('SHOW TABLES')->query();
		}
		catch (Exception $e)
		{
			AliceUtilLogger::WriteLog(_AE_LOG_INFO, $this->checkName . " Test failed, can't execute SHOW TABLES statement");
			$this->setResult(-1);
			throw new Exception(JText::_('ALICE_ANALYZE_REQUIREMENTS_DBPERMISSIONS_ERROR'));
		}

		if ( !$result)
		{
			AliceUtilLogger::WriteLog(_AE_LOG_INFO, $this->checkName . " Test failed, can't execute SHOW TABLES statement");
			$this->setResult(-1);
			throw new Exception(JText::_('ALICE_ANALYZE_REQUIREMENTS_DBPERMISSIONS_ERROR'));
		}

		try
		{
			$result = $db->setQuery('SHOW CREATE TABLE ' . $db->nameQuote('#__ak_profiles'))->query();
		}
		catch (Exception $e)
		{
			AliceUtilLogger::WriteLog(_AE_LOG_INFO, $this->checkName . " Test failed, can't execute SHOW CREATE TABLE statement");
			$this->setResult(-1);
			throw new Exception(JText::_('ALICE_ANALYZE_REQUIREMENTS_DBPERMISSIONS_ERROR'));
		}

		if ( !$result)
		{
			AliceUtilLogger::WriteLog(_AE_LOG_INFO, $this->checkName . " Test failed, can't execute SHOW CREATE TABLE statement");
			$this->setResult(-1);
			throw new Exception(JText::_('ALICE_ANALYZE_REQUIREMENTS_DBPERMISSIONS_ERROR'));
		}

		return true;
	}

	public function getSolution()
	{
		return JText::_('ALICE_ANALYZE_REQUIREMENTS_DBPERMISSIONS_SOLUTION');
	}
}
