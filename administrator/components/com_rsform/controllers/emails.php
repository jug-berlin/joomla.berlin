<?php
/**
* @package RSForm! Pro
* @copyright (C) 2007-2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

class RSFormControllerEmails extends RSFormController
{
	function __construct()
	{
		parent::__construct();
		
		$this->registerTask('apply', 'save');
		
		$this->_db = JFactory::getDBO();
	}
	
	function save()
	{
		$model	= $this->getModel('forms');
		$row	= $model->saveemail();
		$type	= JRequest::getCmd('type','additional');
		
		if ($this->getTask() == 'apply')
			return $this->setRedirect('index.php?option=com_rsform&task=forms.emails&type='.$type.'&cid='.$row->id.'&formId='.$row->formId.'&tmpl=component&update=1');
		
		$document = JFactory::getDocument();
		$document->addScriptDeclaration('window.opener.updateemails('.$row->formId.',\''.JRequest::getCmd('type','additional').'\');window.close();');
	}
	
	function remove()
	{
		$db		= JFactory::getDBO();
		$cid	= JRequest::getInt('cid');
		$formId = JRequest::getInt('formId');
		$type	= JRequest::getCmd('type','additional');
		$view	= $type == 'additional' ? 'forms' : 'directory';
		
		if ($cid)
		{
			$db->setQuery("DELETE FROM #__rsform_emails WHERE id = ".$cid." ");
			$db->execute();
			$db->setQuery("DELETE FROM #__rsform_translations WHERE reference_id IN ('".$cid.".fromname','".$cid.".subject','".$cid.".message') ");
			$db->execute();
		}
		
		JRequest::setVar('view', $view);
		JRequest::setVar('layout', 'edit_emails');
		JRequest::setVar('tmpl', 'component');
		JRequest::setVar('formId', $formId);
		JRequest::setVar('type', $type);
		
		parent::display();
		jexit();
	}
	
	function update()
	{
		$formId = JRequest::getInt('formId');
		$view	= JRequest::getCmd('type','additional') == 'additional' ? 'forms' : 'directory';
		
		JRequest::setVar('view', $view);
		JRequest::setVar('layout', 'edit_emails');
		JRequest::setVar('tmpl', 'component');
		JRequest::setVar('formId', $formId);
		
		parent::display();
		jexit();
	}
}