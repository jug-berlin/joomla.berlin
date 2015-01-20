<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2009-2014 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @since     3.2
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;

class AkeebaModelUploads extends F0FModel
{
	/**
	 * Upload an archive part to remote storage
	 *
	 * @return bool|int
	 */
	public function upload()
	{
		$id   = $this->getState('id', -1);
		$part = $this->getState('part', -1);
		$frag = $this->getState('frag', -1);

		// Calculate the filenames
		$stat           = Platform::getInstance()->get_statistics($id);
		$local_filename = $stat['absolute_path'];
		$basename       = basename($local_filename);
		$extension      = strtolower(str_replace(".", "", strrchr($basename, ".")));

		if ($part > 0)
		{
			$new_extension = substr($extension, 0, 1) . sprintf('%02u', $part);
		}
		else
		{
			$new_extension = $extension;
		}

		$filename       = $basename . '.' . $new_extension;
		$local_filename = substr($local_filename, 0, -strlen($extension)) . $new_extension;

		// Load the post-processing engine
		Platform::getInstance()->load_configuration($stat['profile_id']);
		$config = Factory::getConfiguration();

		$session = JFactory::getSession();
		$savedEngine = $session->get('postproc_engine', null, 'akeeba');
		$engine  = null;

		if ( !empty($savedEngine) && ($frag != -1))
		{
			// If it's not the first fragment, try to revive the saved engine
			$engine      = unserialize($savedEngine);
		}
		else
		{
			$engine_name = $config->get('akeeba.advanced.postproc_engine');
			$engine      = Factory::getPostprocEngine($engine_name);
		}

		// Start uploading
		$result = $engine->processPart($local_filename);

		// Can't use switch because true == -1 but true !== -1 and we need the latter comparison
		if ($result === true)
		{
			$part++;
			$frag = 0;
		}
		elseif ($result === -1)
		{
			$frag++;
			$savedEngine = serialize($engine);
			$session->set('postproc_engine', $savedEngine, 'akeeba');
		}
		elseif ($result === false)
		{
			$warning = $engine->getWarning();
			$error   = $engine->getError();
			$this->setError(empty($warning) ? $error : $warning);

			$session->set('postproc_engine', null, 'akeeba');
			$part = -1;
			$frag = -1;

			return false;
		}

		$remote_filename = $config->get('akeeba.advanced.postproc_engine', '') . '://';
		$remote_filename .= $engine->remote_path;

		if ($part >= 0)
		{
			if ($part >= $stat['multipart'])
			{
				// Update stats with remote filename
				$data = array(
					'remote_filename' => $remote_filename
				);

				Platform::getInstance()->set_or_update_statistics($id, $data, $engine);
			}
		}

		$this->setState('id', $id);
		$this->setState('part', $part);
		$this->setState('frag', $frag);
		$this->setState('stat', $stat);
		$this->setState('remotename', $remote_filename);

		return $result;
	}
}