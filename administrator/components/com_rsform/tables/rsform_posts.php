<?php
/**
* @package RSForm! Pro
* @copyright (C) 2007-2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class TableRSForm_Posts extends JTable
{
	/**
	 * Primary Key
	 *
	 * @var int
	 */
	var $form_id = null;
	var $enabled = 0;
	var $method	 = 1;
	var $silent	 = 1;
	var $url	 = 'http://';
		
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function __construct(& $db)
	{
		parent::__construct('#__rsform_posts', 'form_id', $db);
	}
}