<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 *
 * @since     3.2
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * Operations against remote files
 */
class AkeebaViewRemotefiles extends F0FViewHtml
{
	/**
	 * Modified constructor to enable loading layouts from the plug-ins folder
	 *
	 * @param $config
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$tmpl_path = dirname(__FILE__) . '/tmpl';
		$this->addTemplatePath($tmpl_path);
	}

	public function onListactions($tpl = null)
	{
		$model = $this->getModel();

		$actions = $model->getActions();

		$this->actions = $actions;

		return true;
	}

	public function onDltoserver($tpl = null)
	{
		$model = $this->getModel();
		$id = $model->getState('id', 0);

		$this->setLayout('dlprogress');

		// Get progress bar stats
		$session = JFactory::getSession();
		$total = $session->get('dl_totalsize', 0, 'akeeba');
		$done = $session->get('dl_donesize', 0, 'akeeba');
		if ($total <= 0)
		{
			$percent = 0;
		}
		else
		{
			$percent = (int)(100 * ($done / $total));
			if ($percent < 0)
			{
				$percent = 0;
			}
			if ($percent > 100)
			{
				$percent = 100;
			}
		}
		$this->total = $total;
		$this->done = $done;
		$this->percent = $percent;

		$this->id = $model->getState('id');
		$this->part = $model->getState('part');
		$this->frag = $model->getState('frag');

		// Render the progress bar
		$document = JFactory::getDocument();

		$script = <<<JS


;// This comment is intentionally put here to prevent badly written plugins from causing a Javascript error
// due to missing trailing semicolon and/or newline in their code.
(function($){
	$(document).ready(function(){
		document.forms.adminForm.submit();
	})
})(akeeba.jQuery)


JS;
		$document->addScriptDeclaration($script);

		return true;
	}
}