<?php
/**
 * @author    JoomlaShine.com http://www.joomlashine.com
 * @copyright Copyright (C) 2008 - 2011 JoomlaShine.com. All rights reserved.
 * @license   GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted index access' );
jimport('joomla.filesystem.file');

class JSNCompression
{
	protected $file_list 			= array();
	protected $compress_options 	= array();
	protected $processed_file_list	= array();
	protected $metadata_filename 	= '';
	protected $compress_filename 	= '';
	protected $type 				= '';
	protected $param_file_path		= '';
    protected $active_profile		= '';

	function __construct($type, $file_list, $compress_options)
	{
		$this->__set('file_list', 			$file_list);
		$this->__set('compress_options',	$compress_options);
		$this->__set('type', 				$type);
		$this->__set('metadata_filename', 	$this->compress_options['cache_path']. DIRECTORY_SEPARATOR .$this->compress_options['template_name'].'_'.$type.'_metadata.json');
		$this->__set('compress_filename', 	$this->compress_options['cache_path']. DIRECTORY_SEPARATOR .$this->compress_options['template_name'].'_'.$type.'_'.$this->compress_options[$type.'_active_profile'].'.'.$type);
		$this->__set('param_file_path', 	$this->compress_options['template_abs_path'].'/params.ini');
		$this->__set('active_profile', 		$this->compress_options[$type.'_active_profile']);

		foreach ($this->file_list as $key=>$value)
		{
			$this->processed_file_list[] = $value['file_abs_path']. DIRECTORY_SEPARATOR .$value['file_name'];
		}
	}
	/**
	 * Set method
	 * @param $key
	 * @param $val
	 */
	function __set($key, $val)
	{
		$this->$key = $val;
	}
	/**
	 * Check compression cache file
	 *
	 * @return boolean True will trigger compression process
	 */
	function checkCache()
	{
		// No compression file or meta data file
		if (!file_exists($this->compress_filename))
		{
			return true;
		}
		return false;
	}
	/**
	 * Compress CSS/JS file
	 */
	function process()
	{
		$compress_content 	= '';
		$css_content		= '';
		$gzip_content		= '';
		$need_update 		= false;
		$character_escape   = array("<?php", " ?>");
		switch($this->type)
		{
			case 'css':
				require_once 'compression'. DIRECTORY_SEPARATOR .'jsn_css_compression.php';
				$compression_engine = new JSNCssCompression();
				$uri = JURI::root(true);
				foreach($this->file_list as $key => $value)
				{
					if (!(file_exists($this->compress_filename) && @filemtime($value['file_abs_path']. DIRECTORY_SEPARATOR .$value['file_name']) < @filemtime($this->compress_filename)))
					{
						 $need_update = true;
						 break;
					}
				}

				if ($need_update)
				{
					foreach($this->file_list as $key => $value)
					{
						$css_content 		 = file_get_contents($value['file_abs_path']. DIRECTORY_SEPARATOR .$value['file_name']);
						$last_sep 			 = strrpos($key, '/');
						$file_url			 = substr($key, 0, $last_sep);
						$compress_content 	.= $compression_engine->compressCSS($css_content, $file_url, $this->compress_options['template_path']);
					}

					// @import rules must proceed any other style, so we move those to the top.
				    $regexp = '/@import[^;]+;/i';
				    preg_match_all($regexp, $compress_content, $matches);
				    $compress_content = preg_replace($regexp, '', $compress_content);
				    $compress_content = implode('', $matches[0]) . $compress_content;
				}
				break;
			case 'js':
				foreach ($this->file_list as $key => $value)
				{
					if (!(file_exists($this->compress_filename) && @filemtime($value['file_abs_path']. DIRECTORY_SEPARATOR .$value['file_name']) < @filemtime($this->compress_filename)))
					{
						 $need_update = true;
						 break;
					}
				}

				if ($need_update)
				{
					foreach ($this->file_list as $key => $value)
					{
						if ($value['file_name'])
						{
							$compress_content .= file_get_contents($value['file_abs_path']. DIRECTORY_SEPARATOR .$value['file_name']) . ';' ;
							$compress_content .= "\r\n"; // Add line break to prevent code becoming comment from last file
						}
					}
				}
				break;
		}

		if ($need_update)
		{
			$gzip_content .= '/*'.$this->active_profile.'*/';
			$compress_content = $gzip_content.str_replace($character_escape, '//', $compress_content); //str_replace($character_escape, '//', $compress_content);
			JFile::write($this->compress_filename, $compress_content);
		}
	}

	function executeCompress()
	{
		if (isset($this->type))
		{
			$this->process();
		}
	}
}
