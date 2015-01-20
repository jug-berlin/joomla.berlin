<?php
/**
* @package RSForm! Pro
* @copyright (C) 2007-2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSFormViewUpdates extends JViewLegacy
{
	protected $hash;
	protected $jversion;
	protected $version;
	protected $sidebar;
	
	public function display($tpl = null) {
		$this->addToolBar();
		
		$this->hash 	= $this->get('hash');
		$this->jversion = $this->get('joomlaVersion');
		$this->version = (string) new RSFormProVersion();
		
		$this->sidebar	= $this->get('SideBar');
		
		parent::display($tpl);
	}
	
	protected function addToolbar() {
		// set title
		JToolBarHelper::title('RSForm! Pro', 'rsform');
		
		require_once JPATH_COMPONENT.'/helpers/toolbar.php';
		RSFormProToolbarHelper::addToolbar('updates');
	}
}