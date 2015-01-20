<?php
/**
* @package RSForm! Pro
* @copyright (C) 2007-2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class RSFormViewBackuprestore extends JViewLegacy
{
	public function display($tpl = null) {
		$this->addToolbar();
		
		// tabs
		$this->tabs		 = $this->get('RSTabs');
		// fields
		$this->field	 = $this->get('RSFieldset');
		$this->sidebar 	 = $this->get('SideBar');
		
		$this->writable = $this->get('isWritable');
		$this->forms	= $this->get('forms');
		
		parent::display($tpl);
	}
	
	protected function addToolBar() {
		// set title
		JToolBarHelper::title('RSForm! Pro', 'rsform');
		
		$backupIcon  = RSFormProHelper::isJ('3.0') ? 'download' : 'archive';
		$restoreIcon = RSFormProHelper::isJ('3.0') ? 'upload' : 'unarchive';
		
		JToolBarHelper::custom('backup.download', $backupIcon, $backupIcon, JText::_('RSFP_BACKUP_GENERATE'), false);
		JToolBarHelper::custom('restore.process', $restoreIcon, $restoreIcon, JText::_('RSFP_RESTORE'), false);
		
		require_once JPATH_COMPONENT.'/helpers/toolbar.php';
		RSFormProToolbarHelper::addToolbar('backuprestore');
	}
}