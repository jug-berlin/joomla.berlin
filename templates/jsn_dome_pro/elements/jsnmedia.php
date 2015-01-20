<?php
/**
 * @author    JoomlaShine.com http://www.joomlashine.com
 * @copyright Copyright (C) 2008 - 2011 JoomlaShine.com. All rights reserved.
 * @license   GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 * @version   $Id: jsnmedia.php 17002 2012-10-13 09:39:19Z tuyetvt $
 */
defined('JPATH_BASE') or die;
jimport('joomla.form.formfield');
class JFormFieldJSNMedia extends JFormField
{
	protected $type = 'JSNMedia';

	protected static $initialised = false;

	protected function getInput()
	{
		require_once dirname(dirname(__FILE__)). DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_utils.php';
		$jsnUtils     = JSNUtils::getInstance();
		$templateName = $jsnUtils->getTemplateName();

		/* Form the internal path to default template logo */
		$defaultLogoPath = '';
		if (strpos($this->name, 'logoPath') !== false)
		{
			$defaultLogoPath = 'templates/' . $templateName . '/images/logo.png';
		}

		if ($this->value == '')
		{
			$this->value = $defaultLogoPath;
		}

		$assetField	= $this->element['asset_field'] ? (string) $this->element['asset_field'] : 'asset_id';
		$authorField= $this->element['created_by_field'] ? (string) $this->element['created_by_field'] : 'created_by';
		$asset		= $this->form->getValue($assetField) ? $this->form->getValue($assetField) : (string) $this->element['asset_id'];
		$disabled  = ((string) $this->element['disabled'] == 'true') ? true : false;

		// check System Cache Plugin
		$cacheSensitive = JSN_CACHESENSITIVE && (string) $this->element['cachesensitive']=='yes';
		if ($cacheSensitive) $disabled = true;

		if ($asset == "") {
			 $asset = JRequest::getCmd('option');
		}

		$link = (string) $this->element['link'];
		if (!self::$initialised) {

			// Build the script.
			$script = array();
			$script[] = '	function jInsertFieldValue(value, id) {';
			$script[] = '		var old_id = document.id(id).value;';
			$script[] = '		if (old_id != id) {';
			$script[] = '			var elem = document.id(id)';
			$script[] = '			elem.value = value;';
			$script[] = '			elem.fireEvent("change");';
			$script[] = '		}';
			$script[] = '	}';

			// Add the script to the document head.
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

			self::$initialised = true;
		}

		// Initialize variables.
		$html = array();
		$attr = '';

		$directory = (string)$this->element['directory'];
		if ($this->value == $defaultLogoPath)
		{
			$folder = '';
		}
		elseif (file_exists(JPATH_ROOT . '/' . $this->value)) {
			$folder = explode ('/',$this->value);
			array_shift($folder);
			array_pop($folder);
			$folder = implode('/',$folder);
		}
		elseif (file_exists(JPATH_ROOT . '/images/' . $directory)) {
			$folder = $directory;
		}
		else {
			$folder='';
		}

		// The button.
		$unactived  = ($disabled) ? ' jsn-disabled-button' : '';
		$ref 		= ($disabled) ? '' : ' rel="{handler: \'iframe\', size: {x: 800, y: 500}}"';
		$href 		= ($disabled) ? ' href="javascript:void(0);"' : ' href="'.($this->element['readonly'] ? '' : ($link ? $link : 'index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;asset='.$asset.'&amp;author='.$this->form->getValue($authorField)) . '&amp;fieldid='.$this->id.'&amp;folder='.$folder).'"';
		$modal 		= ($disabled) ? '' : ' class="jsn-modal"';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$attr .= ($disabled) ? ' disabled="disabled"' : '';
		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';

		if ($jsnUtils->isJoomla3()) {
			$modal 		= ($disabled) ? '' : ' class="jsn-modal btn"';

			$html[] = '<div class="input-append">';
			$html[] = '<input type="text" name="'.$this->name.'" id="'.$this->id.'" value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" readonly="readonly"'.$attr.' />';
			$html[] = '<a' . $modal . ' title="' . JText::_('JLIB_FORM_BUTTON_SELECT') . '"' . $href . $ref . '>...</a>';
			
			if ($disabled) {
				$html[] = '<span title="'.JText::_('LOGO_BUTTON_DEFAULT').'" class="btn disabled">' . JText::_('LOGO_BUTTON_DEFAULT') . '</span>';
			}
			else {
				$html[] = '<a title="'.JText::_('LOGO_BUTTON_DEFAULT').'" href="javascript:void(0);" class="btn" onclick="document.getElementById(\''.$this->id.'\').value=\'' . htmlspecialchars($defaultLogoPath, ENT_COMPAT, 'UTF-8') . '\';">' . JText::_('LOGO_BUTTON_DEFAULT') . '</a>';
			}

			$html[] = '</div>';
		}
		else {
			// The text field.
			$html[] = '<div class="fltlft">';
			$html[] = '	<input type="text" name="'.$this->name.'" id="'.$this->id.'"' .
						' value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'"' .
						' readonly="readonly"'.$attr.' />';
			$html[] = '</div>';

			$html[] = '<div class="button2-left'.$unactived.'">';
			$html[] = '	<div class="blank">';
			$html[] = '		<a'.$modal.' title="'.JText::_('JLIB_FORM_BUTTON_SELECT').'"' .
						 $href.
						$ref.'>';
			$html[] = '			'.JText::_('JLIB_FORM_BUTTON_SELECT').'</a>';
			$html[] = '	</div>';
			$html[] = '</div>';

			$html[] = '<div class="button2-left'.$unactived.'">';
			$html[] = '	<div class="blank">';
			$html[] = '		<a title="'.JText::_('LOGO_BUTTON_DEFAULT').'"' .
						' href="javascript:void(0);"'.
						' onclick="document.getElementById(\''.$this->id.'\').value=\'' . htmlspecialchars($defaultLogoPath, ENT_COMPAT, 'UTF-8') . '\';">';
			$html[] = '			'.JText::_('LOGO_BUTTON_DEFAULT').'</a>';
			$html[] = '	</div>';
			$html[] = '</div>';
		}

		return implode("\n", $html);
	}
}
