<?php
set_time_limit(0);
ob_end_clean();
ob_implicit_flush(true);
ignore_user_abort(true);
clearstatcache();

define('BASE', dirname(__FILE__));
define('ROOT', dirname(dirname(dirname(__FILE__))));
define('JPATH_BASE', ROOT);
define('JPATH_SITE', ROOT);
define('_JEXEC', true);

$key     = isset($_REQUEST['key'])    ? $_REQUEST['key']                      : '';
$action  = isset($_REQUEST['action']) ? trim(strtolower($_REQUEST['action'])) : '';
$keyFile = ROOT . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $key . '.key';

if (empty($action) || !in_array($action, array('stop', 'download'))) {
	echo '[complete:invalid_action]';
	exit;
}

if (!is_file($keyFile) || !is_readable($keyFile)) {
	echo '[complete:invalid_key:' . $keyFile . ']';
	exit;
}

// Load key file
$info = json_decode(file_get_contents($keyFile));

if (empty($info)) {
	echo '[complete:invalid_key_info]';
	exit;
}

switch ($action)
{
	case 'stop':
		// Create a file that mark progress is aborting
		touch(ROOT . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $key . '.abort');
	break;

	case 'download':
		require_once BASE . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'http' . DIRECTORY_SEPARATOR . 'adapter.php';
		require_once BASE . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'http' . DIRECTORY_SEPARATOR . 'adapter' . DIRECTORY_SEPARATOR . 'socket.php';
		require_once BASE . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'http' . DIRECTORY_SEPARATOR . 'client.php';
		require_once BASE . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'http' . DIRECTORY_SEPARATOR . 'download.php';

		$lockHandle = fopen($keyFile, 'r');

		// Lock key file
		flock($lockHandle, LOCK_EX);

		$httpDownload = new JSNTPLHttpDownload($info->savePath);
		$httpDownload->start($key, $info->fileUrl, $info->saveName);

		// Release key lock
		flock($lockHandle, LOCK_UN);

		// Close lock handle
		fclose($lockHandle);
	break;
}
