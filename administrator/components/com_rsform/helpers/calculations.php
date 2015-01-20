<?php
/**
* @package RSForm! Pro
* @copyright (C) 2007-2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

class RSFormProCalculations
{
	protected static function escape($string) {
		return addslashes($string);
	}
	
	public static function expression($calculation, $formId) {
		$return		= '';
		$pattern	= '#{(.*?):value}#is';
		$expression	= $calculation->expression;
		$filter		= JFilterInput::getInstance();
		
		preg_match_all($pattern,$calculation->expression,$matches);
		if ($matches) {
			foreach ($matches[0] as $i => $match) {
				$field	 = $filter->clean($matches[1][$i]."_".$formId,'cmd');
				$return .= "\t total".$field." = 0;\n";
				$return .= "\t values".$field." = rsfp_getValue(".$formId.", '".$matches[1][$i]."');\n";
				$return .= "\t if (typeof values".$field." == 'object') { \n";
				$return .= "\t\t for(i=0;i<values".$field.".length;i++) {\n";
				$return .= "\t\t\t thevalue = values".$field."[i]; \n";
				$return .= "\t\t\t if (isset(RSFormProPrices['".$formId."_".$matches[1][$i]."'])) { \n";
				$return .= "\t\t\t\t total".$field." += isset(RSFormProPrices['".$formId."_".$matches[1][$i]."'][thevalue]) ? parseFloat(RSFormProPrices['".$formId."_".$matches[1][$i]."'][thevalue]) : 0; \n";
				$return .= "\t\t\t }\n";
				$return .= "\t\t }\n";
				$return .= "\t } else { \n";
				$return .= "\t\t total".$field." += (values".$field.".indexOf(',') == -1 && values".$field.".indexOf('.') == -1) ? parseFloat(values".$field.") :  parseFloat(rsfp_toNumber(values".$field.",'".self::escape(RSFormProHelper::getConfig('calculations.decimal'))."','".self::escape(RSFormProHelper::getConfig('calculations.thousands'))."')); \n";
				$return .= "\t } \n";
				$return .= "\t total".$field." = !isNaN(total".$field.") ? total".$field." : 0; \n\n";
				
				$expression = str_replace($match,'total'.$field,$expression);
			}
			
			$return .= "\n\t grandTotal".$calculation->id.$formId." = ".$expression.";\n";
			$return .= "\t document.getElementById('".$calculation->total."').value = number_format(grandTotal".$calculation->id.$formId.",".(int) RSFormProHelper::getConfig('calculations.nodecimals').",'".self::escape(RSFormProHelper::getConfig('calculations.decimal'))."','".self::escape(RSFormProHelper::getConfig('calculations.thousands'))."'); \n\n";
		}
		
		return $return;
	}
	
	public static function getFields($calculations, $formId) {
		$fields		= array();
		$return		= "\n".'var rsfpCalculationFields'.$formId.' = [];';
		$pattern	= '#{(.*?):value}#is';
		
		if (!empty($calculations)) {
			foreach ($calculations as $calculation) {
				if (preg_match_all($pattern,$calculation->expression,$matches)) {
					foreach ($matches[1] as $i => $match) {
						$fields[] = $match;
					}
				}
			}
			
			if ($fields = array_unique($fields)) {
				foreach ($fields as $field) {
					$return .= "\n".'rsfpCalculationFields'.$formId.'["'.$formId.'_'.addslashes($field).'"] = {};';
				}
			}
		}
		
		return $return;
	}
}