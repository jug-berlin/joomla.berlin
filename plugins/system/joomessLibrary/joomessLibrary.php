<?php
/**
 * @package Joomess.de Library for the extensions of Johannes Meßmer
 * @projectsite www.joomess.de
 * @author Johannes Meßmer
 * @copyright (C) 2012 Johannes Me�mer
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once(JPATH_SITE.'/'.'plugins'.'/'.'system'.'/'.'joomessLibrary'.'/'.'lib'.'/'.'jsmin.php');
require_once(JPATH_SITE.'/'.'plugins'.'/'.'system'.'/'.'joomessLibrary'.'/'.'lib'.'/'.'cssmin.php');

jimport( 'joomla.filesystem.folder' );
jimport( 'joomla.filesystem.file' );
jimport( 'joomla.plugin.helper' );

class plgSystemJoomessLibrary extends JPlugin {
 
	public function plgSystemJoomessLibrary(&$subject, $config = array()) 
	{
		parent::__construct($subject, $config);
	}
	
	public function onAfterInitialise() {
		$lib =& joomessLibrary::getInstance();
		
		//Set new Cache-Value
		$app =& JFactory::getApplication();
		$user =& JFactory::getUser();
		if($app->isAdmin() && !$user->guest) {
			if($set = JRequest::getInt("joomessLibraryCacheValue", false)) {
				if($set == -2) $lib->cleanAll();
				elseif($set == -3) { //Disable Module
					$db = JFactory::getDbo();
					$sql = 'UPDATE `#__modules` SET `published` = 0 WHERE `module` = "mod_joomessLibrary_status" ';
					$db->setQuery($sql);
					$db->query();
				} else $lib->setParam("cache_enabled", time() + $set, true);
			}
		}
	}
	
	public function onAfterRender()
	{
		$lib =& joomessLibrary::getInstance();
		
		// Use a 20% chance of running; this allows multiple concurrent page
		$random = rand(1, 5);
		if($random != 3) return;
		
		// Check if you need to clear Cache - all 
		$last = $lib->getParam("last_run", 0);
		$now = time();
		if(abs($now-$last) < 10800) return;
		
		// Update last run status
		$lib->setParam('last_run', $now, true);
		
		//Clear Cache
		$lib->clean();
	}
	
	public function onBeforeCompileHead() {
		$lib =& joomessLibrary::getInstance();
		$lib->render();
	}
}

class joomessLibrary {
	
	public static $_JLIB_VERSION 		= "1.04";
	public static $_JLIB_EXTENSION_ID 	= 6;
	
	private $doc, $cache, $db;
	
	private $js, $jsCode, $css;
	
	private $jquery, $plugins, $ready;
	
	private $nameSpace;
	
	public $cacheFolder;
	
	private $pars, $changedParams;
	
	private $enabled;
	
	private $langLoaded;
	
	private $headScripts, $_headStyleSheets, $_headScript;
	
	private function __construct() {
		$this->doc 				=& JFactory::getDocument();
		$this->db 				=& JFactory::getDbo();
		
		$this->js 				= array();
		$this->jsCode 			= array();
		$this->css 				= array();
		
		$this->jquery 			= false;
		$this->plugins 			= array();
		$this->ready 			= array();
		
		$this->langLoaded 		= array();
		
		$this->nameSpace 		= "JMQuery";
		$this->cacheFolder 		= "cache/joomessLibrary";
		
		$this->_headScripts 	= array();
		$this->_headStyleSheets = array();
		$this->_headScript 		= array();
		
		if(!$this->getParam("databaseInstalled", false)) {
			$sql = "CREATE TABLE IF NOT EXISTS `#__joomess_library` (
					  `id` varchar(36) NOT NULL,
					  `files` text NOT NULL,
					  `created_date` datetime NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
			$this->db->setQuery($sql);
			$this->db->query();
			$this->setParam("databaseInstalled", true);
		}
	}
	
	public static function &getInstance() {
		static $instance;
		if(empty($instance)) {
			$instance = new joomessLibrary();
		}
		return $instance;
	}
	
	private $tutorialInstance;
	public function getTutorialController() {
		if(!isset($tutorialInstance)) {
			require_once(JPATH_SITE.'/'.'plugins'.'/'.'system'.'/'.'joomessLibrary'.'/'.'lib'.'/'.'tutorials'.'/'.'controller.php');
			$tutorialInstance = JLibTutorialController::getInstance();
		}
		return $tutorialInstance;
	}
	
	public function active() {
		$this->loadParams();
		return $this->enabled;
	}
	
	private function loadParams() {
		if(!isset($this->pars)) {
			jimport('joomla.html.parameter');
			$plugin = JPluginHelper::getPlugin('system', 'joomessLibrary');
			$this->enabled = (!$plugin) ? false : true;
			if($this->enabled) {
				switch($this->getJoomlaVersion()) {
					case self::jVersion30:
						$this->pars = new JRegistry();
						$this->pars->loadString($plugin->params);
					break;
					default:
						$this->pars = new JParameter($plugin->params);
				}
			}
				
			$this->changedParams = false;
		}
	}
	
	function getParam($key, $default) {
		$this->loadParams();
		if(!$this->enabled) return $default;
		return ($this->getJoomlaVersion() == self::jVersion30) 
					? $this->pars->get($key, $default)
					: $this->pars->getValue($key, $default);
	}
	
	function setParam($key, $value, $directSave = false) {
		$this->loadParams();
		if(!$this->enabled) return false;
		if($this->getJoomlaVersion() == self::jVersion30)
			$this->pars->set($key, $value);
		else
			$this->pars->setValue($key, $value);
		if($directSave) $this->saveParams();
		else $this->changedParams = true;
	}
	
	function saveParams() {
		if(!$this->enabled) return false;
		if( version_compare(JVERSION,'1.6.0','ge') ) {
			// Joomla! 1.6
			$data = $this->pars->toString('JSON');
			$this->db->setQuery('UPDATE `#__extensions` SET `params` = '.$this->db->Quote($data).' WHERE '.
					"`element` = ".$this->db->quote('joomessLibrary')." AND `type` = 'plugin'");
		} else {
			// Joomla! 1.5
			$data = $this->pars->toString('INI');
			$this->db->setQuery('UPDATE `#__plugins` SET `params` = '.$this->db->Quote($data, false).' WHERE '.
					"`element` = ".$this->db->quote('joomessLibrary', false)." AND `folder` = 'system'");
		}
		
		// If a DB error occurs, return null
		if(version_compare(JVERSION, '1.6.0', 'ge')) {
			try {
				$this->db->query();
			} catch (Exception $e) {
				return false;
			}
		} else {
			$this->db->query();
			if($this->db->getErrorNum()) return false;
		}
		
		unset($this->params);
		
		return true;
	}
	
	function jQuery($plugins = array()) {
		$this->jquery = true;		
		
		foreach ($plugins AS $plugin) $this->plugin($plugin);
		
		return $this->nameSpace;
	}
	
	function js($file, $compress = true, $static = true, $core = false) { 
		if(!isset($this->js[$file])) $this->js[$file] = array("file" => $file, "compress" => $compress, "static" => $static, "core" => $core);
	}
	
	function jsCode($content) {
		$this->jsCode[] = $content;
	}
	
	function css($file, $media = null, $core = false) {
		if(!isset($this->css[$file])) $this->css[$file] = array("file" => $file, "media" => $media, "core" => $core );
	}
	
	function documentReady($js) {
		$this->ready[] = $js;
	}
	
	function plugin($name, $jQuery = true) {
		if($jQuery) { 
			$name = "jquery.".$name;
			$this->jQuery();
		}
		if(!isset($this->plugins[$name])) $this->plugins[$name] = true;
	}
	
	
	function language($name) {
		if(!isset($this->langLoaded[$name])) {
			$jlang =& JFactory::getLanguage();
			//-- Load language files
			$jlang->load($name, JPATH_SITE.'/plugins/system/joomessLibrary', 'en-GB', true);
			$jlang->load($name, JPATH_SITE.'/plugins/system/joomessLibrary', null, true);
			
			$this->langLoaded[$name] = true;
		}
	}
	
	function special($name, $pars = array(), $preload = false) {
		switch($name) {
			case "googlePlusButton":
				$this->plugin("googlePlusButton", false); 
				
				$link = isset($pars["href"]) ? $pars["href"] : JUri::current();
				$size = isset($pars["size"]) ? $pars["size"] : "standard";
				$annotation = isset($pars["annotation"]) ? $pars["annotation"] : "bubble";
				
				return '<g:plusone size="'.$size.'" annotation="'.$annotation.'" href="'.$link.'"></g:plusone>';
				
			case "googleShareButton":
				$this->css("plugins/system/joomessLibrary/css/special.googleShareButton.css");
				if($preload) return true;
				
				$link = isset($pars["href"]) ? $pars["href"] : JUri::current();
				
				$sharelink = 'https://plus.google.com/share?url='.urlencode($link);
				return '<a href="'.$sharelink.'" class="joomess-goShBu" target="_blank" onclick="' . "window.open('$sharelink','sharer','toolbar=0,status=0,width=600,height=475'); return false;" . '"><span class="goShBuRo"></span>Share</a>';
				
			case "facebookLikeButton":
				$link = isset($pars["href"]) ? $pars["href"] : JUri::current();
				$width = isset($pars["width"]) ? $pars["width"] : 120;
				$height = isset($pars["height"]) ? $pars["height"] : 21;
				$layout = isset($pars["layout"]) ? $pars["layout"] : "button_count";
				
				return '<iframe src="http://www.facebook.com/plugins/like.php?href='.urlencode($link).'&amp;send=false&amp;layout='.$layout.'&amp;width='.$width.'&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font=verdana&amp;height='.$height.'" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:'.$width.'px; height:'.$height.'px;" allowTransparency="true"></iframe>';
				
			case "facebookShareButton":
				$this->css("plugins/system/joomessLibrary/css/special.facebookShareButton.css");
				if($preload) return true;
				
				$link = isset($pars["href"]) ? $pars["href"] : JUri::current();
				
				$sharelink = 'http://www.facebook.com/sharer/sharer.php?u='.urlencode($link);				
				return '<a href="'.$sharelink.'" class="joomess-fbShBu" target="_blank" onclick="' . "window.open('$sharelink','sharer','toolbar=0,status=0,width=548,height=325,menubar=0,resizable=0'); return false;" . '">Share</a>';
			
			case "twitterButton":
				$this->plugin("twitterButton", false);
				
				$link = isset($pars["href"]) ? $pars["href"] : JUri::current();
				
				return '<a href="https://twitter.com/share" class="twitter-share-button" data-url="'.$link.'">Tweet</a>';
				
			case "socialbar":
				$this->plugin("socialshareprivacy");
				$this->css("plugins/system/joomessLibrary/css/jquery.socialshareprivacy.css");
				$this->language("socialshareprivacy");
				
				$link 		= isset($pars["href"]) ? $pars["href"] : JUri::current();
				$id 		= isset($pars["id"]) ? $pars["id"] : uniqid ();
				$gplus 		= isset($pars["gplus"]) ? $pars["gplus"] : true;
				$facebook 	= isset($pars["facebook"]) ? $pars["facebook"] : true;
				$twitter 	= isset($pars["twitter"]) ? $pars["twitter"] : true;
				
				$this->documentReady('jQuery("#'.$id.'").socialSharePrivacy({
							services : {
								facebook : {
									"status": "'.($facebook ? "on" : "off").'",
									"dummy_img": "'.$this->root().'/plugins/system/joomessLibrary/images/socialshareprivacy/dummy_facebook'.( JFactory::getLanguage()->getTag() != 'de-DE' ? "_en" : "" ).'.png",
									"perma_option": "off",
									"txt_info": "'.JText::_('JL_SOCIAL_FACEBOOK').'",
									"txt_fb_off": "'.JText::_('JL_SOCIAL_FACEBOOK_OFF').'",
									"txt_fb_on": "'.JText::_('JL_SOCIAL_FACEBOOK_ON').'"
								},
								twitter : {
									"status": "'.($twitter ? "on" : "off").'",
									"dummy_img": "'.$this->root().'/plugins/system/joomessLibrary/images/socialshareprivacy/dummy_twitter.png",
									"perma_option": "off",
									"txt_info": "'.JText::_('JL_SOCIAL_TWITTER').'",
									"txt_twitter_off": "'.JText::_('JL_SOCIAL_TWITTER_OFF').'",
									"txt_twitter_on": "'.JText::_('JL_SOCIAL_TWITTER_ON').'"
								},
								gplus : {
									"status": "'.($gplus ? "on" : "off").'",
									"dummy_img": "'.$this->root().'/plugins/system/joomessLibrary/images/socialshareprivacy/dummy_gplus.png",
									"perma_option": "off",
									"txt_info": "'.JText::_('JL_SOCIAL_GPLUS').'",
									"txt_gplus_off": "'.JText::_('JL_SOCIAL_GPLUS_OFF').'",
									"txt_gplus_on": "'.JText::_('JL_SOCIAL_GPLUS_ON').'"
								}
							},
							"txt_help": "'.JText::_('JL_SOCIAL_INFO_HELP').'",
							"info_link": "'.JText::_('JL_SOCIAL_INFO_LINK').'",
							"uri": "'.$link.'"
						});');
				
				return '<div id="'.$id.'"> </div>';
				
			case "colorInput":
				$this->plugin("miniColors");
				$this->css("plugins/system/joomessLibrary/css/jquery.miniColors.css");
				
				$name = isset($pars["name"]) ? $pars["name"] : "";
				$id = isset($pars["id"]) ? $pars["id"] : uniqid ();
				$value = isset($pars["value"]) ? $pars["value"] : "#FFFFFF";
				$width = isset($pars["width"]) ? $pars["width"] : "auto";
				$hidden = isset($pars["hidden"]) ? $pars["hidden"] : false;
				$active = isset($pars["active"]) ? $pars["active"] : true;
				
				if($active) $this->documentReady("jQuery('#$id').miniColors();");
				
				return '<input name="'.$name.'" id="'.$id.'" type="'.($hidden ? "hidden" : "text").'" value="'.$value.'" style="width:'.$width.';" />';
				
		}
		return false;
	}
	
	public function minifyCSS($src) {
		$sheet = JLIB_Minify_CSS::minify($src);
		$sheet = str_replace("\n", " ", $sheet);
		return $sheet;
	}
	
	public function minifyJS($src) {
		$sheet = JLIB_JSMin::minify($src);
		return JLIB_JSMin::minify($sheet);
	}
	
	private function loadFiles($ids = array()) {
		if(empty($ids)) return array( );
		$sql = "SELECT `id`, `files` 
				FROM `#__joomess_library` ";
		$i = 0;
		foreach($ids AS $id => $type) {
			if($i != 0) $sql .= " OR "; else $sql .= " WHERE ";  
			$sql .= "`id` = ".$this->db->quote($id);
			$i++;
		}
		$this->db->setQuery($sql);
		$data = $this->db->loadObjectList();
		
		$files = array();
		foreach($data AS $dat) {
			$decode = json_decode($dat->files);
			$files[$dat->id] = $decode;
		}
		return $files;
	}
	
	function render() { 
		$now = time();
		$cache_time = $this->getParam("cache_enabled", $now);
		$caching = ($now-$cache_time >= 0); 
		$css_caching = $this->getParam("css_cache", true);
		
		$session =& JFactory::getSession();
		
		//Clean cache
		if(!$caching) $this->cleanAll();
		
		//Full-site caching - testing
		if($this->getParam("full_cache_css", false) && $css_caching) { 
			$app =& JFactory::getApplication();
			$headData = $this->doc->getHeadData(); 
			foreach($headData["styleSheets"] AS $file => $attrs) { 
				if($attrs["mime"] == 'text/css' && (empty($attrs["attribs"]) || is_string($attrs["attribs"]))) { 
					$key = $file; 
					//Check link of file
					$withHttp = false;
					if($this->root(true) == substr($file, 0, strlen($this->root(true)))) {
						$file = substr($file, strlen($this->root(true)));
						$withHttp = true;
					}
					else if($this->root(false) == substr($file, 0, strlen($this->root(false)))) {
						$file = substr($file, strlen($this->root(false)));
						$withHttp = true;
					}
					if(substr($file, 0, 1) == "/") $file = ltrim($file, "/"); 
					$parts = explode("?", $file); $file = $parts[0];
					//Is template file?
					$core = false;
					if(substr($file, 0, 9) == 'templates' || substr($file, 14, 9) == 'templates' || substr($file, -12) == 'template.css') $core = true; 
					//Check admin file
					$withAdmin = false;
					if(substr($file, 0, 13) == 'administrator' && $app->isAdmin()) {
						$file = substr($file, 14);
						$withAdmin = true;
					}
					if($app->isAdmin() && substr($file, 0, 3) == '../') $file = substr($file, 3);
					else if(($app->isAdmin() && !$withHttp) || $withAdmin) $file = "administrator/".$file;
					//Lib can find file
					if(JFile::exists(JPATH_SITE.DS.$file)) {
						//Add file to css loader
						$this->css($file, $attrs["media"], $core); //var_dump($file);
						//Remove file of Joomla! cache
						unset($headData["styleSheets"][$key]);
					}
				} //else var_dump($attrs);
			}
			if(empty($headData["styleSheets"])) $headData["styleSheets"]["jm_dummy.css"] = array();
			$this->doc->setHeadData($headData);
		}
		
		//Check files to Cache
		$loaded = $caching ? $session->get("files", array(), "joomessLibrary") : array(); 
		foreach($loaded AS $file => $type) 
			if(!JFile::exists(JPATH_SITE.'/'.$this->cacheFolder."/".$file)) 
				unset($loaded[$file]);
		$files = $caching ? $this->loadFiles($loaded) : array();
		
		//Core files überprüfen
		foreach($files AS $id => $dat) {
			$parts = explode(".", $id);
			switch($parts[count($parts)-1]) {
				case "js":
					foreach($dat->js->c AS $cFile) {
						if(!isset($this->js[$cFile])) {
							unset($files[$id]);
							unset($loaded[$id]);
							break;
						}
					}
					break;
				case "css":
					foreach($dat->c AS $cFile) {
						if(!isset($this->css[$cFile])) {
							unset($files[$id]);
							unset($loaded[$id]);
							break;
						}
					}
					break;
			}
		}
		//Vorhanden Dateien nicht mehr laden
		$usedFiles = array();
		$loadQuery = $this->jquery; 
		$queryFile = "";
		foreach($files AS $id => $dat) { 
			$usedFiles[$id] = array();
			$parts = explode(".", $id);
			switch($parts[count($parts)-1]) {
				case "js":
					if(isset($dat->jQuery)) { $loadQuery = false; $queryFile = $id; $usedFiles[$id][] = 'jQuery'; }
					foreach($dat->plugins AS $plugin)
						if(isset($this->plugins[$plugin])) { unset($this->plugins[$plugin]); $usedFiles[$id][] = $plugin; }
					foreach($dat->js->c AS $file)
						if(isset($this->js[$file])) { unset($this->js[$file]); $usedFiles[$id][] = $file; }
					foreach($dat->js->d AS $file)
						if(isset($this->js[$file])) { unset($this->js[$file]); $usedFiles[$id][] = $file; }
					break;
				case "css":
					if(!$css_caching) break;
					foreach($dat->c AS $file)
						if(isset($this->css[$file])) { unset($this->css[$file]); $usedFiles[$id][] = $file; }
					foreach($dat->d AS $file)
						if(isset($this->css[$file])) {unset($this->css[$file]); $usedFiles[$id][] = $file; }
					break;
			}
		}
		
		foreach($loaded AS $id => $type) { 
			if(empty($usedFiles[$id])) unset($loaded[$id]);
		}
		
		$loadFiles = array();
		if($queryFile != "") $loadFiles[] = array( "id" => $queryFile, "type" => "js" );
		foreach($loaded AS $id => $type)
			if(!empty($usedFiles[$id]))
				$loadFiles[] = array( "id" => $id, "type" => $type );
		
		//Javascript
			//Variables JS
			foreach($this->jsCode AS $data) 
				$this->_addScriptDeclaration( $data );
			if(!empty($this->ready)) {
				$readyJS = "";
				foreach($this->ready AS $js) {
					$readyJS .= $js;
				}
				$readyJS = $this->minifyJS($readyJS);
				$readyJS = str_replace("jQuery", $this->nameSpace, $readyJS);
				$readyJS = str_replace("$", $this->nameSpace, $readyJS);
				$this->_addScriptDeclaration($this->nameSpace."('document').ready(function() { $readyJS });");
			} 
			
			//ID generieren
			$id = ($loadQuery) ? $this->nameSpace : "";
			foreach($this->js AS $dat) if($dat["static"]) $id .= $dat["file"];
			foreach($this->plugins AS $name => $bool) $id .= $name;
			$id = md5($id).".js";
			
			$cacheFile = $this->cacheFolder."/".$id; 
			
			if((!JFile::exists(JPATH_SITE.'/'.$cacheFile) || !$caching) && (!empty($this->js) || !empty($this->plugins) || $loadQuery)) {
				$js = array();
				$files = array(); 
				
				if($loadQuery) {
					$js[] = "if(window.jQuery!=undefined)JMQueryOld=jQuery;";
					$files['jQuery'] = true;
					$js[] = JFile::read(JPATH_SITE.'/'."plugins/system/joomessLibrary/js/jquery-1.8.3.min.noconflict.js");
					$js[] = $this->nameSpace."=jQuery.noConflict(true);if(window.JMQueryOld!=undefined)jQuery=JMQueryOld;";
				}
				
				$plugins = array();
				foreach($this->plugins AS $name => $bool) {
					$plugins[] = $name;
					$path = "plugins/system/joomessLibrary/js/plugins/$name.js";
					if(JFile::exists(JPATH_SITE.'/'.$path)) {
						$content = JFile::read(JPATH_SITE.'/'.$path);
						$content = str_replace("jQuery", $this->nameSpace, $content);
						$head = "\n/*! Plugin: $path */ \n";
							
						$js[] = $head.$content;
					} else {
						echo "Plugin nicht gefunden: " . $path . "\n";
					}
				}
				$files['plugins'] = $plugins;
				
				$js_files = array( 'c' => array(), 'd' => array() );
				foreach($this->js AS $dat) { 
					$js_files[$dat["core"] ? 'c' : 'd'][] = $dat["file"];
					if($dat["static"]) {
						$path = JPATH_SITE.'/'.str_replace("/", DS, $dat["file"]);
						
						if(JFile::exists($path)) {
							$content = JFile::read($path);
							$content = str_replace("jQuery", $this->nameSpace, $content);
							$head = "\n/*! File: ".$dat["file"]." */ \n";
							
							if($dat["compress"]) $js[] = $head.$this->minifyJS($content);
							else $js[] = $head.$content;
						} else {
							echo "Script nicht gefunden: " . $path . "\n";
						}
					}
				}
				$files["js"] = $js_files;
				
				$out = "/*! Joomess File-Compress */ \n";
				$out .= implode("\n", $js);
				
				JFile::write(JPATH_SITE.'/'.$cacheFile, $out);
				
				//Vorhandenen Eintrag eventuell entfernen
				$sql = " DELETE FROM `#__joomess_library` WHERE `id` = {$this->db->quote($id)} LIMIT 1 ";
				$this->db->setQuery($sql);
				$this->db->query();
				
				$date = JFactory::getDate();
				
				//Datenbankeintrag erstellen
				$ins = new JObject();
				$ins->id = $id;
				$ins->files = json_encode($files);
				$ins->created_date = ($this->getJoomlaVersion() == self::jVersion30) ? $date->toSql() : $date->toMySQL();
				
				$this->db->insertObject("#__joomess_library", $ins, "id");
				
				$loadFiles[] = array( "id" => $id, "type" => "js" );
			} else if(!empty($this->js) || !empty($this->plugins)) { 
				$loadFiles[] = array( "id" => $id, "type" => "js" );
			}
			
		//CSS
		if($caching && $css_caching) { //Generate CSS-Files only when caching is enabled
			//ID generieren
			$id = "";
			foreach($this->css AS $dat => $attrs) $id .= $dat;
			$id = md5($id).".css";
				
			$cacheFile = $this->cacheFolder."/".$id;
				
			if((!JFile::exists(JPATH_SITE.'/'.$cacheFile) || !$caching) && !empty($this->css)) {
				$css = array();
				$files = array( 'c' => array(), 'd' => array() );
				
				foreach($this->css AS $file => $attrs) { 
					$files[$attrs["core"] ? 'c' : 'd'][] = $file;
					$this->_loadCss($css, $file, $attrs["media"]);
				}
				//var_dump($this->css);
				//var_dump($css);
				
				$out = "/*! Joomess File-Compress */ \n";
				$out .= implode("\n", $css);
				
				JFile::write(JPATH_SITE.'/'.$cacheFile, $out);
				
				//Vorhandenen Eintrag eventuell entfernen
				$sql = " DELETE FROM `#__joomess_library` WHERE `id` = {$this->db->quote($id)} LIMIT 1 ";
				$this->db->setQuery($sql);
				$this->db->query();
				
				$date = JFactory::getDate();
				
				//Datenbankeintrag erstellen
				$ins = new JObject();
				$ins->id = $id;
				$ins->files = json_encode($files);
				$ins->created_date = ($this->getJoomlaVersion() == self::jVersion30) ? $date->toSql() : $date->toMySQL();
				
				$this->db->insertObject("#__joomess_library", $ins, "id");
			
				$loadFiles[] = array( "id" => $id, "type" => "css" );
			} else if(!empty($this->css)) {
				$loadFiles[] = array( "id" => $id, "type" => "css" );
			}
		} else { //Caching is disabled -> append css files normally (Firebug etc.)
			foreach($this->css AS $file => $attrs) {
				$this->_addStyleSheet($this->root(true)."/".$file, 'text/css', $attrs["media"], array( "jm_safe" => true ));
			}
		}
			
		foreach($loadFiles AS $file) {
			if($file["type"] == "js")  $this->_addScript($this->root(true)."/".$this->cacheFolder."/".$file["id"]);
			elseif($file["type"] == "css" && $css_caching) $this->_addStyleSheet($this->root(true)."/".$this->cacheFolder."/".$file["id"]);
		}
		
		$headData = $this->doc->getHeadData();
		//Remove "jm_dummy.css"
		if(isset($headData["styleSheets"]["jm_dummy.css"]))
			unset($headData["styleSheets"]["jm_dummy.css"]);
		//Append files to head Data
		foreach($this->_headScript AS $key => $content) {
			if(isset($headData["script"][$key]))
				$headData["script"][$key] = $content . chr(13) . $headData["script"][$key];
			else
				$headData["script"][$key] = $content;
		}
		$headData["scripts"] 		= array_merge($this->_headScripts, $headData["scripts"]);
		$headData["styleSheets"] 	= array_merge($this->_headStyleSheets, $headData["styleSheets"]);
		
		$this->_headScripts 	= array();
		$this->_headStyleSheets = array();
		$this->_headScript 		= array();
		
		$this->doc->setHeadData($headData);
		
		$session->set("files", $loaded, "joomessLibrary");
		
		//Save Plugin params
		if($this->changedParams) $this->saveParams();
		
		return $loaded;
	}
	
	private function _addScript($url, $type = "text/javascript", $defer = false, $async = false) {
		switch($this->getJoomlaVersion()) {
			case self::jVersion15:
				$this->_headScripts[$url] = $type;
				break;
			default:
				$this->_headScripts[$url]['mime'] = $type;
				$this->_headScripts[$url]['defer'] = $defer;
				$this->_headScripts[$url]['async'] = $async;
				break;
		}
		
	}
	
	private function _addStyleSheet($url, $type = 'text/css', $media = null, $attribs = array()) {
		$this->_headStyleSheets[$url]['mime'] = $type;
		$this->_headStyleSheets[$url]['media'] = $media;
		$this->_headStyleSheets[$url]['attribs'] = $attribs;
	}
	
	private function _addScriptDeclaration($content, $type = 'text/javascript') {
		if (!isset($this->_headScript[strtolower($type)]))
			$this->_headScript[strtolower($type)] = $content;
		else
			$this->_headScript[strtolower($type)] .= chr(13) . $content;
	}
	
	private function _loadCss(&$css, $file, $media = null) {
		$path = JPATH_SITE.'/'.str_replace('\\', '/', $file);
		$path = str_replace( '//', '/', $path );
		if(JFile::exists($path)) {
			$content = JFile::read($path);
			$head = "\n/*! File: ".$file." */ \n";
		
			$sheet = $this->minifyCSS($content);
			
			$folders = explode("/", $file);
			$cfolders = explode("/", $this->cacheFolder);
			
			//Check for "imports" - recursive
			preg_match_all('#@import (url\()?("|\')?(.*?)("|\')?\)?;#i', $sheet, $matches); 
			foreach($matches[0] AS $i => $match) {
				$url = $matches[3][$i];
				$shorters = explode("../", $url);
				$path = "";
				for($u = 0; $u < count($folders) - count($shorters); $u++)
					$path .= $folders[$u]."/";
				$path .= $shorters[count($shorters) - 1];
				$path = str_replace("//", "/", $path);
				
				$this->_loadCss($css, $path);
				$sheet = str_replace( $match, "", $sheet );
			}
		
			//Update urls of images
			preg_match_all('#url\((.*?)\)#i', $sheet, $matches);
		
			foreach($matches[0] AS $i => $match) {
				$url = $matches[1][$i];
				$url = trim( $url, '\'"');
				$shorters = explode("../", $url);
				$path = ""; $ccount = 0;
				for($u = 0; $u < count($folders) - count($shorters); $u++)
					if(isset($cfolders[$u]))
						if($cfolders[$u] == $folders[$u]) $ccount++; else break;
				//if( (count($cfolders) - $ccount) > 3 ) $ccount = 
				for($u = 0; $u < count($cfolders) - $ccount; $u++)
					$path .= "../";
				for($u = $ccount; $u < count($folders) - count($shorters); $u++)
					$path .= $folders[$u]."/";
				$path .= $shorters[count($shorters) - 1];
				$path = str_replace("//", "/", $path);
					
				$sheet = str_replace( $match, "url($path)", $sheet );
			}
		
			if(trim($sheet) != "") {
				if($media != null)
					$sheet = "@media $media {".$sheet."}";
				$css[] = $head.$sheet;
			}
		} else {
			//echo "Stylesheet nicht gefunden: " . $path . "\n";
		}
	}
	
	private $rootpaths;
	public function root($pathonly = false) {
		$key = $pathonly ? "only" : "all"; 
		if(empty($this->rootpaths)) $this->rootpaths = array();
		if(empty($this->rootpaths[$key])) {
			$root = JUri::root($pathonly); 
			
			$root = rtrim($root, "/");
			$parts = explode("/", $root);
			
			switch($parts[count($parts) - 1]) {
				case "assistant": 
					if($parts[count($parts) - 3] == "components") {
						$root = "";
						for($i = 0; $i < count($parts) - 3; $i++) $root .= $parts[$i]."/";
					}
					break;
				case "com_jvotesystem": case "com_jchatsystem": case "com_stripegallery":
					$root = "";
					for($i = 0; $i < count($parts) - 2; $i++) $root .= $parts[$i]."/";
					break;
			}
			
			$root = rtrim($root, "/");
			$this->rootpaths[$key] = $root;
		}
		return $this->rootpaths[$key];
	}
	
	const jVersion15 = 1.5;
	const jVersion25 = 2.5;
	const jVersion30 = 3.0;
	public function getJoomlaVersion() {
		if(version_compare( JVERSION, '1.6.0', 'lt' )) return self::jVersion15;
		if(version_compare( JVERSION, '3.0.0', 'lt' )) return self::jVersion25;
		else return self::jVersion30;
	}
	
	public function cleanAll() {
		//Folder
		if(JFolder::exists(JPATH_SITE.'/'.$this->cacheFolder))
			JFolder::delete(JPATH_SITE.'/'.$this->cacheFolder);
		
		//DB
		$sql = " TRUNCATE TABLE `#__joomess_library` ";
		$this->db->setQuery($sql);
		$this->db->query();
		
		return true;
	}
	
	public function clean($days = 3) {
		$sql = "SELECT `id`
				FROM `#__joomess_library`
				WHERE DATE_SUB(CURDATE(),INTERVAL 3 DAY) > `created_date` 
				LIMIT 0, 100 ";
		$this->db->setQuery($sql);
		$list = $this->db->loadObjectList();
		
		foreach($list AS $file) {
			if(JFile::exists(JPATH_SITE.'/'.$this->cacheFolder.'/'.$file->id))
				JFile::delete(JPATH_SITE.'/'.$this->cacheFolder.'/'.$file->id);
		}				
		
	}
	
	
	public function connectToServer($eid, $task, $params = array()) {
		//Connect to joomess Server
		$url 				= array();
		$url['view'] 		= 'tools';
		$url['task'] 		= $task;
		$url['id'] 			= $eid;
		$url['url'] 		= urlencode( joomessLibrary::getInstance()->root() );
		$url['lang']		= JFactory::getLanguage()->getTag();
			$options = array();
			$options['php'] 	= @phpversion();
			$jversion = new JVersion();
			$options['joomla']	= $jversion->getShortVersion();
			$options['mysql']	= @mysql_get_client_info();
		$url['versions']	= json_encode($options); //Only for support!
		
		foreach($params AS $key => $param)
			$url[$key] = urlencode($param);
	
		$req = 'http://www.joomess.de/index.php?option=com_je';
		foreach($url AS $key => $val) $req .= '&'.$key.'='.$val;
			
		$data = @file_get_contents($req);
			
		if($data) {
			$out = json_decode($data); 
		} else $out = false;
	
		return $out;
	}
	
	/* The copyright information may not be removed or made invisible! To remove the code, please purchase a version on www.joomess.de. Thanks!*/
	function copyright($name, $handler = null) {
		$out = array();
		$out[] = '<p style="text-align: center;font-size: 11pt; font-style: italic; text-align: center;">';
		$out[] = VBGeneral::getInstance()->buildHtmlLink('http://joomess.de/projects/jvotesystem', 'jVoteSystem');
		$out[] = ' developed and designed by ';
		$out[] = VBGeneral::getInstance()->buildHtmlLink('http://joomess.de', 'www.joomess.de.');
		$out[] = '</p>';
		
		return implode("", $out);
	}
	
}
?>