<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 *
 * @since     2.1
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * Extension Filter controller class
 *
 */
class AkeebaControllerExtfilter extends AkeebaControllerDefault
{
	public function execute($task)
	{
		if (!in_array($task, array(
			'components', 'languages', 'modules', 'plugins', 'templates',
			'toggleComponent', 'toggleLanguage', 'toggleModule',
			'togglePlugin', 'toggleTemplate'
		))
		)
		{
			$task = 'components';
		}
		parent::execute($task);
	}

	/**
	 * Components task, shows all non-core components
	 */
	function components($cachable = false, $urlparams = false)
	{
		$this->getThisView()->setLayout('components');
		$this->getThisView()->task = components;
		parent::display($cachable, $urlparams);
	}

	/**
	 * Languages task, shows all languages except the default
	 */
	function languages($cachable = false, $urlparams = false)
	{
		$this->getThisView()->setLayout('languages');
		$this->getThisView()->task = 'languages';
		parent::display($cachable, $urlparams);
	}

	/**
	 * Modules task, shows all non-core modules
	 */
	function modules($cachable = false, $urlparams = false)
	{
		$this->getThisView()->setLayout('modules');
		$this->getThisView()->task = 'modules';
		parent::display($cachable, $urlparams);
	}

	/**
	 * Plugins task, shows all non-core plugins
	 */
	function plugins($cachable = false, $urlparams = false)
	{
		$this->getThisView()->setLayout('plugins');
		$this->getThisView()->task = 'plugins';
		parent::display($cachable, $urlparams);
	}

	/**
	 * Templates task, shows all non-core templates
	 */
	function templates($cachable = false, $urlparams = false)
	{
		$this->getThisView()->setLayout('templates');
		$this->getThisView()->task = 'templates';
		parent::display($cachable, $urlparams);
	}

	/**
	 * Toggles the exclusion of a component
	 *
	 */
	function toggleComponent()
	{
		//JResponse::setHeader('Cache-Control','no-cache, must-revalidate',true); // HTTP 1.1 - Cache control
		//JResponse::setHeader('Expires','Sat, 26 Jul 1997 05:00:00 GMT',true); // HTTP 1.0 - Date in the past

		// Get the option passed along
		$root = $this->input->get('root', 'default', 'string');
		$item = $this->input->get('item', '', 'string');

		// Try to figure out if this component is allowed to be excluded (exists and is non-Core)
		$model = $this->getThisModel();
		$components = $model->getComponents();

		$found = false;
		$numRows = count($components);
		for ($i = 0; $i < $numRows; $i++)
		{
			$row = $components[$i];
			if ($row['item'] == $item)
			{
				$found = true;
				$name = $row['name'];
			}
		}

		$link = JUri::base() . 'index.php?option=com_akeeba&view=extfilter&task=components';
		if (!$found)
		{
			$msg = JText::sprintf('EXTFILTER_ERROR_INVALIDCOMPONENT', $item);
			$this->setRedirect($link, $msg, 'error');
		}
		else
		{
			$model->toggleComponentFilter($root, $item);
			$link = JUri::base() . 'index.php?option=com_akeeba&view=extfilter&task=components';
			$msg = JText::sprintf('EXTFILTER_MSG_TOGGLEDCOMPONENT', $name);
			$this->setRedirect($link, $msg);
		}

		parent::redirect();
	}

	/**
	 * Toggles the exclusion of a module
	 *
	 */
	function toggleModule()
	{
		//JResponse::setHeader('Cache-Control','no-cache, must-revalidate',true); // HTTP 1.1 - Cache control
		//JResponse::setHeader('Expires','Sat, 26 Jul 1997 05:00:00 GMT',true); // HTTP 1.0 - Date in the past

		// Get the option passed along
		$root = $this->input->get('root', 'frontend', 'string');
		$item = $this->input->get('item', '', 'string');


		// Try to figure out if this component is allowed to be excluded (exists and is non-Core)
		$model = $this->getThisModel();
		$modules = $model->getModules();

		$found = false;
		$numRows = count($modules);
		for ($i = 0; $i < $numRows; $i++)
		{
			$row = $modules[$i];
			if (($row['item'] == $item) && ($row['root'] == $root))
			{
				$found = true;
				$name = $row['name'];
				break;
			}
		}

		$link = JUri::base() . 'index.php?option=com_akeeba&view=extfilter&task=modules';
		if (!$found)
		{
			$msg = JText::sprintf('EXTFILTER_ERROR_INVALIDMODULE', $item);
			$this->setRedirect($link, $msg, 'error');
		}
		else
		{
			$model->toggleModuleFilter($root, $item);
			$msg = JText::sprintf('EXTFILTER_MSG_TOGGLEDMODULE', $name);
			$this->setRedirect($link, $msg);
		}

		parent::redirect();
	}

	/**
	 * Toggles the exclusion of a language
	 *
	 */
	function toggleLanguage()
	{
		//JResponse::setHeader('Cache-Control','no-cache, must-revalidate',true); // HTTP 1.1 - Cache control
		//JResponse::setHeader('Expires','Sat, 26 Jul 1997 05:00:00 GMT',true); // HTTP 1.0 - Date in the past

		// Get the option passed along
		$root = $this->input->get('root', '', 'string');
		$item = $this->input->get('item', '', 'string');


		// Try to figure out if this component is allowed to be excluded (exists and is non-Core)
		$model = $this->getThisModel();
		$languages = $model->getLanguages();

		$found = false;
		$numRows = count($languages);
		for ($i = 0; $i < $numRows; $i++)
		{
			$row = $languages[$i];
			if (($row['item'] == $item) && ($row['root'] == $root))
			{
				$found = true;
				$name = $row['name'];
				break;
			}
		}

		$link = JUri::base() . 'index.php?option=com_akeeba&view=extfilter&task=languages';
		if (!$found)
		{
			$msg = JText::sprintf('EXTFILTER_ERROR_INVALIDLANGUAGE', $item);
			$this->setRedirect($link, $msg, 'error');
		}
		else
		{
			$model->toggleLanguageFilter($root, $item);
			$msg = JText::sprintf('EXTFILTER_MSG_TOGGLEDLANGUAGE', $name);
			$this->setRedirect($link, $msg);
		}

		parent::redirect();
	}

	/**
	 * Toggles the exclusion of a plugin
	 *
	 */
	function togglePlugin()
	{
		//JResponse::setHeader('Cache-Control','no-cache, must-revalidate',true); // HTTP 1.1 - Cache control
		//JResponse::setHeader('Expires','Sat, 26 Jul 1997 05:00:00 GMT',true); // HTTP 1.0 - Date in the past

		// Get the option passed along
		$root = $this->input->get('root', '', 'string');
		$item = $this->input->get('item', '', 'string');


		// Try to figure out if this component is allowed to be excluded (exists and is non-Core)
		$model = $this->getThisModel();
		$plugins = $model->getPlugins();

		$found = false;
		$numRows = count($plugins);
		for ($i = 0; $i < $numRows; $i++)
		{
			$row = $plugins[$i];
			if (($row['item'] == $item) && ($row['root'] == $root))
			{
				$found = true;
				$name = $row['name'];
				break;
			}
		}

		if (!$found)
		{
			$link = JUri::base() . 'index.php?option=com_akeeba&view=extfilter&task=plugins';
			$msg = JText::sprintf('EXTFILTER_ERROR_INVALIDPLUGIN', $item);
			$this->setRedirect($link, $msg, 'error');
		}
		else
		{
			$model->togglePluginFilter($root, $item);
			$link = JUri::base() . 'index.php?option=com_akeeba&view=extfilter&task=plugins';
			$msg = JText::sprintf('EXTFILTER_MSG_TOGGLEDPLUGIN', $name);
			$this->setRedirect($link, $msg);
		}

		parent::redirect();
	}

	/**
	 * Toggles the exclusion of a template
	 *
	 */
	function toggleTemplate()
	{
		//JResponse::setHeader('Cache-Control','no-cache, must-revalidate',true); // HTTP 1.1 - Cache control
		//JResponse::setHeader('Expires','Sat, 26 Jul 1997 05:00:00 GMT',true); // HTTP 1.0 - Date in the past

		// Get the option passed along
		$root = $this->input->get('root', '', 'string');
		$item = $this->input->get('item', '', 'string');

		// Try to figure out if this component is allowed to be excluded (exists and is non-Core)
		$model = $this->getThisModel();
		$templates = $model->getTemplates();

		$found = false;
		$numRows = count($templates);
		for ($i = 0; $i < $numRows; $i++)
		{
			$row = $templates[$i];
			if (($row['item'] == $item) && ($row['root'] == $root))
			{
				$found = true;
				$name = $row['name'];
				break;
			}
		}

		$link = JUri::base() . 'index.php?option=com_akeeba&view=extfilter&task=templates';
		if (!$found)
		{
			$msg = JText::sprintf('EXTFILTER_ERROR_INVALIDTEMPLATE', $item);
			$this->setRedirect($link, $msg, 'error');
		}
		else
		{
			$model->toggleTemplateFilter($root, $item);
			$msg = JText::sprintf('EXTFILTER_MSG_TOGGLEDTEMPLATE', $name);
			$this->setRedirect($link, $msg);
		}

		parent::redirect();
	}
}