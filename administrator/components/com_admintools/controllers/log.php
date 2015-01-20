<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsControllerLog extends F0FController
{
	public function ban()
	{
		$this->_csrfProtection();

		$id = $this->input->getString('id', '');
		if (empty($id))
		{
			JError::raiseError('500', JText::_('ATOOLS_ERR_LOG_BAN_NOID'));
		}

		$model = $this->getThisModel();
		$model->setIds(array($id));
		$item = $model->getItem();

		$banModel = F0FModel::getTmpInstance('Ipbls', 'AdmintoolsModel');
		$data = array(
			'id'          => 0,
			'ip'          => $item->ip,
			'description' => JText::_('ATOOLS_LBL_REASON_' . strtoupper($item->reason))
		);
		$banModel->getTable()->save($data);

		$this->setRedirect('index.php?option=com_admintools&view=logs', JText::_('ATOOLS_LBL_IPBL_SAVED'));
	}

	public function unban()
	{
		$this->_csrfProtection();

		$id = $this->input->getString('id', '');
		if (empty($id))
		{
			JError::raiseError('500', JText::_('ATOOLS_ERR_LOG_BAN_NOID'));
		}

		$model = $this->getThisModel();
		$model->setIds(array($id));
		$item = $model->getItem();

		$banModel = F0FModel::getTmpInstance('Ipbls', 'AdmintoolsModel')
			->ip($item->ip);
		$items = $banModel->getItemList();
		$ids = array();
		foreach ($items as $banItem)
		{
			$ids[] = $banItem->id;
		}
		$banModel->setIds($ids);
		$banModel->delete();
		$this->setRedirect('index.php?option=com_admintools&view=logs', JText::_('ATOOLS_LBL_IPBL_DELETED'));
	}

	protected function onBeforeBrowse()
	{
		return $this->checkACL('admintools.security');
	}

	protected function onBeforeRemove()
	{
		return $this->checkACL('admintools.security');
	}

	protected function onBeforeBan()
	{
		return $this->checkACL('admintools.security');
	}

	protected function onBeforeUnban()
	{
		return $this->checkACL('admintools.security');
	}
}