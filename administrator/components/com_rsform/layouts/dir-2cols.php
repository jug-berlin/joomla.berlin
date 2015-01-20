<?php
/**
* @package RSForm! Pro
* @copyright (C) 2007-2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$out = '<div class="rsform-table" id="rsform-table3">'."\n";

// Organize fields into titles, images and other.
$titles = array();
$others = array();

foreach ($fields as $field) {
	if ($field->indetails) {
		if ($field->componentId < 0 && isset($headers[$field->componentId])) {
			$caption = JText::_('RSFP_'.$headers[$field->componentId]);
			$value	 = $this->getStaticPlaceholder($headers[$field->componentId]);
		} else {
			$caption = '{'.$field->FieldName.':caption}';
			$value 	 = '{'.$field->FieldName.':value}';
		}
		
		// Add to titles
		if (count($titles) < 3) {
			$titles[] = $value;
			continue;
		}
		
		// Is it an upload field?		
		if (in_array($field->FieldName, $imagefields)) {
			continue;
		}
		
		// No more titles, add to other.
		$others[] = (object) array(
			'caption' => $caption,
			'value'	  => $value
		);
	}
}

if ($titles) {
	if (isset($titles[0])) {
		$out .= "\t".'<p class="rsform-main-title rsform-title">'.$titles[0].'</p>'."\n";
	}
	if (isset($titles[1])) {
		$out .= "\t".'<p class="rsform-big-subtitle rsform-title">'.$titles[1].'</p>'."\n";
	}
	if (isset($titles[2])) {
		$out .= "\t".'<p class="rsform-small-subtitle rsform-title">'.$titles[2].'</p>'."\n";
	}
}

if (!empty($imagefields)) {
	$out .= "\t".'<ul class="rsform-gallery">'."\n";
	
	foreach ($imagefields as $image) {
		$out .= "\t\t".'{if {'.$image.':value}}<li><a href="javascript:void(0)" class="modal" rel="{handler: \'clone\'}"><img src="{'.$image.':path}" alt="" /></a></li>{/if}'."\n";
	}
	
	$out .= "\t".'</ul>'."\n";
}

if (!empty($others)) {
	$out .= "\t".'<div class="rsfp-table">'."\n";
	
	foreach ($others as $other) {
		$out .= "\t\t".'<div class="rsform-table-row">'."\n";
		$out .= "\t\t\t".'<div class="rsform-left-col">'.$other->caption.'</div>'."\n";
		$out .= "\t\t\t".'<div class="rsform-right-col">'.$other->value.'</div>'."\n";
		$out .= "\t\t".'</div>'."\n";
	}
	
	$out .= "\t".'</div>'."\n";
}

$out .= '</div>';

if ($out != $this->_directory->ViewLayout && $this->_directory->formId) {
	// Clean it
	// Update the layout
	$db = JFactory::getDBO();
	$db->setQuery("UPDATE #__rsform_directory SET ViewLayout='".$db->escape($out)."' WHERE formId=".$this->_directory->formId);
	$db->execute();
}
	
return $out;