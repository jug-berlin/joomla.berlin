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
class JFormFieldJSNRadio extends JFormField{

	public $type = 'JSNRadio';

	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected function getInput() {
		$html = array();
		
		$class = $this->element['class'] ? ' class="radio '.(string) $this->element['class'].'"' : ' class="radio"';
		$html[] = '<fieldset id="'.$this->id.'"'.$class.'>';
		
		// check System Cache Plugin
		$cacheSensitive = JSN_CACHESENSITIVE && (string) $this->element['cachesensitive']=='yes';
		if ($cacheSensitive) $this->value = 0;
		
		$options = $this->getOptions($cacheSensitive);
		
		// Build the radio field output.
		foreach ($options as $i => $option) {

			// Initialize some option attributes.
			$checked	= ((string) $option->value == (string) $this->value) ? ' checked="checked"' : '';
			$class		= !empty($option->class) ? ' class="'.$option->class.'"' : '';
			$disabled	= !empty($option->disable) ? ' disabled="disabled"' : '';

			// Initialize some JavaScript option attributes.
			$onclick	= !empty($option->onclick) ? ' onclick="'.$option->onclick.'"' : '';

			$html[] = '<input type="radio" id="'.$this->id.$i.'" name="'.$this->name.'"' .
					' value="'.htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8').'"'
					.$checked.$class.$onclick.$disabled.'/>';

			$html[] = '<label for="'.$this->id.$i.'"'.$class.'>'.JText::alt($option->text, preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)).'</label>';
		}

		// End the radio field output.
		$html[] = '</fieldset>';
		
		return implode($html);
	}

	protected function getOptions($cacheSensitive)
	{
		// Initialize variables.
		$options = array();

		foreach ($this->element->children() as $option) {

			// Only add <option /> elements.
			if ($option->getName() != 'option') {
				continue;
			}
			$disabled = ((string) $option['disabled']=='true');
			if ($cacheSensitive) $disabled = 'disabled="disabled"';

			// Create a new option object based on the <option /> element.
			$tmp = JHtml::_('select.option', (string) $option['value'], trim((string) $option), 'value', 'text', $disabled);

			// Set some option attributes.
			$tmp->class = (string) $option['class'];

			// Set some JavaScript option attributes.
			$tmp->onclick = (string) $option['onclick'];

			// Add the option object to the result set.
			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}	
}
