<?php
/**
 * @package Joomess.de Library for the extensions of Johannes Meßmer
 * @projectsite www.joomess.de
 * @author Johannes Meßmer
 * @copyright (C) 2012 Johannes Me�mer
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once(JPATH_SITE.'/'.'plugins'.'/'.'system'.'/'.'joomessLibrary'.'/'.'joomessLibrary.php');

require_once(JPATH_SITE.'/'.'plugins'.'/'.'system'.'/'.'joomessLibrary'.'/'.'lib'.'/'.'tutorials'.'/'.'component.php');
require_once(JPATH_SITE.'/'.'plugins'.'/'.'system'.'/'.'joomessLibrary'.'/'.'lib'.'/'.'tutorials'.'/'.'tutorial.php');

class JLibTutorialController {

	public static function &getInstance() {
		static $instance;
		if(empty($instance)) 
			$instance = new JLibTutorialController();
		return $instance;
	}
	
	private function __construct() {
		
	}
	
	public function get( $component ) {
		//Generate path of controller
		$path = JPATH_SITE.'/'.'components'.'/'.'com_'.strtolower($component).'/'.'joomessLibrary_tutorials.php';
		$load = @require_once( $path );
		
		if($load) {
			$class = 'JLibTutorialComponent' . $component;
			if(class_exists( $class )) {
				return $class::getInstance( $class );
			}
		}
	}
	
}
?>