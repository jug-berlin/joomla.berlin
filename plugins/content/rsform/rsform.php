<?php
/**
* @version 1.4.0
* @package RSForm! Pro 1.4.0
* @copyright (C) 2007-2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class plgContentRSForm extends JPlugin
{
	// Check if RSForm! Pro can be loaded
	protected function canRun() {
		if (class_exists('RSFormProHelper')) {
			return true;
		}
		
		$helper = JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/rsform.php';
		if (file_exists($helper)) {
			require_once($helper);
			return true;
		}
		
		return false;
	}
	
	// Joomla! Triggers - onContentBeforeDisplay()
	public function onContentBeforeDisplay($context, &$row, &$params, $page=0) {
		// Don't run this plugin when the content is being indexed
		if ($context == 'com_finder.indexer') {
			return true;
		}
		
		if ($this->canRun()) {
			if (is_object($row) && isset($row->text)) {
				$this->_addForm($row->text);
			} elseif (is_string($row)) {
				$this->_addForm($row);
			}
		}
	}
	
	// Joomla! Triggers - onContentPrepare()
	public function onContentPrepare($context, &$row, &$params, $page=0) {
		// Don't run this plugin when the content is being indexed
		if ($context == 'com_finder.indexer') {
			return true;
		}
		
		if ($this->canRun()) {
			if (is_object($row) && isset($row->text)) {
				$this->_addForm($row->text);
			} elseif (is_string($row)) {
				$this->_addForm($row);
			}
		}
	}
	
	// Syntax replacement function
	protected function _addForm(&$text) {		
		// Performance check
		if (strpos($text, '{rsform ') !== false) {
			// Expression to search for
			$pattern = '#\{rsform ([0-9]+)\}#i';
			// Found matches
			if (preg_match_all($pattern, $text, $matches)) {
				// No replacement when we're not dealing with HTML
				if (JFactory::getDocument()->getType() != 'html') {
					$text = preg_replace($pattern, '', $text);
					return true;
				}
				
				// Load language
				JFactory::getLanguage()->load('com_rsform', JPATH_SITE);
				
				// Disable caching
				$cache = JFactory::getCache('com_content');
				$cache->setCaching(false);
				
				// Start replacing
				foreach ($matches[0] as $i => $match) {
					$formId = $matches[1][$i];
					$text 	= str_replace($matches[0][$i], RSFormProHelper::displayForm($formId, true), $text);
				}
			}
		}
	}
}