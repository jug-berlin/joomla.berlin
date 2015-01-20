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

abstract class JLibTutorialComponent {
	
	function &getInstance($class = "") {
		static $instance;
		if(empty($instance) && $class != "") 
			$instance = new $class();
		return $instance;
	}
	
	protected function __construct( ) {
		
	}
	
	public function start( $tut ) {
		
	}
	
	public function get( $tut ) {
		
	}
	
	public function getTutorials() {
		
	}
	
}
?>