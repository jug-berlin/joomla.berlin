<?php
defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

/**
 * Position Mapping field type
 *
 * @package		
 * @subpackage	
 * @since		1.6
 */
class JFormFieldJSNPositionMapping extends JFormField
{
	public $type = 'JSNPositionMapping';
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected function getInput() {
		
		require_once dirname(dirname(__FILE__)). DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_utils.php';
		$jsnUtils 	  		= JSNUtils::getInstance();
		$doc 				= JFactory::getDocument();
		$templateName		= $jsnUtils->getTemplateName();
		$templateAbsPath 	= JPATH_ROOT . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $templateName;
		$attr 				= ($this->element['disabled'] ? 'disabled="'.$this->element['disabled'].'"' : '');
		$default 			= ($this->element['default'] ? $this->element['default'] : '');
		$data 				= $jsnUtils->getPositions($templateName);

		// check System Cache Plugin
		$cacheSensitive = JSN_CACHESENSITIVE && (string) $this->element['cachesensitive']=='yes';
		if ($cacheSensitive) $attr = 'disabled="disabled"';
	
		$html 				= '<div class="jsn-positionmapping">';

		if($this->value) {
			$html 	.= $jsnUtils->renderPositionComboBox($this->value, $data['desktop'], 'Select position', 'jform[params]['.$this->element['name'].']', $attr);
		} else {
			$html 	.= $jsnUtils->renderPositionComboBox($default, $data['desktop'], 'Select position', 'jform[params]['.$this->element['name'].']', $attr);
		}

		$html 	.= '</div>';
		
		return $html;		
	}
} 