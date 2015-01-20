<?php
defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

/**
 * Output JSN Text section
 *
 * @package		
 * @subpackage	
 * @since		1.6
 */
class JFormFieldJSNText extends JFormField{

	public $type = 'JSNText';

	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected function getInput() {
		$html	= '';

		require_once dirname(dirname(__FILE__)). DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_utils.php';
		$jsnUtils     = JSNUtils::getInstance();
		
		// check System Cache Plugin
		$cacheSensitive = JSN_CACHESENSITIVE && (string) $this->element['cachesensitive']=='yes';
		
		$size		= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$maxLength	= $this->element['maxlength'] ? ' maxlength="'.(int) $this->element['maxlength'].'"' : '';
		$class		= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$readonly	= ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$disabled	= ((string) $this->element['disabled'] == 'true' || $cacheSensitive) ? ' disabled="disabled"' : '';
		
		//posttext for Parameter
		$posttext	= (isset($this->element['posttext'])) ? '<span class="jsn-posttext">'.$this->element['posttext'].'</span>' : '';
		
		if ($jsnUtils->isJoomla3()) {
			if (!empty($posttext))
			{
				$html  = '<div class="input-append">';
				$html .= '    <input type="text" name="'.$this->name.'" id="'.$this->id.'" value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'"' .  $size.$maxLength.$class.$readonly.$disabled.' />';
				$html .= '    <span class="add-on">' . $posttext . '</span>';
				$html .= '</div>';
			}
			else
			{
				$html = '<input type="text" name="'.$this->name.'" id="'.$this->id.'" value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'"' .  $size.$maxLength.$class.$readonly.$disabled.' />';
			}
		}
		else {
			$html		= '<input type="text" name="'.$this->name.'" id="'.$this->id.'"' .
					  ' value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'"' .
					  $size.$maxLength.$class.$readonly.$disabled.' />'.$posttext;
		}
		
		return	$html;
	}	
}
