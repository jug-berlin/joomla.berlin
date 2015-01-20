<?php
/**
 * @author    JoomlaShine.com http://www.joomlashine.com
 * @copyright Copyright (C) 2008 - 2011 JoomlaShine.com. All rights reserved.
 * @license   GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 * @version   $Id: jsn_listmodifiedfiles.php 17002 2012-10-13 09:39:19Z tuyetvt $
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted index access' );

require_once(dirname(__FILE__). DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'jsn_checksum_integrity_comparison.php');
$obj_checksum 	= new JSNChecksumIntegrityComparison();
$results 		= $obj_checksum->compareIntegrity();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
	<head>
		<title>Modified files list</title>
		<link rel="stylesheet" href="<?php echo JURI::base(true); ?>/templates/<?php echo $this->template; ?>/admin/css/jsn_admin.css" type="text/css" />
	</head>
	<body>
		<div id="jsn-modified-file-list">
			<h3 class="jsn-element-heading">
				<?php echo JText::_('MODIFIED_FILES_LIST'); ?>
			</h3>
			<div class="jsn-div-scroll">
				<ul class="list">
				<?php
					if (count($results))
					{
						foreach ($results as $key => $values)
						{
							if (count($values))
							{
								foreach ($values as $value)
								{
									$slash = strrpos($value, '/');
									if ($slash)
									{
										$file  = substr($value, $slash + 1);
										$path  = substr($value, 0, $slash + 1);
									}
									else
									{
										$file = $value;
										$path = '';
									}
									echo '<li class="icon-'.$key.'">'.'<strong>'.$path.'</strong>'.$file.'</li>';
								}
							}
						}
					}
				?>
				</ul>
			</div>
			<div class="explain-icon">
				<span class="icon-added begin">
					<?php echo JText::_('ADDED'); ?>
				</span>
				<span class="icon-modified">
					<?php echo JText::_('MODIFIED'); ?>
				</span>
				<span class="icon-deleted end">
					<?php echo JText::_('DELETED'); ?>
				</span>
			</div>
		</div>
	</body>
</html>