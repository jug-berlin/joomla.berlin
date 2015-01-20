<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @since     3.4
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use Akeeba\Engine\Postproc\Connector\Amazons3;

/**
 * S3 Import view - Model
 */
class AkeebaModelS3imports extends F0FModel
{
	public function getS3Credentials()
	{
		$config         = Factory::getConfiguration();
		$defS3AccessKey = $config->get('engine.postproc.s3.accesskey', '');
		$defS3SecretKey = $config->get('engine.postproc.s3.privatekey', '');

		$accessKey = JFactory::getApplication()
							 ->getUserStateFromRequest('com_akeeba.s3access', 's3access', $defS3AccessKey, 'raw');
		$secretKey = JFactory::getApplication()
							 ->getUserStateFromRequest('com_akeeba.s3secret', 's3secret', $defS3SecretKey, 'raw');
		$bucket    = JFactory::getApplication()->getUserStateFromRequest('com_akeeba.bucket', 's3bucket', '', 'raw');
		$folder    = JFactory::getApplication()->getUserStateFromRequest('com_akeeba.folder', 'folder', '', 'raw');
		$file      = JFactory::getApplication()->getUserStateFromRequest('com_akeeba.file', 'file', '', 'raw');
		$part      = JFactory::getApplication()->getUserStateFromRequest('com_akeeba.s3import.part', 'part', -1, 'int');
		$frag      = JFactory::getApplication()->getUserStateFromRequest('com_akeeba.s3import.frag', 'frag', -1, 'int');


		$this->setState('s3access', $accessKey);
		$this->setState('s3secret', $secretKey);
		$this->setState('s3bucket', $bucket);
		$this->setState('folder', $folder);
		$this->setState('file', $file);
		$this->setState('part', $part);
		$this->setState('frag', $frag);
	}

	public function setS3Credentials($accessKey, $secretKey)
	{
		$this->setState('s3access', $accessKey);
		$this->setState('s3secret', $secretKey);

		JFactory::getApplication()->setUserState('com_akeeba.s3access', $accessKey);
		JFactory::getApplication()->setUserState('com_akeeba.s3secret', $secretKey);
	}

	private function _getS3Object()
	{
		static $s3 = null;

		if ( !is_object($s3))
		{
			$s3Access = $this->getState('s3access');
			$s3Secret = $this->getState('s3secret');
			$s3       = Amazons3::getInstance($s3Access, $s3Secret, false);
		}

		return $s3;
	}

	private function _hasAdequateInformation($checkBucket = true)
	{
		$s3access = $this->getState('s3access');
		$s3secret = $this->getState('s3secret');
		$s3bucket = $this->getState('s3bucket');

		$check = !empty($s3access) && !empty($s3secret);

		if ($checkBucket)
		{
			$check = $check && !empty($s3bucket);
		}

		return $check;
	}

	public function getBuckets()
	{
		$buckets = null;

		if ( !is_array($buckets))
		{
			$buckets = array();
			if ($this->_hasAdequateInformation(false))
			{
				$s3      = $this->_getS3Object();
				$buckets = Amazons3::listBuckets(false);
			}
		}

		return $buckets;
	}

	public function getContents()
	{
		$folders = null;
		$files   = null;
		$root    = $this->getState('folder', '/');

		if ( !is_array($folders) || !is_array($files))
		{
			$folders = array();
			if ($this->_hasAdequateInformation())
			{
				$s3  = $this->_getS3Object();
				$raw = Amazons3::getBucket($this->getState('s3bucket'), $root, null, null, '/', true);

				if ($raw)
				{
					foreach ($raw as $name => $record)
					{
						if (substr($name, -8) == '$folder$')
						{
							continue;
						}

						if (array_key_exists('name', $record))
						{
							$extension = substr($name, -4);
							if ( !in_array($extension, array('.zip', '.jpa')))
							{
								continue;
							}
							$files[$name] = $record;
						}
						elseif (array_key_exists('prefix', $record))
						{
							$folders[$name] = $record;
						}
					}
				}
			}
		}

		return array(
			'files'   => $files,
			'folders' => $folders,
		);
	}

	public function getBucketsDropdown()
	{
		$options   = array();
		$buckets   = $this->getBuckets();
		$options[] = JHtml::_('select.option', '', JText::_('S3IMPORT_LABEL_SELECTBUCKET'));
		if ( !empty($buckets))
		{
			foreach ($buckets as $b)
			{
				$options[] = JHtml::_('select.option', $b, $b);
			}
		}

		$selected = $this->getState('s3bucket', '');

		return JHtml::_('select.genericlist', $options, 's3bucket', array(), 'value', 'text', $selected);
	}

	public function getCrumbs()
	{
		$folder = JFactory::getApplication()->getUserStateFromRequest('com_akeeba.folder', 'folder', '', 'raw');
		if ( !empty($folder))
		{
			$folder = rtrim($folder, '/');
			$crumbs = explode('/', $folder);
		}
		else
		{
			$crumbs = array();
		}

		return $crumbs;
	}

	public function downloadToServer()
	{
		if ( !$this->_hasAdequateInformation())
		{
			$this->setError(JText::_('S3IMPORT_ERR_NOTENOUGHINFO'));

			return false;
		}

		// Gather the necessary information to perform the download
		$part           = JFactory::getApplication()->getUserState('com_akeeba.s3import.part', -1);
		$frag           = JFactory::getApplication()->getUserState('com_akeeba.s3import.frag', -1);
		$remoteFilename = $this->getState('file', '');

		$s3 = $this->_getS3Object();

		// Get the number of parts and total size from the session, or –if not there– fetch it
		$totalparts = JFactory::getApplication()->getUserState('com_akeeba.s3import.totalparts', -1);
		$totalsize  = JFactory::getApplication()->getUserState('com_akeeba.s3import.totalsize', -1);
		if (($totalparts < 0) || (($part < 0) && ($frag < 0)))
		{
			$filePrefix = substr($remoteFilename, 0, -3);
			$allFiles   = $s3->getBucket($this->getState('s3bucket'), $filePrefix);
			$totalsize  = 0;
			if (count($allFiles))
			{
				foreach ($allFiles as $name => $file)
				{
					$totalsize += $file['size'];
				}
			}
			JFactory::getApplication()->setUserState('com_akeeba.s3import.totalparts', count($allFiles));
			JFactory::getApplication()->setUserState('com_akeeba.s3import.totalsize', $totalsize);
			JFactory::getApplication()->setUserState('com_akeeba.s3import.donesize', 0);
			$totalparts = JFactory::getApplication()->getUserState('com_akeeba.s3import.totalparts', -1);
		}

		// Start timing ourselves
		$timer = Factory::getTimer(); // The core timer object
		$start = $timer->getRunningTime(); // Mark the start of this download
		$break = false; // Don't break the step

		while (($timer->getRunningTime() < 10) && !$break && ($part < $totalparts))
		{
			// Get the remote and local filenames
			$basename  = basename($remoteFilename);
			$extension = strtolower(str_replace(".", "", strrchr($basename, ".")));

			if ($part > 0)
			{
				$new_extension = substr($extension, 0, 1) . sprintf('%02u', $part);
			}
			else
			{
				$new_extension = $extension;
			}

			$filename        = $basename . '.' . $new_extension;
			$remote_filename = substr($remoteFilename, 0, -strlen($extension)) . $new_extension;

			// Figure out where on Earth to put that file
			$local_file = Factory::getConfiguration()
								   ->get('akeeba.basic.output_directory') . '/' . basename($remote_filename);

			// Do we have to initialize the process?
			if ($part == -1)
			{
				// Currently downloaded size
				JFactory::getApplication()->setUserState('com_akeeba.s3import.donesize', 0);
				// Init
				$part = 0;
			}

			// Do we have to initialize the file?
			if ($frag == -1)
			{
				// Delete and touch the output file
				Platform::getInstance()->unlink($local_file);
				$fp = @fopen($local_file, 'wb');
				if ($fp !== false)
				{
					@fclose($fp);
				}
				// Init
				$frag = 0;
			}

			// Calculate from and length
			$length = 1048576;
			// That's wrong: the first byte is byte 0, not byte 1!!!
			//$from = $frag * $length + 1;
			$from = $frag * $length;
			$to   = $length + $from;
			if ($from == 0)
			{
				$from = 1;
			}

			// Try to download the first frag
			$temp_file = $local_file . '.tmp';
			@unlink($temp_file);
			$required_time = 1.0;
			$result        = $s3->getObject($this->getState('s3bucket', ''), $remote_filename, $temp_file, $from, $to);
			if ( !$result)
			{
				// Failed download
				@unlink($temp_file);
				if (
				(
					(($part < $totalparts) || (($totalparts == 1) && ($part == 0))) &&
					($frag == 0)
				)
				)
				{
					// Failure to download the part's beginning = failure to download. Period.
					$this->setError(JText::_('S3IMPORT_ERR_NOTFOUND'));

					return false;
				}
				elseif ($part >= $totalparts)
				{
					// Just finished! Create a stats record.
					$multipart = $totalparts;
					$multipart--;

					$filetime = time();
					// Create a new backup record
					$record = array(
						'description'     => JText::_('DISCOVER_LABEL_IMPORTEDDESCRIPTION'),
						'comment'         => '',
						'backupstart'     => date('Y-m-d H:i:s', $filetime),
						'backupend'       => date('Y-m-d H:i:s', $filetime + 1),
						'status'          => 'complete',
						'origin'          => 'backend',
						'type'            => 'full',
						'profile_id'      => 1,
						'archivename'     => basename($remoteFilename),
						'absolute_path'   => dirname($local_file) . '/' . basename($remoteFilename),
						'multipart'       => $multipart,
						'tag'             => 'backend',
						'filesexist'      => 1,
						'remote_filename' => '',
						'total_size'      => $totalsize
					);
					$id     = null;
					$id     = Platform::getInstance()->set_or_update_statistics($id, $record, $this);

					return null;
				}
				else
				{
					// Since this is a staggered download, consider this normal and go to the next part.
					$part++;
					$frag = -1;
				}
			}

			// Add the currently downloaded frag to the total size of downloaded files
			if ($result)
			{
				clearstatcache();
				$filesize = (int)@filesize($temp_file);
				$total    = JFactory::getApplication()->getUserState('com_akeeba.s3import.donesize', 0);
				$total += $filesize;
				JFactory::getApplication()->setUserState('com_akeeba.s3import.donesize', $total);
			}

			// Successful download, or have to move to the next part.
			if ($result)
			{
				// Append the file
				$fp = @fopen($local_file, 'ab');
				if ($fp === false)
				{
					// Can't open the file for writing
					@unlink($temp_file);
					$this->setError(JText::_('S3IMPORT_ERR_CANTWRITE'));

					return false;
				}
				$tf = fopen($temp_file, 'rb');
				while ( !feof($tf))
				{
					$data = fread($tf, 262144);
					fwrite($fp, $data);
				}
				fclose($tf);
				fclose($fp);
				@unlink($temp_file);

				$frag++;
			}

			// Advance the frag pointer and mark the end
			$end = $timer->getRunningTime();

			// Do we predict that we have enough time?
			$required_time = max(1.1 * ($end - $start), $required_time);
			if ($required_time > (10 - $end + $start))
			{
				$break = true;
			}
			$start = $end;
		}

		// Pass the id, part, frag in the request so that the view can grab it
		$this->setState('part', $part);
		$this->setState('frag', $frag);
		JFactory::getApplication()->setUserState('com_akeeba.s3import.part', $part);
		JFactory::getApplication()->setUserState('com_akeeba.s3import.frag', $frag);

		if ($part >= $totalparts)
		{
			// Just finished! Create a new backup record
			$record = array(
				'description'     => JText::_('DISCOVER_LABEL_IMPORTEDDESCRIPTION'),
				'comment'         => '',
				'backupstart'     => date('Y-m-d H:i:s'),
				'backupend'       => date('Y-m-d H:i:s', time() + 1),
				'status'          => 'complete',
				'origin'          => 'backend',
				'type'            => 'full',
				'profile_id'      => 1,
				'archivename'     => basename($remoteFilename),
				'absolute_path'   => dirname($local_file) . '/' . basename($remoteFilename),
				'multipart'       => $totalparts,
				'tag'             => 'backend',
				'filesexist'      => 1,
				'remote_filename' => '',
				'total_size'      => $totalsize
			);
			$id     = null;
			$id     = Platform::getInstance()->set_or_update_statistics($id, $record, $this);

			return null;
		}

		return true;
	}
}