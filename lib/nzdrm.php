<?php
/**
 * Nozdormu is a PHP5 cron wapper.
 *
 * @package    Nzdrm
 * @version    0.1
 * @author     vfo 
 */


namespace Nzdrm;
require_once('log.php');
require_once('profiler.php');
require_once('process.php');
require_once('vendors/PHPMailer/PHPMailerAutoload.php');


class Nzdrm {
	const VERSION = '0.1-dev';
	const CNFPATH = '../etc/';
	const BINPATH = '../bin/';

	public static $locale = 'C';
	public static $timezone = 'UTC';
	public static $process = '';
    public static $encoding = 'UTF-8';
  	public static $profiling = false;
	public static $logging = false;
	public static $mail_on_success = true;
	public static $organisation = 'domain.tld';
	public static $mail_recipient = 'mail@domain.tld';
	public static $profile_in_mail = true;
	public static $log_threshold = '300';
	public static $log_folder_path = '../var/log/';
	public static $log_file_name = 'Nozdormu';
	public static $initialized = false;
	public static $cnf = array();
	final public function __construct() { }

	public static function init($cnf_fn = 'nzdrm.conf')
	{
		if (static::$initialized)
		{
			logger('WARNING',"Can't be init more than once",__METHOD__);
		}
		$cnf = array();
		if (!file_exists(self::CNFPATH.$cnf_fn))
		{
			logger('WARNING', "Can't find ".self::CNFPATH.$cnf_fn."! Use default parameters", __METHOD__);
		}
		else
		static::$cnf = parse_ini_file(self::CNFPATH.$cnf_fn, true);
		static::$profiling = (empty(static::$cnf['profiler']))?false:true;
		static::$profiling and \Nzdrm\Profiler::init();
		static::$logging = (empty(static::$cnf['logger']))?false:true;
		static::$locale = (empty(static::$cnf['locale']))?null:static::$cnf['locale'];
		static::$log_folder_path = (empty(static::$cnf['log_folder_path']))?'../var/log/':static::$cnf['log_folder_path'];
		static::$log_file_name = (empty(static::$cnf['log_file_name']))?'Nozdormu':static::$cnf['log_file_name'];
        if (static::$locale)
		{
			setlocale(LC_ALL, static::$locale) or
			logger('WARNING', 'The configured locale '.static::$locale.' is not installed on your system.', __METHOD__);
		}
		static::$timezone = (!empty(static::$cnf['timezone'])) ? static::$cnf['timezone']: date_default_timezone_get();
		if (!date_default_timezone_set(static::$timezone))
		{
			date_default_timezone_set('UTC');
			logger('WARNING', 'The configured locale '.static::$timezone.' is not valid.', __METHOD__);

		}
		static::$log_threshold = (!empty(static::$cnf['log_threshold'])) ? static::$cnf['log_threshold']: '300';
		static::$initialized = true;
		if (static::$profiling)
		{
			\Nzdrm\Profiler::mark(__METHOD__.' End');
		}

	}
	public static function get_from_db()
	{
		/** @TODO
		**  Set DB connection
		**	Get Waiting Task(s)
		**	Set Pending state
		**	Lauch Task
		**	Update state not oneshot task
		**/
	}
	public static function launch($process)
	{
		
		static::$process = $process;
		if (static::$profiling)
			\Nzdrm\Profiler::mark(__METHOD__.' Start');
		if (!file_exists(self::BINPATH.$process.'.php'))
			logger('ERROR', "Can't find ".self::BINPATH.$process."! Shut down", __METHOD__);
		else
			if (!is_executable(self::BINPATH.$process.'.php'))
				logger('ERROR', self::BINPATH.$process." isn't executable! Shut down", __METHOD__);
			else
			{
				if (static::$logging)
					logger('INFO', 'Nozdormu launches '.$process.'.php');
				$pr = new \Nzdrm\Process($process.'.php');
				while(true)
					if ($pr->status === false)
						break;
			}
		if (static::$profiling)
			\Nzdrm\Profiler::mark(__METHOD__.' End');
		if (static::$logging)
			logger('INFO', 'Nozdormu ended '.$process.'.php');
		self::shut_down();
	}
	public static function shut_down()
	{
		if (static::$logging)
			logger('INFO', 'Nozdormu is Shuting down.');
		$mail = new \PHPmailer();
		$mail->From = 'noreply@'.static::$organisation;
		$mail->FromName = 'Nozdormu';
		$mail->addAddress(static::$mail_recipient);
		$mail->addReplyTo('noreply@'.static::$organisation, 'No-Reply');
		
		$mail->WordWrap = 50;
		if (file_exists(static::$log_folder_path.date('Y/m/d-').static::$process.'.log'))
		$mail->addAttachment(static::$log_folder_path.date('Y/m/d-').static::$process.'.log');
		
		$mail->isHTML(false);
		$title = '';
		$err= false;
		if (file_exists(static::$log_folder_path.date('Y/m/d-').static::$process.'.log'))
		{
			$ftmp = file_get_contents(static::$log_folder_path.date('Y/m/d-').static::$process.'.log');
			$titlechange = array('WARNING', 'ERROR','ALERT');
			foreach ($titlechange AS $tc)
				if (substr_count($ftmp, $tc))
				{
					$title = 'Cron ended with '.strtolower($tc).'(s)';
					$err= true;
				}
			if (!$title)
				$title = "Cron sucessfully ended";
		}
		$mail->Subject = '[NZDRM]['.self::$process.']'.$title;
		$body= "Hi,\n";
if ($err)
$body .="The script '".static::$process."' generates the error/warning messages during his last execution.\nPlease read log file in attachment.\n";
else
$body .= "The script '".static::$process."' successfully ended";

if (static::$profile_in_mail)
 $body .= "Profile overviews:\n" . \Nzdrm\Profiler::displayLogs();
$body .="\n-- \n[This is an automatic message send by Nozdormu v. ".self::VERSION."]\n";

		$mail->Body = $body;
		if ($err !== true AND !static::$mail_on_success)
			if(!$mail->send()) 
  			 logger('WARNING', 'Mailer Error: ' . $mail->ErrorInfo, __METHOD__);

	}
}
?>
