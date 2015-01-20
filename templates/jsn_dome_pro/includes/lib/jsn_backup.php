<?php
/**
 * @author    JoomlaShine.com http://www.joomlashine.com
 * @copyright Copyright (C) 2008 - 2011 JoomlaShine.com. All rights reserved.
 * @license   GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted index access' );

class JSNBackup{
	public function JSNBackup(){}

	public static function getInstance()
	{
		static $instance;
		if ($instance == null) {
			$instance = new JSNBackup();
		}
		return $instance;
	}

	/**
	 * Make backup of current website database
	 *
	 */
	public function executeBackup($path, $filename, $data)
	{
		if ($this->existingBackupFile($path)) return false;

		$db 				= JFactory::getDBO();
		$config 			= new JConfig();
		$database_prefix	= $config->dbprefix;
		$database_name 		= $config->db;
		$backupFileName 	= 'jsn_'.$filename.'_backup_'.date('YmdHis').'.sql';
		$zipFileName 		= 'backup_sample-data_'.date('Y-m-d-His').'.zip';
		$backupFilePath 	= $path. DIRECTORY_SEPARATOR .$backupFileName;
		$zipFilePath 		= $path. DIRECTORY_SEPARATOR .$zipFileName;

 		$schema = '# JSN Template Solution' . "\n" .
                  '# http://joomlashine.com/' . "\n" .
                  '#' . "\n" .
                  '# Copyright (c) ' . date('Y') . ' JoomlaShine.com. All rights reserved.' . "\n" .
                  '#' . "\n" .
                  '# Database: ' . $database_name . "\n" .
                  '# Database Server: localhost' . "\n" .
                  '#' . "\n" .
                  '# Backup Date: ' . date("F j, Y, g:i a") . "\n\n";

		if($data != false && is_array($data))
		{
			$siteTableList = $db->getTableList();

	        foreach ($data as $value)
			{
				$tables = $value->backup;
				if(count($tables))
				{
					foreach ($tables as $table)
					{
						/* Ensure that the table actually exists on current site */
						if (in_array($database_prefix.$table, $siteTableList))
						{
							$schema .= $this->getTableInfo($db, $table, $database_prefix);
						}
					}
				}
			}
		}
		jimport('joomla.filesystem.file');
		JFile::write($backupFilePath, $schema);
		$this->createZip($zipFilePath, $path, $backupFileName);
		JFile::delete($backupFilePath);

		return $zipFileName;
	}

	/**
	 * Get table information
	 *
	 */
	public function getTableInfo($database_object, $table_name, $database_prefix)
	{
        $table_info =  $database_object->getTableCreate('#__'.$table_name);
        $schema 	=  "\n".'DROP TABLE IF EXISTS `'.$database_prefix.$table_name.'`;' . "\n";
        $schema 	.= $table_info['#__'.$table_name].';';
        $schema 	.= "\n\n";
		$table_fields = $database_object->getTableColumns('#__'.$table_name, false);
		$field_list   = array_keys($table_fields);

		$query 		= 'SELECT `' . implode('`, `', $field_list) . '` FROM #__'.$table_name;
		$database_object->setQuery($query);
		$rows_query = $database_object->loadAssocList();
		if (count($rows_query))
		{
			foreach ($rows_query as $value)
			{
				$schema .= 'INSERT INTO `'.$database_prefix.$table_name.'` (`' . implode('`, `', $field_list) . '`) VALUES (';
				reset($field_list);
				foreach ($field_list as $field_value)
				{
					if (!isset($value[$field_value]))
					{
						 $schema .= 'NULL, ';
					}
					elseif (!empty($value[$field_value]) && !is_null($value[$field_value]))
					{
						$row 	= addslashes($value[$field_value]);
						$row 	= preg_replace("/\n#/", "/\n".'\#/', $row);
						$schema .= '\'' . $row . '\', ';
					}
					else
					{
						$schema .= '\'\', ';
					}
				}
				$schema = preg_replace('/, $/', '', $schema) . ');' . "\n";
			}
		}
		return 	$schema;
	}

	/**
	 * Create backup zip file
	 *
	 * @param $file_name_zip
	 * @param $file
	 */
	public function createZip($zipFileName, $basePath, $backupFile)
	{
        require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'jsn_archive.php';

        $zip = new JSNZIPFile($zipFileName);
		$zip->setOptions(array('basedir' => $basePath, 'inmemory' => 0, 'recurse' => 1, 'storepaths' => 1));
		$zip->addFiles($backupFile);
		$zip->createArchive();
	}

	/**
	 * Download backup file
	 *
	 * @param $type
	 * @param $file_name
	 */
	public function downloadFile($type, $file_name)
	{
		jimport('joomla.filesystem.file');
		$folder_temp	= JPATH_ROOT. DIRECTORY_SEPARATOR .'tmp';
		$file_path 		= $folder_temp. DIRECTORY_SEPARATOR .$file_name;
		$file_size 		= filesize($file_path);
		switch ($type)
		{
			case "zip":
				header("Content-Type: application/zip");
				break;
			case "bzip":
				header("Content-Type: application/x-bzip2");
				break;
			case "gzip":
				header("Content-Type: application/x-gzip");
				break;
			case "tar":
				header("Content-Type: application/x-tar");
		}
		$header = "Content-Disposition: attachment; filename=\"";
		$header .= $file_name;
		$header .= "\"";
		header($header);
		header('Content-Description: File Transfer');
		header("Content-Length: " . $file_size);
		header("Content-Transfer-Encoding: binary");
		header("Cache-Control: no-cache, must-revalidate, max-age=60");
		header("Expires: Sat, 01 Jan 2000 12:00:00 GMT");
		ob_clean();
   	 	flush();
		@readfile($file_path);
		//JFile::delete($file_path);
	}

	public function existingBackupFile($path)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$fileList 	= JFolder::files($path);

		if ($fileList !== false)
		{
			foreach ($fileList as $file)
			{
				if (is_file($path. DIRECTORY_SEPARATOR .$file) && substr($file, 0, 1) != '.' && strtolower($file) !== 'index.html')
				{
					$ext = strtolower(JFile::getExt($file));

					if ($ext == 'zip')
					{
						$str = substr($file, 0, 18);
						if ($str == 'backup_sample-data')
						{
	   					 	return true;
						}
					}
				}
			}
		}
		return false;
	}
}
?>
