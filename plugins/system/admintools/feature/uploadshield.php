<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

class AtsystemFeatureUploadshield extends AtsystemFeatureAbstract
{
	protected $loadOrder = 370;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		if (!F0FPlatform::getInstance()->isFrontend())
		{
			return false;
		}

		if ($this->skipFiltering)
		{
			return false;
		}

		return ($this->cparams->getValue('uploadshield', 1) == 1);
	}

	/**
	 * Scans all uploaded files for PHP tags. This prevents uploading PHP files or crafted
	 * images with raw PHP code in them which may lead to arbitrary code execution under
	 * several common circumstances. It will also block files with null bytes in their
	 * filenames or with double extensions which include PHP in them (e.g. .php.jpg).
	 */
	public function onAfterInitialise()
	{
		// Do we have uploaded files?
		$input = $this->input->files;

		$ref = new ReflectionProperty($input, 'data');
		$ref->setAccessible(true);
		$filesHash = $ref->getValue($input);

		if (empty($filesHash))
		{
			return;
		}

		$extraInfo = '';
		foreach ($filesHash as $key => $temp_descriptor)
		{
			if (is_array($temp_descriptor) && !array_key_exists('tmp_name', $temp_descriptor))
			{
				$descriptors = $temp_descriptor;
			}
			else
			{
				$descriptors[] = $temp_descriptor;
			}

			unset($temp_descriptor);

			foreach ($descriptors as $descriptor)
			{
				$files = array();

				if (is_array($descriptor['tmp_name']))
				{
					foreach ($descriptor['tmp_name'] as $key => $value)
					{
						$files[] = array(
							'name'     => $descriptor['name'][$key],
							'type'     => $descriptor['type'][$key],
							'tmp_name' => $descriptor['tmp_name'][$key],
							'error'    => $descriptor['error'][$key],
							'size'     => $descriptor['size'][$key],
						);
					}
				}
				else
				{
					$files[] = $descriptor;
				}

				foreach ($files as $fileDescriptor)
				{
					$tempNames = $fileDescriptor['tmp_name'];
					$intendedNames = $fileDescriptor['name'];

					if (!is_array($tempNames))
					{
						$tempNames = array($tempNames);
					}

					if (!is_array($intendedNames))
					{
						$intendedNames = array($intendedNames);
					}

					$len = count($tempNames);

					for ($i = 0; $i < $len; $i++)
					{
						$tempName = array_shift($tempNames);
						$intendedName = array_shift($intendedNames);

						$extraInfo = "File descriptor :\n";
						$extraInfo .= print_r($fileDescriptor, true);
						$extraInfo .= "\n";

						// 1. Null byte check
						if (strstr($intendedName, "\u0000"))
						{
							$this->exceptionsHandler->blockRequest('uploadshield', null, $extraInfo);

							return;
						}

						// 2. PHP-in-extension check
						$explodedName = explode('.', $intendedName);
						array_reverse($explodedName);

						// 2a. File extension is .php
						if ((count($explodedName) > 1) && (strtolower($explodedName[0]) == 'php'))
						{
							$this->exceptionsHandler->blockRequest('uploadshield', null, $extraInfo);

							return;
						}

						// 2a. File extension is php.xxx
						if ((count($explodedName) > 2) && (strtolower($explodedName[1]) == 'php'))
						{
							$this->exceptionsHandler->blockRequest('uploadshield', null, $extraInfo);

							return;
						}

						// 2b. File extensions is php.xxx.yyy
						if ((count($explodedName) > 3) && (strtolower($explodedName[2]) == 'php'))
						{
							$this->exceptionsHandler->blockRequest('uploadshield', null, $extraInfo);

							return;
						}

						// 3. Contents scanner
						$fp = @fopen($tempName, 'r');

						if ($fp !== false)
						{
							$data = '';
							$extension = strtolower($explodedName[0]);

							while (!feof($fp))
							{
								$buffer = @fread($fp, 131072);
								$data .= $buffer;

								if (strstr($buffer, '<?php'))
								{
									$this->exceptionsHandler->blockRequest('uploadshield', null, $extraInfo);

									return;
								}

								if (in_array($extension, array('inc', 'phps', 'class', 'php3', 'php4', 'txt', 'dat', 'tpl', 'tmpl')))
								{
									// These are suspicious text files which may have the short tag (<?) in them
									if (strstr($buffer, '<?'))
									{
										$this->exceptionsHandler->blockRequest('uploadshield', null, $extraInfo);

										return;
									}
								}

								$data = substr($data, -4);
							}
							fclose($fp);
						}
					}
				}
			}
		}
	}
} 