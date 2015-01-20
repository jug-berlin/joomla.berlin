<?php
defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

/**
 * Cache folder field type
 *
 * @package		
 * @subpackage	
 * @since		1.6
 */
class JFormFieldJSNCacheFolder extends JFormField
{
	public $type = 'JSNCacheFolder';
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected function getInput() {
		
		require_once dirname(dirname(__FILE__)). DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_utils.php';
		$jsnUtils 	  		= JSNUtils::getInstance();
		$templateName		= $jsnUtils->getTemplateName();

		$doc 				= JFactory::getDocument();
		
		$attr 				= '';
		$jconfig 			= new JConfig();
		
		// Initialize some field attributes.
		$attr 		.= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$attr 		.= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr 		.= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$value		= htmlspecialchars( html_entity_decode( $this->value, ENT_QUOTES ), ENT_QUOTES );
		
		$root_folder	= str_replace('\\', '/', JPATH_ROOT ).'/';
		$html			= '<div class="jsn-cachefolder">';
		
		if (!$this->element['disabled']) {
			$doc->addScriptDeclaration("
				window.addEvent('domready', function(){ 
					var isJoomla3 = " . ($jsnUtils->isJoomla3() ? 'true' : 'false') . ";
					$('jform_params_cachefolder').addEvents({
						keyup: function()
						{
							$('jform_params_cachefolder').fireEvent('hideCheckLink');
						},
						keydown: function()
						{
							$('jform_params_cachefolder').fireEvent('hideCheckLink');
						},
						hideCheckLink: function()
						{
							if( $('jform_params_cachefolder').value == '') 
							{
								$('jsn-checkcache').set('styles', {'visibility': 'hidden'});
							} else {
								$('jsn-checkcache').set('styles', {'visibility': 'visible'});
							}
						}
					});	
					$('jsn-checkcache-action').addEvent('click', function() {
						actionUrl = '".JURI::root()."index.php';
						var resultMsg = new Element('font');
						var jsonRequest = new Request.JSON({url: actionUrl, onSuccess: function(jsonObj){
							$('jsn-checkcache-result').set('html' , '');
							if(jsonObj.isDir) {
								if (isJoomla3 == true) {
									$('jform_params_cachefolder').addClass('check-result-ok');
								}
								else {
									if(jsonObj.isWritable) {
										resultMsg.set({color: 'green', text: '".JText::_('FOLDER_EXISTS_WRITABLE')."'});
									} else {
										resultMsg.set({color: 'red', text: '".JText::_('FOLDER_NOT_WRITABLE')."'});
									}
								}
							} else {
								resultMsg.set({color: 'red', text: '".JText::_('FOLDER_NOT_EXISTS')."'});	
							}
							resultMsg.inject($('jsn-checkcache-result'));
						}}).get({'template': '".$templateName."', 'tmpl': 'jsn_runajax', 'cache_folder': '".$root_folder."' + $('jform_params_cachefolder').value , 'task': 'checkCacheFolder'});
					
					});
					
				});
			");
			
			if ($jsnUtils->isJoomla3()) {
				$html .= "<div class=\"input-append\">\r\n";
				$html .= "	<input value=\"{$value}\" type=\"text\" name=\"{$this->name}\" id=\"jform_params_cachefolder\" {$attr} />";
				$html .= "	<a id=\"jsn-checkcache-action\" class=\"btn\" href=\"javascript:void(0)\">" . JText::_('CHECK_CACHE_FOLDER') . "</a>";
				$html .= "</div>\r\n";
			}
			else {
				$html .= '<input value="'.$value.'" type="text" name="'.$this->name.'" id="jform_params_cachefolder" '.$attr.' /><span id="jsn-checkcache"><a id="jsn-checkcache-action" class="link-action" href="javascript:void(0)">'.JText::_('CHECK_CACHE_FOLDER').'</a><span id="jsn-checkcache-result"></span></span>';
			}
		} else {
			if ($jsnUtils->isJoomla3()) {
				$html .= "<div class=\"input-append\">\r\n";
				$html .= "	<input value=\"{$value}\" type=\"text\" name=\"{$this->name}\" id=\"jform_params_cachefolder\" {$attr} />";
				$html .= "	<span class=\"btn disabled\" href=\"javascript:void(0)\">" . JText::_('CHECK_CACHE_FOLDER') . "</span>";
				$html .= "</div>\r\n";
			}
			else {
				$html .= '<input value="'.$value.'" type="text" name="'.$this->name.'" id="jform_params_cachefolder" '.$attr.' /><span id="jsn-checkcache"><span class="link-disabled">'.JText::_('CHECK_CACHE_FOLDER').'</span></span>';
			}
		}
		$html  .= '</div>';
		
		return $html;
	}
} 