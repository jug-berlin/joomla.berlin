<?php
class JSNTPLHttpDownload
{
	/**
	 * @var  int
	 */
	private $_checkPoint;

	/**
	 * @var  int
	 */
	private $_checkSize;

	/**
	 * @var  JSNHttpRequest
	 */
	private $_request;

	/**
	 * Class constructor
	 * 
	 * @param   string  $path  Path to the folder that will store downloaded files
	 */
	public function __construct ($path)
	{
		$this->_checkPoint = microtime(true);
		$this->_checkSize  = 0;
		$this->_path       = $path;

		$this->_request = JSNTPLHttpClient::createRequest();
		$this->_request->registerCallback('download.progress', 'progress', $this);
		$this->_request->registerCallback('download.complete', 'complete', $this);
	}

	/**
	 * Output data to client for update download progress
	 * 
	 * @param   object   $event        Event object
	 * @param   int      $size         Total download size
	 * @param   int      $downloaded   Downloaded size of file at the moment
	 * @param   boolean  $forceUpdate  Force send data to client
	 * 
	 * @return  void
	 */
	public function progress ($event, $size, $downloaded, $forceUpdate = false)
	{
		$currentPoint = microtime(true);
		$speed = 0;

		if ($currentPoint - $this->_checkPoint > 0.5 || $forceUpdate === true)
		{
			if (is_file($this->_path . DIRECTORY_SEPARATOR . $this->_process . '.abort'))
			{
				$event->stop = true;
				return;
			}

			$time = $currentPoint - $this->_checkPoint;
			$length = $downloaded - $this->_checkSize;
			$speed = round($length / $time, 2);

			// Save checkpoint
			$this->_checkSize = $downloaded;
			$this->_checkPoint = $currentPoint;

			echo $this->_useIFrame
				? "<script type=\"text/javascript\">window.parent.{$this->_progressCallback}({$size}, {$downloaded}, {$speed})</script>"
				: "[{$size},{$downloaded},{$speed}]";

			flush();
		}
	}

	/**
	 * Send result to client when download process is aborted
	 * 
	 * @param   string  $file  Name of the file after downloaded
	 * 
	 * @return  void
	 */
	public function complete ($file)
	{
		$abortFile = $this->_path . DIRECTORY_SEPARATOR . $this->_process . '.abort';
		$cacheFile = $this->_path . DIRECTORY_SEPARATOR . $file;
		$message   = 'success';

		if (is_file($abortFile))
		{
			unlink($abortFile);
			unlink($cacheFile);
			$message = 'abort';
		}
		else if ($this->_saveAsName != null)
		{
			rename($cacheFile, $this->_path . DIRECTORY_SEPARATOR . $this->_saveAsName);
		}

		echo $this->_useIFrame
			? "<script type=\"text/javascript\">window.parent.{$this->_completeCallback}('{$message}')</script>"
			: "[complete:{$message}]";

		flush();
	}

	/**
	 * Start to download file
	 * 
	 * @param   string  $processId  ID of the download process
	 * @param   string  $file       URL to file that will be downloaded
	 * @param   string  $saveAs     Name of file after downloaded
	 * 
	 * @return  void
	 */
	public function start ($processId, $file, $saveAs = null)
	{
		$this->_file = $file;
		$this->_saveAsName = $saveAs;

		$this->_useIFrame = preg_match('/MSIE/i', $_SERVER['HTTP_USER_AGENT']);
		$this->_process   = $processId;
		$this->_file      = $file;

		$this->_progressCallback  = '__' . $this->_process . '_progress';
		$this->_completeCallback  = '__' . $this->_process . '_complete';

		if ($this->_useIFrame)
		{
			echo "<!DOCTYPE html>\r\n";
			echo "<html>\r\n";
			echo "	<head></head>\r\n";
			echo "	<body>\r\n";

			flush();
			
			$this->_request->download($this->_file, $this->_path);

			echo "	</body>\r\n";
			echo "</html>\r\n";

			return;
		}

		echo str_repeat("&nbsp;", 500);
		flush();
		$this->_request->download($this->_file, $this->_path);
	}
}
