<?php
/**
* @package RSForm! Pro
* @copyright (C) 2007-2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');

class RSFormViewRsform extends JViewLegacy
{
	function display( $tpl = null )
	{
		$this->params	= $this->get('Params');
		$this->formId 	= $this->get('FormId');
		
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		
		$title = $this->params->get('page_title', '');
		if (empty($title)) {
			$title = JFactory::getConfig()->get('sitename');
		}
		elseif (JFactory::getConfig()->get('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', JFactory::getConfig()->get('sitename'), $title);
		}
		elseif (JFactory::getConfig()->get('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, JFactory::getConfig()->get('sitename'));
		}
		
		$doc->setTitle($title);
		
		parent::display($tpl);
	}
}