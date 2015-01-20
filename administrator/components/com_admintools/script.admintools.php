<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
defined('_JEXEC') or die();

// Load FOF if not already loaded
if (!defined('F0F_INCLUDED'))
{
	$paths = array(
		(defined('JPATH_LIBRARIES') ? JPATH_LIBRARIES : JPATH_ROOT . '/libraries') . '/f0f/include.php',
		__DIR__ . '/fof/include.php',
	);

	foreach ($paths as $filePath)
	{
		if (!defined('F0F_INCLUDED') && file_exists($filePath))
		{
			@include_once $filePath;
		}
	}
}

// Pre-load the installer script class from our own copy of FOF
if (!class_exists('F0FUtilsInstallscript', false))
{
	@include_once __DIR__ . '/fof/utils/installscript/installscript.php';
}

// Pre-load the database schema installer class from our own copy of FOF
if (!class_exists('F0FDatabaseInstaller', false))
{
	@include_once __DIR__ . '/fof/database/installer.php';
}

// Pre-load the update utility class from our own copy of FOF
if (!class_exists('F0FUtilsUpdate', false))
{
	@include_once __DIR__ . '/fof/utils/update/update.php';
}

// Pre-load the cache cleaner utility class from our own copy of FOF
if (!class_exists('F0FUtilsCacheCleaner', false))
{
	@include_once __DIR__ . '/fof/utils/cache/cleaner.php';
}

class Com_AdmintoolsInstallerScript extends F0FUtilsInstallscript
{
	/**
	 * The component's name
	 *
	 * @var   string
	 */
	protected $componentName = 'com_admintools';

	/**
	 * The title of the component (printed on installation and uninstallation messages)
	 *
	 * @var string
	 */
	protected $componentTitle = 'Admin Tools';

	/**
	 * The list of extra modules and plugins to install on component installation / update and remove on component
	 * uninstallation.
	 *
	 * @var   array
	 */
	protected $installation_queue = array
	(
		// modules => { (folder) => { (module) => { (position), (published) } }* }*
		'modules' => array(
				'admin' => array(//'atjupgrade' => array('cpanel', 1)
			),
			'site'  => array(

			)
		),
		// plugins => { (folder) => { (element) => (published) }* }*
		'plugins' => array(
		   'system'    => array(
			   'admintools'         => 1,
			   'oneclickaction'     => 0,
			   'atoolsupdatecheck'  => 0,
			   'atoolsjupdatecheck' => 0
		   ),
		   'installer' => array(
			   'admintools' => 1,
		   ),
		)
	);

	/**
	 * The list of obsolete extra modules and plugins to uninstall on component upgrade / installation.
	 *
	 * @var array
	 */
	protected $uninstallation_queue = array
	(
		// modules => { (folder) => { (module) }* }*
		'modules' => array
		(
			'admin' => array
			(
				'atjupgrade'
			),
			'site'  => array(

			)
		),
		// plugins => { (folder) => { (element) }* }*
		'plugins' => array
		(
			'system'    => array(

			),
			'quickicon' => array
			(
				'atoolsjupdatecheck'
			),

		)
	);

	/**
	 * Obsolete files and folders to remove from both paid and free releases. This is used when you refactor code and
	 * some files inevitably become obsolete and need to be removed.
	 *
	 * @var   array
	 */
	protected $removeFilesAllVersions = array(
		'files'   => array(
			'cache/com_admintools.updates.php',
			'cache/com_admintools.updates.ini',
			'administrator/cache/com_admintools.updates.php',
			'administrator/cache/com_admintools.updates.ini',

			'administrator/components/com_admintools/controllers/acl.php',
			'administrator/components/com_admintools/controllers/default.php',
			'administrator/components/com_admintools/controllers/ipautoban.php',
			'administrator/components/com_admintools/models/acl.php',
			'administrator/components/com_admintools/models/base.php',
			'administrator/components/com_admintools/models/ipautoban.php',
			'administrator/components/com_admintools/models/ipbl.php',
			'administrator/components/com_admintools/models/ipwl.php',
			'administrator/components/com_admintools/models/log.php',
			'administrator/components/com_admintools/tables/badwords.php',
			'administrator/components/com_admintools/tables/base.php',
			'administrator/components/com_admintools/tables/customperms.php',
			'administrator/components/com_admintools/tables/redirs.php',
			'administrator/components/com_admintools/tables/wafexceptions.php',
			'administrator/components/com_admintools/views/badwords/view.html.php',
			'administrator/components/com_admintools/views/base.view.html.php',

			'administrator/components/com_jadmintools/fof/LICENSE.txt',
			'administrator/components/com_jadmintools/fof/controller.php',
			'administrator/components/com_jadmintools/fof/dispatcher.php',
			'administrator/components/com_jadmintools/fof/index.html',
			'administrator/components/com_jadmintools/fof/inflector.php',
			'administrator/components/com_jadmintools/fof/input.php',
			'administrator/components/com_jadmintools/fof/model.php',
			'administrator/components/com_jadmintools/fof/query.abstract.php',
			'administrator/components/com_jadmintools/fof/query.element.php',
			'administrator/components/com_jadmintools/fof/query.mysql.php',
			'administrator/components/com_jadmintools/fof/query.mysqli.php',
			'administrator/components/com_jadmintools/fof/query.sqlazure.php',
			'administrator/components/com_jadmintools/fof/query.sqlsrv.php',
			'administrator/components/com_jadmintools/fof/table.php',
			'administrator/components/com_jadmintools/fof/template.utils.php',
			'administrator/components/com_jadmintools/fof/toolbar.php',
			'administrator/components/com_jadmintools/fof/view.csv.php',
			'administrator/components/com_jadmintools/fof/view.html.php',
			'administrator/components/com_jadmintools/fof/view.json.php',
			'administrator/components/com_jadmintools/fof/view.php',

			// Joomla! update files
			'administrator/components/com_admintools/restore.php',
			'administrator/components/com_admintools/controllers/jupdate.php',
			'administrator/components/com_admintools/models/jupdate.php',

			// CSS/JS Combination
			'plugins/system/admintools/admintools/cssmin.php',
		),
		'folders' => array(
			'administrator/components/com_admintools/views/acl',
			'administrator/components/com_admintools/views/ipautoban',
			'administrator/components/com_admintools/views/ipbl',
			'administrator/components/com_admintools/views/ipwl',
			'administrator/components/com_admintools/views/log',

			// Bad behaviour integration
			'plugins/system/admintools/admintools/badbehaviour',

			// Joomla! update files
			'administrator/components/com_admintools/classes',
			'administrator/components/com_admintools/views/jupdate',
		)
	);

	/**
	 * A list of scripts to be copied to the "cli" directory of the site
	 *
	 * @var   array
	 */
	protected $cliScriptFiles = array(
		'admintools-filescanner.php',
		'admintools-update.php',
		'admintools-dbrepair.php',
	);

	/**
	 * Post-installation message definitions for Joomla! 3.2 or later.
	 *
	 * This array contains the message definitions for the Post-installation Messages component added in Joomla! 3.2 and
	 * later versions. Each element is also a hashed array. For the keys used in these message definitions please
	 * @see F0FUtilsInstallscript::addPostInstallationMessage
	 *
	 * @var array
	 */
	protected $postInstallationMessages = array(
		'autojupdate' => array(
			'type'					=> 'action',
			'title_key'				=> 'COM_ADMINTOOLS_POSTSETUP_LBL_AUTOJUPDATE',
			'description_key'		=> 'COM_ADMINTOOLS_POSTSETUP_DESC_AUTOJUPDATE2',
			'action_key'			=> 'COM_ADMINTOOLS_POSTSETUP_BTN_ENABLE_FEATURE',
			'language_extension'	=> 'com_admintools',
			'language_client_id'	=> '1',
			'version_introduced'	=> '3.1.2',
			'condition_file'		=> 'admin://components/com_admintools/helpers/postinstall.php',
			'condition_method'		=> 'com_admintools_postinstall_autojupdate_condition',
			'action_file'			=> 'admin://components/com_admintools/helpers/postinstall.php',
			'action'				=> 'com_admintools_postinstall_autojupdate_action',
		),
		'accept_license' => array(
			'type'					=> 'message',
			'title_key'				=> 'COM_ADMINTOOLS_POSTSETUP_LBL_ACCEPTLICENSE',
			'description_key'		=> 'COM_ADMINTOOLS_POSTSETUP_DESC_ACCEPTLICENSE',
			'action_key'			=> 'COM_ADMINTOOLS_POSTSETUP_BTN_I_CONFIRM_THIS',
			'language_extension'	=> 'com_admintools',
			'language_client_id'	=> '1',
			'version_introduced'	=> '3.1.2'
		),
		'accept_support' => array(
			'type'					=> 'message',
			'title_key'				=> 'COM_ADMINTOOLS_POSTSETUP_LBL_ACCEPTSUPPORT',
			'description_key'		=> 'COM_ADMINTOOLS_POSTSETUP_DESC_ACCEPTSUPPORT',
			'action_key'			=> 'COM_ADMINTOOLS_POSTSETUP_BTN_I_CONFIRM_THIS',
			'language_extension'	=> 'com_admintools',
			'language_client_id'	=> '1',
			'version_introduced'	=> '3.1.2'
		),
	);


	/**
	 * Runs after install, update or discover_update
	 *
	 * @param string     $type install, update or discover_update
	 * @param JInstaller $parent
	 */
	function postflight($type, $parent)
	{
        /** @var AdmintoolsModelStats $model */
		$this->isPaid = is_dir($parent->getParent()->getPath('source') . '/plugins/system/admintools/admintools/pro.php');

		if (!$this->isPaid)
		{
			unset($this->postInstallationMessages['autojupdate']);
		}

        // Let's install common tables
        $model = F0FModel::getTmpInstance('Stats', 'AdmintoolsModel');

        if(method_exists($model, 'checkAndFixCommonTables'))
        {
            $model->checkAndFixCommonTables();
        }

		parent::postflight($type, $parent);
	}

	/**
	 * Renders the post-installation message
	 */
	function renderPostInstallation($status, $fofInstallationStatus, $strapperInstallationStatus, $parent)
	{
		?>
		<div style="margin: 1em; font-size: 14pt; background-color: #fffff9; color: black">
			You can download translation files <a href="http://cdn.akeebabackup.com/language/admintools/index.html">directly
				from our CDN page</a>.
		</div>
		<img src="<?php echo rtrim(JURI::base(), '/') ?>/../media/com_admintools/images/admintools-48.png" width="48"
			 height="48" alt="Admin Tools" align="right"/>

		<h2>Admin Tools Installation Status</h2>

		<?php
		parent::renderPostInstallation($status, $fofInstallationStatus, $strapperInstallationStatus, $parent);

        /** @var AdmintoolsModelStats $model */
        $model  = F0FModel::getTmpInstance('Stats', 'AdmintoolsModel');

        if(method_exists($model, 'collectStatistics'))
        {
            $iframe = $model->collectStatistics(true);

            if($iframe)
            {
                echo $iframe;
            }
        }
	}

	protected function renderPostUninstallation($status, $parent)
	{
		?>
		<h2>Admin Tools Uninstallation Status</h2>
		<?php
		parent::renderPostUninstallation($status, $parent);
	}
}