<?php
/**
* @package RSForm! Pro
* @copyright (C) 2007-2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class RSFormViewSubmissions extends JViewLegacy
{
	public function display($tpl = null) {
		$this->params 	= JFactory::getApplication()->getParams('com_rsform');
		$this->template = $this->get('template');
		
		if ($this->getLayout() == 'default') {
			$this->filter 		= $this->get('filter');
			$this->itemid 		= $this->get('Itemid');
			$this->pagination 	= $this->get('pagination');
		} else {
			// Add pathway
			JFactory::getApplication()->getPathway()->addItem(JText::_('RSFP_VIEW_SUBMISSION'), '');
		}
		
		parent::display($tpl);
	}
}