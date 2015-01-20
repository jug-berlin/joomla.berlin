<?php
defined('_JEXEC') or die;

jimport('joomla.document.html.html');

class JDocumentHTML2 extends JDocumentHTML
{
	/**
	 * Set the html document head data
	 *
	 * @param	array	$data	The document head data in array form
	 */
	public function setHeadData($data)
	{
		if (empty($data) || !is_array($data)) {
			return;
		}

		$this->title		= (isset($data['title']) ) ? $data['title'] : $this->title;
		$this->description	= (isset($data['description']) ) ? $data['description'] : $this->description;
		$this->link			= (isset($data['link']) ) ? $data['link'] : $this->link;
		$this->_metaTags	= (isset($data['metaTags']) ) ? $data['metaTags'] : $this->_metaTags;
		$this->_links		= (isset($data['links']) ) ? $data['links'] : $this->_links;
		$this->_styleSheets	= (isset($data['styleSheets']) ) ? $data['styleSheets'] : $this->_styleSheets;
		$this->_style		= (isset($data['style']) ) ? $data['style'] : $this->_style;
		$this->_scripts		= (isset($data['scripts']) ) ? $data['scripts'] : $this->_scripts;
		$this->_script		= (isset($data['script']) ) ? $data['script'] : $this->_script;
		$this->_custom		= (isset($data['custom']) ) ? $data['custom'] : $this->_custom;
	}
}