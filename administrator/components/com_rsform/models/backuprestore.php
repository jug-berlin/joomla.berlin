<?php
/**
* @package RSForm! Pro
* @copyright (C) 2007-2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class RSFormModelBackupRestore extends JModelLegacy
{
	var $_data = null;
	var $_total = 0;
	var $_query = '';
	var $_pagination = null;
	var $_db = null;
	
	function __construct()
	{
		parent::__construct();
		$this->_db = JFactory::getDBO();
		$this->_query = $this->_buildQuery();
	}
	
	function _buildQuery()
	{
		$query  = "SELECT FormId, FormTitle, FormName FROM #__rsform_forms WHERE 1";
		$query .= " ORDER BY `".$this->getSortColumn()."` ".$this->getSortOrder();
		
		return $query;
	}
	
	function getForms()
	{		
		if (empty($this->_data))
			$this->_data = $this->_getList($this->_query);
		
		foreach ($this->_data as $i => $row)
		{
			$this->_db->setQuery("SELECT COUNT(`SubmissionId`) cnt FROM #__rsform_submissions WHERE FormId='".$row->FormId."'");
			$row->_allSubmissions = $this->_db->loadResult();
		}
		
		return $this->_data;
	}
	
	function getSortColumn()
	{
		$mainframe = JFactory::getApplication();
		return $mainframe->getUserStateFromRequest('com_rsform.forms.filter_order', 'filter_order', 'FormId', 'word');
	}
	
	function getSortOrder()
	{
		$mainframe = JFactory::getApplication();
		return $mainframe->getUserStateFromRequest('com_rsform.forms.filter_order_Dir', 'filter_order_Dir', 'ASC', 'word');
	}
	
	function getIsWritable()
	{
		return is_writable(JPATH_SITE.'/media');
	}
	
	public function getRSFieldset() {
		require_once JPATH_COMPONENT.'/helpers/adapters/fieldset.php';
		
		$fieldset = new RSFieldset();
		return $fieldset;
	}
	
	public function getRSTabs() {
		require_once JPATH_COMPONENT.'/helpers/adapters/tabs.php';
		
		$tabs = new RSTabs('com-rsform-configuration');
		return $tabs;
	}
	
	public function getSideBar() {
		require_once JPATH_COMPONENT.'/helpers/toolbar.php';

		return RSFormProToolbarHelper::render();
	}
}