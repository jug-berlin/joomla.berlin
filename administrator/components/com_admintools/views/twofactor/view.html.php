<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsViewTwofactor extends F0FViewHtml
{
	protected function onBrowse($tpl = null)
	{
		// Set the toolbar title
		$model = $this->getModel();
		$model2 = F0FModel::getTmpInstance('Wafconfig', 'AdmintoolsModel');
		$config = $model2->getConfig();

		$userInfo = $model->getFakeUser();
		$user = $userInfo['user'] . '@' . $userInfo['hostname'];

		$this->supported = class_exists('F0FEncryptTotp');
		$this->qrcodeurl = $model->getQRCodeURL();
		$this->enabled = $config['twofactorauth'];
		$this->user = $user;
		$this->secret = $config['twofactorauth_secret'];
		$this->panic = $config['twofactorauth_panic'];
	}
}