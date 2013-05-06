<?php

class Logger extends CI_Log {
	
	public $max_string_length = 1200;
	
	/**
	 * Write Log File
	 *
	 * Generally this function will be called using the global log_message() function
	 *
	 * @param	string	the error level
	 * @param	string	the error message
	 * @param	bool	whether the error is a native PHP error
	 * @return	bool
	 */
	private function log_write($msg, $filepath)
	{
		$messages = explode("\n",$msg);
		
		foreach($messages as $k => $m)
		{
			$messages[$k] = substr($m,0,$this->max_string_length);
		}
		$msg = implode("\n",$messages);
		
		$message = '';

		if ( ! file_exists($filepath))
		{
			$message .= "<"."?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?".">\n\n";
		}

		if ( ! $fp = @fopen($filepath, FOPEN_WRITE_CREATE))
		{
			return FALSE;
		}

		$message .= "\n\n".date($this->_date_fmt). ' --> '.$msg."\n";

		flock($fp, LOCK_EX);
		fwrite($fp, $message);
		flock($fp, LOCK_UN);
		fclose($fp);

		@chmod($filepath, FILE_WRITE_MODE);
		return TRUE;
	}
	

	/**
	 * Log APN
	 */
	public function write($msg)
	{
		$config =& get_config();
		
		$filepath = $this->_log_path.'watcher/log-'.date('Y-m-d').'.php';
		return $this->log_write($msg, $filepath);
	}
	

	
}