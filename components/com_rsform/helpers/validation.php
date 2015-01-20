<?php
/**
* @package RSForm! Pro
* @copyright (C) 2007-2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class RSFormProValidations
{
	public static function none($value,$extra=null,$data=null)
	{
		return true;
	}

	public static function alpha($param,$extra=null,$data=null)
	{
		if(strpos($param,"\n") !== false) 
			$param = str_replace(array("\r","\n"),'',$param);
			
		for($i=0;$i<strlen($param);$i++)
			if(strpos($extra,$param[$i]) === false && preg_match('#([^a-zA-Z ])#', $param[$i]))
				return false;
				
		return true;
	}
	
	public static function numeric($param,$extra=null,$data=null)
	{
		if(strpos($param,"\n") !== false) 
			$param = str_replace(array("\r","\n"),'',$param);
		
		for($i=0;$i<strlen($param);$i++)
			if (strpos($extra,$param[$i]) === false && !is_numeric($param[$i]))
				return false;
				
		return true;
	}
	
	public static function alphanumeric($param,$extra = null,$data=null)
	{
		if(strpos($param,"\n") !== false) 
			$param = str_replace(array("\r","\n"),'',$param);
		
		for($i=0;$i<strlen($param);$i++)
			if(strpos($extra,$param[$i]) === false && preg_match('#([^a-zA-Z0-9 ])#', $param[$i]))
				return false;
				
		return true;
	}
	
	public static function alphaaccented($value, $extra=null, $data=null) {
		if (preg_match('#[^[:alpha:] ]#u', $value)) {
			return false;
		}
		return true;
	}
	
	public static function alphanumericaccented($value, $extra=null, $data=null) {
		if (preg_match('#[^[:alpha:]0-9 ]#u', $value)) {
			return false;
		}
		return true;
	}
	
	public static function email($email,$extra=null,$data=null)
	{
		jimport('joomla.mail.helper');
		
		$email = trim($email);
		return JMailHelper::isEmailAddress($email);
	}
	
	public static function emaildns($email,$extra=null,$data=null)
	{
		// Check if it's an email address format
		if (!RSFormProValidations::email($email,$extra,$data))
			return false;
		
		$email = trim($email);
		list($user, $domain) = explode('@', $email, 2);
		
		// checkdnsrr for PHP < 5.3.0
		if (!function_exists('checkdnsrr') && function_exists('exec') && is_callable('exec'))
		{
			@exec('nslookup -type=MX '.escapeshellcmd($domain), $output);
			foreach($output as $line)
				if (preg_match('/^'.preg_quote($domain).'/',$line))
					return true;
			
			return false;
		}
		
		// fallback method...
		if (!function_exists('checkdnsrr') || !is_callable('checkdnsrr'))
			return true;
		
		return checkdnsrr($domain, substr(PHP_OS, 0, 3) == 'WIN' ? 'A' : 'MX');
	}
	
	public static function uniquefield($value, $extra=null,$data=null)
	{
		$db 	= JFactory::getDBO();
		$form   = JRequest::getVar('form');
		$formId = (int) @$form['formId'];
		
		$db->setQuery("SELECT `SubmissionValueId` FROM #__rsform_submission_values WHERE FormId='".$formId."' AND `FieldName`='".$db->escape($data['NAME'])."' AND `FieldValue`='".$db->escape($value)."'");
		return $db->loadResult() ? false : true;
	}
	
	public static function uniquefielduser($value, $extra=null,$data=null)
	{
		$db 	= JFactory::getDBO();
		$form   = JRequest::getVar('form');
		$formId = (int) @$form['formId'];
		$user	= JFactory::getUser();
		
		$db->setQuery("SELECT sv.`SubmissionValueId` FROM #__rsform_submission_values sv LEFT JOIN #__rsform_submissions s ON (sv.SubmissionId=s.SubmissionId) WHERE sv.FormId='".$formId."' AND sv.`FieldName`='".$db->escape($data['NAME'])."' AND sv.`FieldValue`='".$db->escape($value)."' AND (".($user->get('guest') ? "s.`UserIp`='".$db->escape($_SERVER['REMOTE_ADDR'])."'" : "s.`UserId`='".(int) $user->get('id')."'").")");
		return $db->loadResult() ? false : true;
	}
	
	public static function uszipcode($value)
	{
		return preg_match("/^([0-9]{5})(-[0-9]{4})?$/i",$value);
	}
	
	public static function phonenumber($value)
	{
		return preg_match("/\(?\b[0-9]{3}\)?[-. ]?[0-9]{3}[-. ]?[0-9]{4}\b/i", $value);
	}
	
	public static function creditcard($value,$extra=null,$data=null)
	{
		$value = preg_replace ('/[^0-9]+/', '', $value);
		if (!$value)
			return false;
		
		if (preg_match("/^([34|37]{2})([0-9]{13})$/", $value) && self::luhn($value)) // Amex
			return true;
		
		if (preg_match("/^([30|36|38]{2})([0-9]{12})$/", $value) && self::luhn($value)) // Diners
			return true;
		
		if (preg_match("/^(?:6(?:011|5[0-9][0-9])[0-9]{12})$/", $value) && self::luhn($value)) // Discover
			return true;
			
		if (preg_match("/^([51|52|53|54|55]{2})([0-9]{14})$/", $value) && self::luhn($value)) // Master
			return true;
			
		if (preg_match("/^([4]{1})([0-9]{12,15})$/", $value) && self::luhn($value)) // Visa
			return true;
		
		return false;
	}

	public static function custom($param,$extra=null,$data=null)
	{
		if(strpos($param,"\n") !== FALSE) 
			$param = str_replace(array("\r","\n"),'',$param);
		
		for($i=0;$i<strlen($param);$i++)
			if(strpos($extra,$param[$i]) === false)
				return false;
				
		return true;
	}

	public static function password($param,$extra=null,$data=null)
	{
		if ($data['DEFAULTVALUE'] == $param)
			return true;
		
		return false;
	}
	
	public static function ipaddress($param,$extra=null,$data=null)
	{
		return preg_match('#\b(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b#', $param, $match);
	}
	
	public static function validurl($param,$extra=null,$data=null)
	{
		$format = 
		'/^(https?):\/\/'.                                         // protocol
		'(([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+'.         // username
		'(:([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+)?'.      // password
		'@)?(?#'.                                                  // auth requires @
		')((([a-z0-9][a-z0-9-]*[a-z0-9]\.)*'.                      // domain segments AND
		'[a-z][a-z0-9-]*[a-z0-9]'.                                 // top level domain  OR
		'|((\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])\.){3}'.
		'(\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])'.                 // IP address
		')(:\d+)?'.                                                // port
		')(((\/+([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)*'. // path
		'(\?([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)'.      // query string
		'?)?)?'.                                                   // path and query string optional
		'(#([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)?'.      // fragment
		'$/i';
		
		return preg_match($format, $param, $match);
	}
	
	public static function regex($value,$pattern=null,$data=null) {
		return preg_match($pattern, $value);
	}
	
	public static function sameas($value, $secondField, $data) {
		$valid 	= false;
		$form 	= JRequest::getVar('form');
		if (isset($form[$secondField])) {
			$secondValue = is_array($form[$secondField]) ? implode('', $form[$secondField]) : $form[$secondField];
			if ($value == $secondValue) {
				$valid = true;
			}
		}
		
		return $valid;
	}
	
	protected static function luhn($value) {
		$sum = 0;
		$odd = strlen($value) % 2;

		// Calculate sum of digits.
		for($i = 0; $i < strlen($value); $i++) {
			$sum += $odd ? $value[$i] : (($value[$i] * 2 > 9) ? $value[$i] * 2 - 9 : $value[$i] * 2);
			$odd = !$odd;
		}

		// Check validity.
		return ($sum % 10 == 0) ? true : false;
	}
}