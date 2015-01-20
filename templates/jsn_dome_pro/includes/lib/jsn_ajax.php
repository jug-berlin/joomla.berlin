<?php
/*------------------------------------------------------------------------
# JSN Template Framework
# ------------------------------------------------------------------------
# author    JoomlaShine.com Team
# copyright Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
# Websites: http://www.joomlashine.com
# Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
# @license - GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
# @version $Id: jsn_ajax.php 13522 2012-06-25 08:50:17Z ngocpm $
-------------------------------------------------------------------------*/

defined( '_JEXEC' ) or die( 'Restricted access' );

class JSNAjax
{
	var $_template_folder_path  = '';
	var $_template_folder_name 	= '';
	var $_obj_utils				= null;
	var $_template_edition 		= '';
	var $_template_version 		= '';
	var $_template_name 		= '';
	var $_template_copyright	= '';
	var $_template_author		= '';
	var $_template_author_url	= '';
	var $_product_info_url		= '';

	function JSNAjax()
	{
		$this->_setPhysicalTmplInfo();
		require_once($this->_template_folder_path. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_utils.php');
		require_once($this->_template_folder_path. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_sampledata_helper.php');
		$this->_setUtilsInstance();
		$this->_setTmplInfo();
	}

	/**
	 *
	 * Initialize instance of JSNUtils class
	 */
	function _setUtilsInstance()
	{
		$this->_obj_utils = JSNUtils::getInstance();
	}

	/**
	 * Initialize Physical template information variable
	 *
	 */
	function _setPhysicalTmplInfo()
	{
		$template_name 					= explode(DIRECTORY_SEPARATOR, str_replace(array('\includes\lib', '/includes/lib'), '', dirname(__FILE__)));
		$template_name 					= $template_name [count( $template_name ) - 1];
		$path_base 						= str_replace(DIRECTORY_SEPARATOR."templates". DIRECTORY_SEPARATOR .$template_name. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib', "", dirname(__FILE__));
		$this->_template_folder_name    = $template_name;
		$this->_template_folder_path 	= $path_base . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $template_name;
	}

	/**
	 * Initialize template information variable
	 *
	 */
	function _setTmplInfo()
	{
		$result 				 	= $this->_obj_utils->getTemplateDetails();
		$manifest_cache				= $this->_obj_utils->getTemplateManifestCache();
		$manifest_cache				= json_decode($manifest_cache);
		$this->_template_edition 	= $result->edition;
		$this->_template_version 	= $manifest_cache->version;
		$this->_template_name 		= $result->name;
		$this->_template_copyright 	= $result->copyright;
		$this->_template_author 	= $result->author;
		$this->_template_author_url = $result->authorUrl;
		$template_name	  			= JString::strtolower($this->_template_name);
		$exploded_template_name 	= explode('_', $template_name);
		$template_name				= @$exploded_template_name[0].'-'.@$exploded_template_name[1];
		$this->_product_info_url	= JSN_TEMPLATE_INFO_URL;
	}

	/**
	 * Check cache folder writable or not
	 *
	 */
	function checkCacheFolder()
	{
		$cache_folder   = JRequest::getVar('cache_folder');
		$isDir 			= is_dir($cache_folder);
		$isWritable 	= is_writable($cache_folder);
		echo json_encode(array('isDir' => $isDir, 'isWritable' => $isWritable));
	}

	function checkFolderPermission($for = 'sampledata')
	{
		if ($for == 'sampledata')
		{
			$sdHelperInstance = new JSNSampleDataHelper();
			$failedList = $sdHelperInstance->checkFolderPermission();

			if (count($failedList) > 0)
			{
				echo json_encode(array('permission' => false, 'folders' => $failedList));
			}
			else
			{
				echo json_encode(array('permission' => true));
			}
		}

		exit();
	}

	/**
	 * Check template's latest version from JoomlaShine server
	 */
	function checkVersion()
	{
		$session = JFactory::getSession();
		$templateVersionSesId = md5('template_version_' . strtolower($this->_template_name));

		/* Template identified_name will be something like: tpl_dome */
		$exploded_template_name	= explode('_', JString::strtolower($this->_template_name));
		$identified_name		= 'tpl_'.$exploded_template_name[1];

		$latestVersion = $this->_obj_utils->getLatestProductVersion($identified_name, 'template');

		if($latestVersion === false)
		{
			echo json_encode(array('connection' => false, 'version' => ''));
		}
		else
		{
			echo json_encode(array('connection' => true, 'version' => $latestVersion));
			$session->set($templateVersionSesId, $latestVersion, 'jsntemplatesession');
		}
	}

	/**
	 * Check Files Integrity
	 */
	function checkFilesIntegrity()
	{
		require_once($this->_template_folder_path. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_checksum_integrity_comparison.php');
		$checksum 	= new JSNChecksumIntegrityComparison();
		$result 	= $checksum->compareIntegrity();

		if (is_array($result) && count($result) && (isset($result['added']) || isset($result['deleted']) || isset($result['modified'])))
		{
			if (count(@$result['added']) || count(@$result['deleted']) || count(@$result['modified']))
			{
				// Some files have been modified , added, or deleted
				echo json_encode(array('integrity' => 1));
			}
			else
			{
				// No files modification found
				echo json_encode(array('integrity' => 0));
			}
		}
		else
		{
			// The checksum file is missing or empty
			echo json_encode(array('integrity' => 2));
		}
	}

	function initialDownloadSampleData ()
	{
		$tmpPath = JPATH_ROOT . DIRECTORY_SEPARATOR . 'tmp';

		$obj_sampledata_helper = new JSNSampleDataHelper();
		$obj_sampledata_helper->setSampleDataURL();
		$session = JFactory::getSession();
		$processList = $session->get('jsn-download-process-list', array(), 'jsntemplatesession');

		// Generate new key file
		$process = md5(uniqid() . microtime(true));
		$info = array(
			'savePath' => $tmpPath,
			'saveName' => $process . '.zip',
			'styleId'  => JRequest::getInt('template_style_id', 0, 'GET'),
			'fileUrl'  => JSN_SAMPLE_DATA_FILE_URL
		);

		$processList[] = $process;
		$session->set('jsn-download-process-list', $processList, 'jsntemplatesession');

		// Save information to file
		file_put_contents($tmpPath . DIRECTORY_SEPARATOR . $process . '.key', json_encode($info));

		// Send key name to the client
		echo json_encode(array('key' => $process));
	}

	function initialDownloadPackage ()
	{
		$session            = JFactory::getSession();
		$sdExtSesId         = md5('exts_info_'.strtolower($this->_template_name));
		$sdExtInstallSesId  = md5('exts_to_install_'.strtolower($this->_template_name));
		$sessionExtFailedId = md5('exts_failed_install_'.strtolower($this->_template_name));
		$sdFileSesId        = md5('sample_data_file_'.strtolower($this->_template_name));

		$extInfoArray    = $session->get($sdExtSesId, array(), 'jsntemplatesession');
		$flatInstallExts = $session->get($sdExtInstallSesId, array(), 'jsntemplatesession');

		$sdHelperInstance = new JSNSampleDataHelper();

		/* Get the submitted valirables */
		$extName = JRequest::getVar('ext_name');

		if (array_key_exists($extName, $extInfoArray))
		{
			$extInfo        = $extInfoArray[$extName];
			$installResult  = true;
			$toContinue     = true;
			$mes            = '';
			$failedExts     = $session->get($sessionExtFailedId, array(), 'jsntemplatesession');
			$sampleDataFile = '';

			/* Download latest version */
			require_once($this->_template_folder_path. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_downloadtemplatepackage.php');
			$joomlaVersion = $this->_obj_utils->getJoomlaVersion(true);
			if ($extInfo->downloadUrl)
			{
				$link = $extInfo->downloadUrl;
			}
			else
			{
				$link = JSN_TEMPLATE_AUTOUPDATE_URL
					. '&identified_name=' . urlencode($extInfo->identifiedName)
					. '&joomla_version=2.5'
					. '&edition=free&upgrade=yes';
			}

			$tmpName = $extInfo->name . '-j' . $joomlaVersion . '.zip';
			$tmpPath = JPATH_ROOT . DIRECTORY_SEPARATOR . 'tmp';

			$processList = $session->get('jsn-download-process-list', array(), 'jsntemplatesession');
			$process = md5(uniqid() . microtime(true));
			$info = array(
				'savePath' => $tmpPath,
				'saveName' => $process . '.zip',
				'fileUrl'  => $link
			);

			$processList[] = $process;
			$session->set('jsn-download-process-list', $processList, 'jsntemplatesession');

			// Save information to file
			file_put_contents($tmpPath . DIRECTORY_SEPARATOR . $process . '.key', json_encode($info));

			// Send key name to the client
			echo json_encode(array('key' => $process));
		}
	}

	function getInstallableExtensions ()
	{
		$key = JRequest::getString('key', '');
		$keyFile = JPATH_ROOT . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $key . '.key';
		$downloadedFile = $key . '.zip';

		$link 			= '';
		$template_style_id	= JRequest::getInt('template_style_id', 0, 'GET');

		$session          = JFactory::getSession();
		$login_identifier = md5('state_login_'.strtolower($this->_template_name));
		$sdExtSesId       = md5('exts_info_'.strtolower($this->_template_name));
		$sdFileSesId      = md5('sample_data_file_'.strtolower($this->_template_name));
		$sdExtFaiedSesId  = md5('exts_failed_install_'.strtolower($this->_template_name));
		$state_login      = $session->get($login_identifier, false, 'jsntemplatesession');
		$processList      = $session->get('jsn-download-process-list', array(), 'jsntemplatesession');

		$session->clear($sdExtSesId, 'jsntemplatesession');
		$session->clear($sdExtFaiedSesId, 'jsntemplatesession');
		$session->clear($sdFileSesId, 'jsntemplatesession');
		$failedExts       = $session->get($sdExtFaiedSesId, array(), 'jsntemplatesession');

		if(!$state_login || !in_array($key, $processList)) jexit('Invalid Token');

		/* Read the xml file for the list of to-be-installed exts */
		$extsToInstall = array();

		if (JFile::exists($keyFile) && JFile::exists(JPATH_ROOT . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $downloadedFile))
		{
			/* Array with full info. of available exts */
			$extInfo = array();

			/* Array with brief info of will-be-installed exts */
			$extBriefInfo    = '';
			$extList         = '';
			$hasInstallation = false;

			$xmlReaderInstance = new JSNReadXMLFile();
			$sdHelperInstance  = new JSNSampleDataHelper();

			$unpack = $sdHelperInstance->unpackPackage($downloadedFile);

			if ($unpack)
			{
				$installExts = $xmlReaderInstance->getSampleDataFileContent($unpack, $this->_template_name, true);
				if ($installExts)
				{
					foreach ($installExts as $ext)
					{
						/* Check JSN exts, only ones with show="true" flag */
						if ($ext->author === 'joomlashine')
						{
							if ($ext->show === true)
							{
								/* Check if the extension would be installed */
								$checkResult = $sdHelperInstance->determineExtInstallation($ext);

								$extList[$ext->name]['install'] = $checkResult->toInstall;
								$extList[$ext->name]['desc'] = $ext->description;

								if ($ext->productUrl != '')
								{
									$extList[$ext->name]['url'] = '<a target="_blank" href="' . $ext->productUrl . '">' . JText::_('JSN_SAMPLE_DATA_EXT_SELECT_PRODUCT_LINK_TEXT') . '</a>';
								}

								if ($ext->productDesc != '')
								{
									$extList[$ext->name]['productDesc'] = $ext->productDesc;
								}

								/* Show appropriate message for the extensions selection: new install/upgrade */
								if ($checkResult->toInstall === true)
								{
									if ($checkResult->outdated === true)
									{
										$extList[$ext->name]['message'] = JText::_('JSN_SAMPLE_DATA_EXT_SELECT_UPDATE');
									}
									else
									{
										$extList[$ext->name]['message'] = JText::_('JSN_SAMPLE_DATA_EXT_SELECT_NEW');
									}
								}

								if (isset($ext->extDep) && count($ext->extDep))
								{
									$depList = array();
									foreach ($ext->extDep as $extDep)
									{
										if (isset($installExts[$extDep]))
										{
											$depInfo = $installExts[$extDep];
											$resultDep = $sdHelperInstance->determineExtInstallation($depInfo);

											if ($resultDep->toInstall === true)
											{
												$extList[$ext->name]['depInstall'] = true;

												$depList[$depInfo->name]['install'] = true;
												$depList[$depInfo->name]['desc'] = $depInfo->description;

												if ($resultDep->outdated === true)
												{
													$depList[$depInfo->name]['message'] = JText::_('JSN_SAMPLE_DATA_EXT_SELECT_UPDATE');
												}
												else
												{
													$depList[$depInfo->name]['message'] = JText::_('JSN_SAMPLE_DATA_EXT_SELECT_NEW');
												}

											}
										}
									}

									$extList[$ext->name]['deps'] = $depList;
								}

								/* Set a flag that there's at least one ext installation needed */
								if ($checkResult->toInstall === true || (isset($extList[$ext->name]['deps']) && count($extList[$ext->name]['deps'])) )
								{
									$hasInstallation = true;
								}
							}

							$ext->toInstall  = $checkResult->toInstall;
							$ext->exist      = $checkResult->exist;
							$ext->outdated   = $checkResult->outdated;
							$ext->proEdition = $checkResult->proEdition;

							$extInfo[$ext->name] = $ext;
						}
						else
						{
							/* Temporarily force not install sample data for 3rd-party exts like EasyBlog */
							$failedExts[$ext->name]['exist'] = false;
							$failedExts[$ext->name]['message'] = '';
						}
					}
				}

				$sdHelperInstance->deleteSampleDataFolder($unpack);
			}

			if ($hasInstallation === false)
			{
				$sdHelperInstance->determineFailedExtensions($failedExts, $extInfo);
			}

			$session->set($sdExtSesId, $extInfo, 'jsntemplatesession');
			$session->set($sdExtFaiedSesId, $failedExts, 'jsntemplatesession');
			$session->set($sdFileSesId, $downloadedFile, 'jsntemplatesession');

			echo json_encode(array('download' => true, 'sampleDataFile'=> $downloadedFile, 'connection' => true, 'hasInstallation' => $hasInstallation, 'exts' => $extList));
			return;
		}
		else
		{
			echo json_encode(array('download' => false, 'sampleDataFile'=> '', 'message' => JText::_('JSN_SAMPLE_DATA_PACKAGE_FILE_NOT_FOUND'), 'connection' => true));
			return;
		}
	}

	function selectExtensions()
	{
		$session           = JFactory::getSession();
		$sdExtSesId        = md5('exts_info_'.strtolower($this->_template_name));
		$sdExtInstallSesId = md5('exts_to_install_'.strtolower($this->_template_name));

		$extInfoArray = $session->get($sdExtSesId, array(), 'jsntemplatesession');
		$extInfoKeys = array_keys($extInfoArray);

		$installExts     = array();
		$flatInstallExts = array();
		$notSelected = array();

		$exts = JRequest::getVar('exts', array());
		$totalExt = count($exts);
		if ($totalExt > 0)
		{
			for ($i = 0; $i < $totalExt; $i++)
			{
				$isLastExt = false;
				$ext = $exts[$i];

				if ($i == ($totalExt-1))
				{
					$isLastExt = true;
				}

				if (array_key_exists($ext, $extInfoArray))
				{
					$installExts[$ext]['desc'] = $extInfoArray[$ext]->description;
					$flatInstallExts[$ext]['childOf'] = '';
					$flatInstallExts[$ext]['isLastExt'] = $isLastExt;

					if (isset($extInfoArray[$ext]->extDep))
					{
						$extDeps = array();
						foreach ($extInfoArray[$ext]->extDep as $extDep)
						{
							/**
							 * Ensure that the dependency is actually choosen (in posted array $ext)
							 * $desPostName is in format: parentId_depId
							 */
							$depPostName = $ext . '_' . $extDep;

							if (array_key_exists($extDep, $extInfoArray) && in_array($depPostName, $exts))
							{
								$extDeps[$extDep]['desc'] = $extInfoArray[$extDep]->description;
								$flatInstallExts[$extDep]['childOf'] = $ext;
								$flatInstallExts[$extDep]['isLastExt'] = false;
							}
						}
						$installExts[$ext]['deps'] = $extDeps;
					}
				}
				else
				{
					/* Check that if it is a dependency of an installed extension */
					$extIdParts = explode('_', $ext);

					if (array_key_exists($extIdParts[0], $extInfoArray)
						&& !array_key_exists($extIdParts[0], $installExts))
					{
						$parentExtDeps = $extInfoArray[$extIdParts[0]]->extDep;

						if (in_array($extIdParts[1], $parentExtDeps))
						{
							$installExts[$extIdParts[1]]['desc'] = $extInfoArray[$extIdParts[1]]->description;

							$flatInstallExts[$extIdParts[1]]['childOf']   = '';
							$flatInstallExts[$extIdParts[1]]['isLastExt'] = $isLastExt;
						}
					}
				}
			}
		}

		$sessionExtFailedId = md5('exts_failed_install_'.strtolower($this->_template_name));
		$failedExts         = $session->get($sessionExtFailedId, array(), 'jsntemplatesession');

		$sdHelperInstance  = new JSNSampleDataHelper();
		$sdHelperInstance->determineFailedExtensions($failedExts, $extInfoArray, $exts);

		$session->set($sdExtInstallSesId, $flatInstallExts, 'jsntemplatesession');
		$session->set($sessionExtFailedId, $failedExts, 'jsntemplatesession');

		$sdFileSesId = md5('sample_data_file_'.strtolower($this->_template_name));
		$sdFileName = $session->get($sdFileSesId, '', 'jsntemplatesession');

		$resultArray = array(
					'result'         => true,
					'exts'           => $installExts,
					'sampleDataFile' => $sdFileName
			);

		if (count($flatInstallExts) > 0)
		{
			$flatKeys = array_keys($flatInstallExts);
			$resultArray['firstExt']  = $flatKeys[0];
			$resultArray['childOf']   = $flatInstallExts[$flatKeys[0]]['childOf'];
			$resultArray['isLastExt'] = $flatInstallExts[$flatKeys[0]]['isLastExt'];
		}

		echo json_encode($resultArray);
		exit();
	}

	/**
	 * This function is for installing extensions for sample data
	 * This function DOES NOT actually install the extension, but acts as a
	 * "proxy" to receive request from AJAX, then using HTTP Socket to send an
	 * internal request to install the extension.
	 * It is important to go this way because direct AJAX request to install
	 * the extension might get interrupted because some extensions will perform
	 * a 303 redirection after standard Joomla JInstaller process.
	 */
	function requestInstallExtension()
	{
		$session            = JFactory::getSession();
		$sdExtSesId         = md5('exts_info_'.strtolower($this->_template_name));
		$sdExtInstallSesId  = md5('exts_to_install_'.strtolower($this->_template_name));
		$sessionExtFailedId = md5('exts_failed_install_'.strtolower($this->_template_name));
		$sdFileSesId        = md5('sample_data_file_'.strtolower($this->_template_name));

		$extInfoArray    = $session->get($sdExtSesId, array(), 'jsntemplatesession');
		$flatInstallExts = $session->get($sdExtInstallSesId, array(), 'jsntemplatesession');
		$processList     = $session->get('jsn-download-process-list', array(), 'jsntemplatesession');

		$sdHelperInstance = new JSNSampleDataHelper();

		/* Get the submitted valirables */
		$extName = JRequest::getVar('ext_name');
		$key = JRequest::getVar('key');

		if (array_key_exists($extName, $extInfoArray) && in_array($key, $processList))
		{
			$extInfo        = $extInfoArray[$extName];
			$installResult  = true;
			$toContinue     = true;
			$mes            = '';
			$failedExts     = $session->get($sessionExtFailedId, array(), 'jsntemplatesession');
			$sampleDataFile = '';
			$downloadResult = $key . '.zip';

			if (!is_file(JPATH_ROOT . '/tmp/' . $downloadResult))
			{
				$installResult = false;
				$failedExts[$extInfo->name]['exist'] = $extInfo->exist;
				$mes = JText::_('JSN_SAMPLE_DATA_EXT_DOWNLOAD_FAILED');

				if ($extInfo->hasData === true)
				{
					$failedExts[$extInfo->name]['message'] = JText::sprintf('JSN_SAMPLE_DATA_WARNING_EXT_INSTALL_FAILED', $extInfo->description);
				}
			}
			else
			{
				/* If download success, send HTTP Socket request to install */
				$url = JURI::root().'index.php?template='.strtolower($this->_template_name)
					. '&tmpl=jsn_runajax&task=installExtension'
					. '&package=' . urlencode($downloadResult)
					. '&redirect=0';

				// The last argument NOFOLLOW = true means don't follow redirection
				$httpRequestInstance = new JSNHTTPSocket($url, null, null, 'get', true);
				$output              = json_decode($httpRequestInstance->socketDownload());

	            /* Assuming empty string (as redirection) returned means success */
	            if (isset($output->result) && $output->result === false)
	            {
					$installResult = false;
					if (isset($output->message) && $output->message != '')
					{
						$mes = $output->message;
					}
					else
					{
						$mes = JText::_('JSN_SAMPLE_DATA_INSTALL_FAILED');
					}

					$failedExts[$extInfo->name]['exist'] = $extInfo->exist;

					/* Only show warning for the extension which actually has sample data */
					if ($extInfo->hasData === true)
					{
						$failedExts[$extInfo->name]['message'] = JText::sprintf('JSN_SAMPLE_DATA_WARNING_EXT_INSTALL_FAILED', $extInfo->description);
					}
	            }
	            else
	            {
	            	$installResult = true;

	            	/**
	            	 * As ImageShow might not have completed the installation
	            	 * itself, so Joomla cannot remove the installation file.
	            	 * We need to delete installer package using an existing
	            	 * function of SampleData Helper.
	            	 */
	            	$sdHelperInstance->deleteSampleDataFile($downloadResult);

	            	/* Enable plugins as were not enabled by JInstaller */
	            	$sdHelperInstance->enableInstalledPlugin($extInfo->name, $extInfo->type);
	            }
			}

        	/* Change to the next extension if available */
        	if (array_key_exists($extName, $flatInstallExts))
        	{
        		unset($flatInstallExts[$extName]);
        		if (count($flatInstallExts) > 0)
        		{
					$arrayKeys = array_keys($flatInstallExts);
					$nextExt   = $arrayKeys[0];
					$childOf   = $flatInstallExts[$nextExt]['childOf'];
					$isLastExt = $flatInstallExts[$nextExt]['isLastExt'];
				}
				else
				{
					$nextExt   = '';
					$childOf   = '';
					$isLastExt = false;
        		}

        		$sampleDataFile = $session->get($sdFileSesId, '', 'jsntemplatesession');

        		$session->set($sdExtInstallSesId, $flatInstallExts, 'jsntemplatesession');
	        	$session->set($sessionExtFailedId, $failedExts, 'jsntemplatesession');

				echo json_encode(array(
						'installExt'     => $installResult,
						'extName'        => $extName,
						'message'        => $mes,
						'nextExt'        => $nextExt,
						'childOf'        => $childOf,
						'isLastExt'      => $isLastExt,
						'tocontinue'     => $toContinue,
						'sampleDataFile' => $sampleDataFile
						)
					);
        	}
		}
		exit();
	}

	function reportFailedExtension()
	{
		$session            = JFactory::getSession();
		$sdExtSesId         = md5('exts_info_'.strtolower($this->_template_name));
		$sdExtInstallSesId  = md5('exts_to_install_'.strtolower($this->_template_name));
		$sessionExtFailedId = md5('exts_failed_install_'.strtolower($this->_template_name));

		$extInfoArray    = $session->get($sdExtSesId, array(), 'jsntemplatesession');
		$flatInstallExts = $session->get($sdExtInstallSesId, array(), 'jsntemplatesession');
		$failedExts      = $session->get($sessionExtFailedId, array(), 'jsntemplatesession');

		/* Mark all remaining exts as failed ones to be excluded from sample data */
		foreach ($flatInstallExts as $extName => $extAttrs)
		{
			$extInfo = $extInfoArray[$extName];

			$failedExts[$extInfo->name]['exist'] = $extInfo->exist;
			if ($extInfo->hasData)
			{
				$failedExts[$extInfo->name]['message'] = JText::sprintf('JSN_SAMPLE_DATA_WARNING_EXT_INSTALL_FAILED', $extInfo->description);
			}
		}

		$session->set($sessionExtFailedId, $failedExts, 'jsntemplatesession');

		echo json_encode(array('result' => true));
	}

	function installExtension()
	{
		$sdHelperInstance = new JSNSampleDataHelper();

		$packageName = JRequest::getVar('package', '', 'GET');
		$packagePath = JPATH_ROOT. DIRECTORY_SEPARATOR .'tmp'. DIRECTORY_SEPARATOR .$packageName;

		if (JFile::exists($packagePath))
		{
			jimport('joomla.installer.helper');
			$installer = JInstaller::getInstance();

			$tmpExtPackage = JPATH_ROOT. DIRECTORY_SEPARATOR .'tmp'. DIRECTORY_SEPARATOR .$packageName;

			$unpack = JInstallerHelper::unpack($packagePath);
			$installResult = $installer->install($unpack['dir']);
			JInstallerHelper::cleanupInstall($packagePath, $unpack['dir']);

			echo json_encode(array('result' => $installResult));
		}
		else
		{
			$mes = JText::sprintf('JSN_SAMPLE_DATA_EXT_PACKAGE_NOT_FOUND', $packageName);
			echo json_encode(array('result' => false, 'message' => $mes));
		}
		exit();
	}

	function installSampleData()
	{
		$defaultTimeout = ini_get('max_execution_time');
		@set_time_limit(360);

		$file_name		= JRequest::getVar('file_name');
		$foldertmp		= JPATH_ROOT. DIRECTORY_SEPARATOR .'tmp';
		$folderbackup	= $this->_template_folder_path. DIRECTORY_SEPARATOR .'backups';
		$link 			= '';
		$errors			= array();
		$isNotExisted	= array();

		$obj_read_xml_file 	= new JSNReadXMLFile();

		$session 			= JFactory::getSession();
		$login_identifier 	= md5('state_login_'.strtolower($this->_template_name));
		$sessionExtFailedId = md5('exts_failed_install_'.strtolower($this->_template_name));
		//$identifier 		= md5('state_installation_'.strtolower($this->_template_name));
		$state_login		= $session->get($login_identifier, false, 'jsntemplatesession');
		$failedExts    		= $session->get($sessionExtFailedId, array(), 'jsntemplatesession');

		if(!$state_login) jexit('Invalid Token');

		$obj_sample_data_helper			= new JSNSampleDataHelper();
		$array_non_basic_module			= $obj_sample_data_helper->getNonBasicModule();
		$array_non_basic_admin_module	= $obj_sample_data_helper->getNonBasicAdminModule();
		$array_3rd_extension_menu		= $obj_sample_data_helper->getThirdExtensionMenus();
		$domain							= $obj_sample_data_helper->getDomain();

		if (!is_writable($foldertmp))
		{
			$obj_sample_data_helper->deleteSampleDataFile($file_name);
			echo json_encode(array('download' => false, 'message' => JText::_('JSN_SAMPLE_DATA_TEMP_FOLDER_UNWRITABLE'), 'redirect_link'=>$link));
			return;
		}
		$path = $foldertmp. DIRECTORY_SEPARATOR .$file_name;
		if (!JFile::exists($path))
		{
			echo json_encode(array('install' => false, 'message' => JText::_('JSN_SAMPLE_DATA_PACKAGE_FILE_NOT_FOUND'), 'redirect_link'=>$link, 'manual'=>true));
			return;
		}
		$unpackage = $obj_sample_data_helper->unpackPackage($file_name);

		if ($unpackage)
		{
			$sample_xml_data = $obj_read_xml_file->getSampleDataFileContent($unpackage, $this->_template_name );
			$installed_data = $sample_xml_data['installed_data'];

			if ($installed_data && is_array($installed_data))
			{
				if (trim($sample_xml_data['version']) != trim($this->_template_version))
				{
					$obj_sample_data_helper->deleteSampleDataFile($file_name);
					$obj_sample_data_helper->deleteSampleDataFolder($unpackage);
					echo json_encode(array('install' => false, 'message' => JText::_('JSN_SAMPLE_DATA_OUTDATED_PRODUCT'), 'redirect_link'=>$link));
					return;
				}
				else
				{
					foreach ($failedExts as $key => $value) {
						unset($installed_data[$key]);
						if (isset($value['message']) && $value['message'] != '')
						{
							$errors[] = $value['message'];
						}

						/* Only for an extension that doesn't exist, not can-not-be upgraded one */
						if (isset($value['exist']) && $value['exist'] === false)
						{
							$isNotExisted[] = $key;
						}
					}

					$obj_backup	= JSNBackup::getInstance();
					if (is_writable($folderbackup))
					{
						$backup_file_name = $obj_backup->executeBackup($this->_template_folder_path. DIRECTORY_SEPARATOR .'backups',$domain, $installed_data);
					}
					else
					{
						$backup_file_name = '';
					}

					$obj_sample_data_helper->deleteNonBasicAdminModule();
					$obj_sample_data_helper->installSampleData($installed_data);

					/* Clean up records of non-installed extensions */
					if (count($isNotExisted))
					{
						$asset = JTable::getInstance('Asset');

						foreach ($isNotExisted as $element)
						{
							$element = 'com_' . $element;

							/* Delete assets record */
							if ($asset->loadByName($element))
							{
								$asset->delete();
							}

							/* Disable menu items (if available) */
							$obj_sample_data_helper->disableMenuItems($element);
						}
					}

					$obj_sample_data_helper->runQueryNonBasicModule($array_non_basic_module);
					$obj_sample_data_helper->runQueryNonBasicModule($array_non_basic_admin_module, true);
					$obj_sample_data_helper->restoreThirdExtensionMenus($array_3rd_extension_menu);
					$obj_sample_data_helper->rebuildMenu();
					$obj_sample_data_helper->copyContentFromFilesFolder($unpackage);
					$obj_sample_data_helper->deleteSampleDataFolder($unpackage);
					$obj_sample_data_helper->setDefaultTemplate(strtolower($this->_template_name));
					$obj_sample_data_helper->deleteSampleDataFile($file_name);

					$session->set($login_identifier, false, 'jsntemplatesession');
					echo json_encode(array('install' => true, 'message'=>'', 'redirect_link'=>$link, 'warnings'=>$errors, 'backup_file_name'=>$backup_file_name));

					$session->clear($sessionExtFailedId, 'jsntemplatesession');
					return;
				}
			}
			else
			{
				$obj_sample_data_helper->deleteSampleDataFile($file_name);
				echo json_encode(array('install' => false, 'message' => JText::_('JSN_SAMPLE_DATA_INVALID'), 'redirect_link'=>$link, 'manual'=>true));
				return;
			}
		}
		else
		{
			$obj_sample_data_helper->deleteSampleDataFile($file_name);
			echo json_encode(array('install' => false, 'message' => JText::_('JSN_SAMPLE_DATA_UNABLE_EXTRACT_PACKAGE'), 'redirect_link'=>$link));
			exit();
		}

		return;
	}

	function backupModifiedFile()
	{
		$session 			= JFactory::getSession();
		$login_identifier 	= md5('state_update_login_'.strtolower($this->_template_name));
		$state_login		= $session->get($login_identifier, false, 'jsntemplatesession');

		if(!$state_login) jexit('Invalid Token');

		$obj_updater_helper	= new JSNUpdaterHelper();
		$backup_result 		= $obj_updater_helper->backupModifiedFile();

		if ($backup_result)
		{
			echo json_encode(array('backup' => true, 'backup_file_name'=>(is_string($backup_result))?$backup_result:''));
			exit();
		}
		else
		{
			$obj_updater_helper->destroySession();
			echo json_encode(array('backup' => false, 'backup_file_name'=>''));
			exit();
		}
	}

	function manualUpdateTemplate()
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.utilities.xmlelement');
		$session 					= JFactory::getSession();
		$login_identifier 			= md5('state_update_login_'.strtolower($this->_template_name));
		$state_login				= $session->get($login_identifier, false, 'jsntemplatesession');
		$modified_file_identifier 	= md5('state_modified_file_'.strtolower($this->_template_name));
		$modified_files				= $session->get($modified_file_identifier, array(), 'jsntemplatesession');

		if(!$state_login) jexit('Invalid Token');

		$obj_updater_helper	= new JSNUpdaterHelper();
		$extract_dir 		= JRequest::getCmd('extract_dir');
		$backup_file 		= JRequest::getCmd('backup_file');
		$package_name 		= JRequest::getVar('package_name');
		$package_path		= JPATH_ROOT. DIRECTORY_SEPARATOR .'tmp'. DIRECTORY_SEPARATOR .$package_name;
		$extract_dir_path 	= JPATH_ROOT. DIRECTORY_SEPARATOR .'tmp'. DIRECTORY_SEPARATOR .$extract_dir;
		$files				= $obj_updater_helper->compareChecksumFile($extract_dir_path);

		$installer 			= JSNInstaller::getInstance();
		$strXML				= '';
		$tmpArray			= array();
		$deleted_files		= array();
		$new_manifest 		= $obj_updater_helper->findManifest($extract_dir_path);
		$tmp_new_version  	= $new_manifest->version;
		$new_version		= $tmp_new_version->data();
		$old_version		= $this->_template_version;
		$compare_version	= $this->_obj_utils->compareVersion($new_version, $old_version);

		if ($compare_version == 0)
		{
			$tmpArray = array_merge($tmpArray, $modified_files);
		}
		else
		{
			if (isset($files['added']))
			{
				$tmpArray = array_merge($tmpArray, $files['added']);
			}

			if (isset($files['modified']))
			{
				$tmpArray = array_merge($tmpArray, $files['modified']);
			}

			if (isset($files['deleted']))
			{
				$deleted_files	= $files['deleted'];
			}
		}

		if (isset($files['modified']) && count($files['modified']) && count($modified_files))
		{
			foreach ($files['modified'] as $item1)
			{
				foreach ($modified_files as $item2)
				{
					if ($item1 == $item2)
					{
						echo json_encode(array('update' => false, 'backup_file_name'=>'', 'message'=>'', 'from_version'=>$old_version, 'to_version'=>$new_version, 'redirect'=>true));
						return;
					}
				}
			}
		}

		$tmpArray  = array_merge($tmpArray, array('template.checksum'));
		if (count($tmpArray))
		{
			$strXML = '<?xml version="1.0" encoding="UTF-8" ?><extension><files>';
			foreach ($tmpArray as $value)
			{
				$strXML .= '<filename>'.$value.'</filename>';
			}
			foreach ($files['modified'] as $value)
			{
				$strXML .= '<filename>'.$value.'</filename>';
			}
			$strXML .= '</files></extension>';
		}

		if (!empty($strXML))
		{
			$new_tmp_xml = new JXMLElement($strXML);
		}
		else
		{
			$new_tmp_xml = null;
		}
		if (!$installer->install($extract_dir_path, $new_tmp_xml, $deleted_files))
		{
			echo json_encode(array('update' => false, 'message' => JText::_('JSN_UPDATE_MANUAL_INSTALL_FAILED'),'backup_file_name'=>'', 'from_version'=>$old_version, 'to_version'=>$new_version, 'redirect'=>false));
		}
		else
		{
			echo json_encode(array('update' => true, 'backup_file_name'=>$backup_file, 'from_version'=>$old_version, 'to_version'=>$new_version, 'redirect'=>false));
		}
		$obj_updater_helper->destroySession();
		if (is_dir($extract_dir_path))
		{
			JFolder::delete($extract_dir_path);
		}
		if (is_file($package_path))
		{
			JFile::delete($package_path);
		}
		exit();
	}

	function initialDownloadTemplatePackage ()
	{
		$session 					= JFactory::getSession();
		$post						= JRequest::get('post');
		$login_identifier 			= md5('state_update_login_'.strtolower($this->_template_name));
		$state_login				= $session->get($login_identifier, false, 'jsntemplatesession');
		$link						= '';
		$objUtils			= JSNUtils::getInstance();
		$objReadXMLFile 	= new JSNReadXMLFile();

		if(!$state_login) jexit('Invalid Token');

		$tmpPath 	= JPATH_ROOT. DIRECTORY_SEPARATOR .'tmp';
		$templateManifest	= $objReadXMLFile->getTemplateManifestFileInformation();

		$post['customer_password'] 	   = JRequest::getString('customer_password', '', 'post', JREQUEST_ALLOWRAW);
		$link = JSN_TEMPLATE_AUTOUPDATE_URL
			.'&identified_name='.urlencode(str_replace('jsn', 'tpl', strtolower($templateManifest['name'])))
			.'&based_identified_name='
			.'&edition='.urlencode($templateManifest['edition'] != '' && $templateManifest['edition'] != 'free' ? 'pro '.$templateManifest['edition'] : $edition = 'free')
			.'&joomla_version=2.5'
			.'&username='.urlencode($post['customer_username'])
			.'&password='.urlencode($post['customer_password'])
			.'&product_version='.urlencode($templateManifest['version'])
			.'&upgrade=yes';

		$processList = $session->get('jsn-download-process-list', array(), 'jsntemplatesession');
		$process = md5(uniqid() . microtime(true));
		$info = array(
			'savePath' => $tmpPath,
			'saveName' => $process . '.zip',
			'fileUrl'  => $link
		);

		$processList[] = $process;
		$session->set('jsn-download-process-list', $processList, 'jsntemplatesession');

		// Save information to file
		file_put_contents($tmpPath . DIRECTORY_SEPARATOR . $process . '.key', json_encode($info));

		// Send key name to the client
		echo json_encode(array('key' => $process));
	}

	function prepareTemplatePackage()
	{
		$session = JFactory::getSession();
		$post = JRequest::get('post');
		$login_identifier = md5('state_update_login_'.strtolower($this->_template_name));
		$state_login = $session->get($login_identifier, false, 'jsntemplatesession');
		$key = JRequest::getString('key');
		if(!$state_login) jexit('Invalid Token');

		$tmp_path 	= JPATH_ROOT. DIRECTORY_SEPARATOR .'tmp';
		if (is_writable($tmp_path))
		{
			$post['customer_password'] 	= JRequest::getString('customer_password', '', 'post', JREQUEST_ALLOWRAW);
			$obj_updater_helper	= new JSNAutoUpdaterHelper();
			$result = $obj_updater_helper->downloadTemplatePackage($post);
			if ($result)
			{
				$errorCode = strtolower((string) $result);
				switch ($errorCode)
				{
					case 'err00':
						$message = JText::_('JSN_SAMPLE_DATA_LIGHTCART_RETURN_ERR00');
						break;
					case 'err01':
						$message = JText::_('JSN_SAMPLE_DATA_LIGHTCART_RETURN_ERR01');
						break;
					case 'err02':
						$message = JText::_('JSN_SAMPLE_DATA_LIGHTCART_RETURN_ERR02');
						break;
					case 'err03':
						$message = JText::_('JSN_SAMPLE_DATA_LIGHTCART_RETURN_ERR03');
						break;
					default:
						$message = '';
						break;
				}
				if ($message != '')
				{
					$obj_updater_helper->destroySession();
					echo json_encode(array('download'=>false, 'file_name'=> '', 'connection'=>true, 'message'=>$message, 'manual'=>false));
				}
				else
				{
					echo json_encode(array('download'=>true, 'file_name'=> (string) $result, 'connection'=>true, 'message'=>'', 'manual'=>false));
				}
				return;
			}
			else
			{
				if ($edition == 'free')
				{
					$templateNameParts = explode('_', strtolower($this->_template_name));
					$link = 'http://www.joomlashine.com/joomla-templates/'.$templateNameParts[0].'-'.$templateNameParts[1].'-download.html';
				}
				else
				{
					$link = 'http://www.joomlashine.com/customer-area.html';
				}
				$obj_updater_helper->destroySession();
				echo json_encode(array('download'=>false, 'file_name'=> '', 'message' => JText::sprintf('JSN_UPDATE_DOWNLOAD_FAILED', $link), 'connection'=>false, 'manual'=>true));
				return;
			}
		}
		else
		{
			$obj_updater_helper->destroySession();
			echo json_encode(array('download'=>false, 'file_name'=> '', 'message' => JText::_('JSN_UPDATE_TEMP_FOLDER_UNWRITABLE'), 'connection'=>false, 'manual'=>false));
			return;
		}
	}

	function autoUpdateTemplate()
	{
		jimport('joomla.utilities.xmlelement');
		$session 					= JFactory::getSession();
		$login_identifier 			= md5('state_update_login_'.strtolower($this->_template_name));
		$state_login				= $session->get($login_identifier, false, 'jsntemplatesession');
		$modified_file_identifier 	= md5('state_modified_file_'.strtolower($this->_template_name));
		$modified_files				= $session->get($modified_file_identifier, array(), 'jsntemplatesession');
		$tmp_path					= JPATH_ROOT. DIRECTORY_SEPARATOR .'tmp';
		if(!$state_login) jexit('Invalid Token');
		if ($this->_template_edition != '' && $this->_template_edition != 'free')
		{
			$edition = $this->_template_edition;
		}
		else
		{
			$edition = 'free';
		}

		if ($edition == 'free')
		{
			$templateNameParts = explode('_', strtolower($this->_template_name));
			$link = 'http://www.joomlashine.com/joomla-templates/'.$templateNameParts[0].'-'.$templateNameParts[1].'-download.html';
			$msg_manual_download = JText::sprintf('JSN_UPDATE_DOWNLOAD_FREE', $link);
		}
		else
		{
			$link = 'http://www.joomlashine.com/customer-area.html';
			$msg_manual_download = '<span class="jsn-red-message">Unable to download file</span>. Please try to <a href="http://www.joomlashine.com/customer-area.html" target="_blank" class="link-action">download file from Customer Area</a>, then select it:';
		}

		$package_name 				= JRequest::getCmd('package_name');
		$backup_file 				= JRequest::getCmd('backup_file');
		$package_path				= $tmp_path. DIRECTORY_SEPARATOR .$package_name;
		$obj_updater_helper			= new JSNUpdaterHelper();
		$obj_auto_updater_helper	= new JSNAutoUpdaterHelper();
		$unpack 					= $obj_auto_updater_helper->unpack($package_path);
		if ($unpack)
		{
			$files			= $obj_updater_helper->compareChecksumFile($unpack['dir']);
			$installer 		= JSNInstaller::getInstance();
			$strXML				= '';
			$tmpArray			= array();
			$deleted_files		= array();
			$new_manifest 		= $obj_updater_helper->findManifest($unpack['dir']);
			$tmp_new_version  	= $new_manifest->version;
			$new_version		= (string) $tmp_new_version;
			$old_version		= $this->_template_version;
			$compare_version	= $this->_obj_utils->compareVersion($new_version, $old_version);

			if ($compare_version == 0)
			{
				$tmpArray = array_merge($tmpArray, $modified_files);
			}
			else
			{
				if (isset($files['added']))
				{
					$tmpArray = array_merge($tmpArray, $files['added']);
				}

				if (isset($files['modified']))
				{
					$tmpArray = array_merge($tmpArray, $files['modified']);
				}

				if (isset($files['deleted']))
				{
					$deleted_files	= $files['deleted'];
				}
			}

			if (isset($files['modified']) && count($files['modified']) && count($modified_files))
			{
				foreach ($files['modified'] as $item1)
				{
					foreach ($modified_files as $item2)
					{
						if ($item1 == $item2)
						{
							echo json_encode(array('update' => false, 'backup_file_name'=>'', 'message'=>'', 'manual'=>false, 'redirect'=>true));
							return;
						}
					}
				}
			}

			$tmpArray  = array_merge($tmpArray, array('template.checksum'));
			if (count($tmpArray))
			{
				$strXML = '<?xml version="1.0" encoding="UTF-8" ?><extension><files>';
				foreach ($tmpArray as $value)
				{
					$strXML .= '<filename>'.$value.'</filename>';
				}

				if (!isset($files['modified'])) {
					$files['modified'] = array();
				}

				foreach ($files['modified'] as $value)
				{
					$strXML .= '<filename>'.$value.'</filename>';
				}
				$strXML .= '</files></extension>';
			}

			if (!empty($strXML))
			{
				$new_tmp_xml = new JXMLElement($strXML);
			}
			else
			{
				$new_tmp_xml = null;
			}

			if (!$installer->install($unpack['dir'], $new_tmp_xml, $deleted_files))
			{
				echo json_encode(array('update' => false, 'backup_file_name'=>'', 'message' => JText::sprintf('JSN_UPDATE_INSTALL_FAILED', $link), 'manual'=>true, 'redirect'=>false));
			}
			else
			{
				echo json_encode(array('update' => true, 'backup_file_name'=>$backup_file, 'message'=>'', 'manual'=>false, 'redirect'=>false));
			}
		}
		else
		{
			echo json_encode(array('update'=>false, 'backup_file_name'=>'', 'message' => JText::sprintf('JSN_UPDATE_UNPACK_FAILED', $link), 'manual'=>true, 'redirect'=>false));
		}
		$obj_updater_helper->destroySession();
		$obj_updater_helper->deleteInstallationPackage($unpack['packagefile'], $unpack['dir']);
		return;
	}
}
