<?php
/**
 * @package AkeebaBackup
 * @subpackage OneClickAction
 * @copyright Copyright (c)2011-2015 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 *
 * @since 3.3
 */

defined('_JEXEC') or die();

// PHP version check
if(defined('PHP_VERSION')) {
	$version = PHP_VERSION;
} elseif(function_exists('phpversion')) {
	$version = phpversion();
} else {
	$version = '5.0.0'; // all bets are off!
}
if(!version_compare($version, '5.0.0', '>=')) return;

JLoader::import('joomla.application.plugin');

class plgSystemOneclickaction extends JPlugin
{
	/**
	 * Handles the onAfterInitialise event in Joomla!, logging in the user using
	 * the one time password and forwarding him to the action URL
	 */
	public function onAfterInitialise()
	{
		$app = JFactory::getApplication();

		// Only fire in administrator requests
		if(in_array($app->getName(),array('administrator','admin'))) {
			// Make sure it's an OneClickAction request
			$otp = JRequest::getCmd('oneclickaction','');
			if(empty($otp)) return;

			// Check that we do have a table!
			self::_checkInstallation();

			// Perform expiration control
			self::_expirationControl();

			// Make sure this OTP exists
			$db = JFactory::getDBO();
			$sql = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__oneclickaction_actions'))
				->where($db->qn('otp').' = '.$db->q($otp));
			$db->setQuery($sql);
			$oca = $db->loadObject();
			if(empty($oca)) return;

			// Login the user
			$user = JFactory::getUser($oca->userid);
			JLoader::import( 'joomla.user.authentication');
			$app = JFactory::getApplication();
			$authenticate = JAuthentication::getInstance();
			$response = new JAuthenticationResponse();
			$response->status = JAuthentication::STATUS_SUCCESS;
			$response->type = 'joomla';
			$response->username = $user->username;
			$response->email = $user->email;
			$response->fullname = $user->name;
			$response->error_message = '';

			JPluginHelper::importPlugin('user');
			$options = array();

			JLoader::import('joomla.user.helper');
			$results = $app->triggerEvent('onLoginUser', array((array)$response, $options));

			JFactory::getSession()->set('user', $user);

			// Delete all similar OCA records
			$sql = $db->getQuery(true)
				->delete($db->qn('#__oneclickaction_actions'))
				->where($db->qn('actionurl').' = '.$db->q($oca->actionurl));
			$db->setQuery($sql);
			$db->execute();

			// Forward to the requested URL
			$app->redirect($oca->actionurl);
			$app->close();
		}
	}

	public function onOneClickActionEnabled()
	{
		return true;
	}

	/**
	 * Adds a new action URL and returns an one time password to access it. This
	 * is meant to be callable directly.
	 *
	 * @param int $userid The user ID to log in when the generated OTP is used
	 * @param string $actionurl The (relative) URL to redirect to, e.g. 'index.php?option=com_foobar'
	 * @param int $expireIn For how many seconds is this OTP valid. Default: 86400 (24 hours)
	 */
	public static function addAction($userid, $actionurl, $expireIn = 86400)
	{
		self::_checkInstallation();
		self::_expirationControl();

		$db = JFactory::getDBO();

		// Check that the action does not already exist
		$sql = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->qn('#__oneclickaction_actions'))
			->where($db->qn('actionurl').' = '.$db->q($actionurl))
			->where($db->qn('userid').' = '.$db->q($userid));
		$actionsCount = $db->loadResult();
		if($actionsCount) return '';

		// Create a randomized OTP
		JLoader::import('joomla.user.helper');
		$expire = gmdate('Y-m-d H:i:s', time() + (int)$expireIn);
		$otp = JUserHelper::genRandomPassword(64);
		$otp = strtoupper($otp);

		// Insert the OTP and action to the database
		$object = (object)array(
			'userid'	=> $userid,
			'actionurl'	=> $actionurl,
			'otp'		=> $otp,
			'expiry'	=> $expire,
		);
		$db->insertObject('#__oneclickaction_actions', $object);


		// If a DB error occurs, return null
		try {
			$db->execute();
		} catch (Exception $e) {
			return null;
		}

		// All OK, return the OTP
		return $otp;
	}

	/**
	 * Checks that the installation is complete, i.e. the table is created.
	 */
	private static function _checkInstallation()
	{
		if(!self::isMySQL()) return false;

		// @todo Move the SQL to the plugin package and do not run this on Joomla! 1.6 or later
		$db = JFactory::getDBO();
		$db->setQuery('DESCRIBE #__oneclickaction_actions');
		$test = $db->loadResult();
		if(is_null($test) || ($db->getError())) {
			$sql = <<<SQL
CREATE TABLE `#__oneclickaction_actions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `userid` bigint(20) unsigned NOT NULL,
  `actionurl` varchar(4000) NOT NULL,
  `otp` char(64) NOT NULL,
  `expiry` datetime NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;
SQL;
			$db->setQuery($sql);
			$result = $db->execute();
			return $result;
		}
		return true;
	}

	private static function _expirationControl()
	{
		$db = JFactory::getDBO();

		$now = gmdate('Y-m-d H:i:s');
		$now = $db->q($now);

		$sql = $db->getQuery(true)
			->delete($db->qn('#__oneclickaction_actions'))
			->where($db->qn('expiry').' <= '.$now);
		$db->setQuery($sql);
		$db->execute();
	}

	private static function isMySQL()
	{
		$db = JFactory::getDbo();
		return strtolower(substr($db->name, 0, 5)) == 'mysql';
	}
}