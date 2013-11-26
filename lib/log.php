<?php
/**
 * Nozdormu is a PHP5 cron wapper.
 *
 * @package    Nzdrm
 * @version    0.1
 * @author     vfo 
 */

namespace Nzdrm;

class Log
{

	protected static $log 		= null;
	protected static $logfile 	= null;
	protected static $handler	= null;
	protected static $levels = array(
			100 => 'DEBUG',
			200 => 'INFO',
			250 => 'NOTICE',
			300 => 'WARNING',
			400 => 'ERROR',
			500 => 'CRITICAL',
			550 => 'ALERT',
			600 => 'EMERGENCY',
			);

		/**
		 * Initialize the class
		 */
		public static function _init()
		{
						// determine the name and location of the logfile
			$rootpath = \Nzdrm\Nzdrm::$log_folder_path.date('Y').'/';
			$filepath = \Nzdrm\Nzdrm::$log_folder_path.date('Y/m').'/';
			$filename = $filepath.date('d').'-'.\Nzdrm\Nzdrm::$log_file_name.'.log';


			if ( ! is_dir($rootpath))
				mkdir($rootpath, 0777, true);
			if ( ! is_dir($filepath))
				mkdir($filepath, 0777, true);

			static::$handler = fopen($filename, 'a');
			if (static::$handler === false)
				\Nzdrm\Nzdrm::$log_threshold = 0;

			static::$logfile = $filename;
			static::$log = true;

		}

		/**
		 * Write Log File
		 *
		 * This function must be called using the global logger() function
		 *
		 * @access        public
		 * @param        int|string        the error level
		 * @param        string        the error message
		 * @param        string        information about the method
		 * @return        bool
		 */
		public static function log($level, $msg, $method = null)
		{

			static::$log or static::_init();
			if (static::$handler)
			{
				fputs(static::$handler, static::$levels[$level].' --> '.date('H:i:s').'  ['.$method.'] : '.$msg.PHP_EOL);
				return true;        
			}
			return false;
		}
	}