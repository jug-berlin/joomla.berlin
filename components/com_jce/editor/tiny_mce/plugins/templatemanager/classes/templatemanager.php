<?php

/**
 * @package   	JCE
 * @copyright 	Copyright (c) 2009-2013 Ryan Demmer. All rights reserved.
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
require_once (WF_EDITOR_LIBRARIES . '/classes/manager.php');

class WFTemplateManagerPlugin extends WFMediaManager {

    var $_filetypes = 'html=html,htm;text=txt';

    /**
     * @access	protected
     */
    public function __construct() {
        parent::__construct();

        // add a request to the stack
        $request = WFRequest::getInstance();
        $request->setRequest(array($this, 'loadTemplate'));

        if ($this->getParam('allow_save', 1)) {
            $request->setRequest(array($this, 'createTemplate'));
        }
    }

    /**
     * Display the plugin
     */
    public function display() {
        // Add actions before initialising parent

        if ($this->getParam('allow_save', 1)) {
            $browser = $this->getBrowser();
            $browser->addAction('save', array('action' => 'createTemplate', 'title' => WFText::_('WF_TEMPLATEMANAGER_CREATE')));
        }

        parent::display();

        $document = WFDocument::getInstance();

        $document->addScript(array('templatemanager'), 'plugins');
        $document->addStyleSheet(array('templatemanager'), 'plugins');

        $settings = $this->getSettings();

        $document->addScriptDeclaration('TemplateManager.settings=' . json_encode($settings) . ';');
    }

    public function createTemplate($dir, $name, $type) {
        $browser = $this->getBrowser();
        $filesystem = $browser->getFileSystem();

        // check path
        WFUtility::checkPath($dir);

        // check name
        WFUtility::checkPath($name);

        // check for extension in destination name
        if (preg_match('#\.(php|php(3|4|5)|phtml|pl|py|jsp|asp|htm|html|shtml|sh|cgi)\.#i', $name)) {
            JError::raiseError(403, 'RESTRICTED');
        }

        // get data
        $data = JRequest::getVar('data', '', 'POST', 'HTML', JREQUEST_ALLOWRAW);
        $data = rawurldecode($data);

        $name = JFile::makeSafe($name) . '.html';
        $path = WFUtility::makePath($dir, $name);

        // Remove any existing template div
        $data = preg_replace('/<div(.*?)class="mceTmpl"([^>]*?)>([\s\S]*?)<\/div>/i', '$3', $data);

        if ($type == 'template') {
            $data = '<div class="mceTmpl">' . $data . '</div>';
        }

        $content  = "<!DOCTYPE html>\n";
        $content .= "<html>\n";
        $content .= "<head>\n";
        $content .= "<title>" . $name . "</title>\n";
        $content .= "<meta charset=\"utf-8\">\n";
        $content .= "</head>\n";
        $content .= "<body>\n";
        $content .= $data;
        $content .= "\n</body>\n";
        $content .= "</html>";

        if (!$filesystem->write($path, stripslashes($content))) {
            $browser->setResult(WFText::_('WF_TEMPLATEMANAGER_WRITE_ERROR'), 'error');
        }

        return $browser->getResult();
    }

    protected function replaceVars($matches) {
        switch ($matches[1]) {
            case 'modified' :
                return strftime($this->getParam('mdate_format', '%Y-%m-%d %H:%M:%S'));
                break;
            case 'created' :
                return strftime($this->getParam('cdate_format', '%Y-%m-%d %H:%M:%S'));
                break;
            case 'username' :
            case 'usertype' :
            case 'name' :
            case 'email' :
                $user = JFactory::getUser();
                return isset($user->$matches[1]) ? $user->$matches[1] : $matches[1];
                break;
            default :

                // Replace other pre-defined variables
                $params = $this->getParam('replace_values');
                if ($params) {
                    foreach (explode(',', $params) as $param) {
                        $k = preg_split('/[:=]/', $param);
                        if (trim($k[0]) == $matches[1]) {
                            return trim($k[1]);
                        }
                    }
                }
                break;
        }
    }

    public function loadTemplate($file) {
        $browser = $this->getBrowser();
        $filesystem = $browser->getFileSystem();

        // check path
        WFUtility::checkPath($file);

        $content = $filesystem->read($file);

        // Remove body etc.
        if (preg_match('/<body[^>]*>([\s\S]+?)<\/body>/', $content, $matches)) {
            $content = trim($matches[1]);
        }

        // Replace variables
        $content = preg_replace_callback('/\{\$(.+?)\}/i', array($this, 'replaceVars'), $content);

        return $content;
    }

    public function getViewable() {
        return $this->getFileTypes('list');
    }

}

?>