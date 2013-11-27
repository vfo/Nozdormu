<?php
/**
 * Nozdormu is a PHP5 cron wapper.
 *
 * @package    Nzdrm
 * @version    0.1
 * @author     vfo 
 */

namespace Nzdrm;
require_once('connection.php');
class Database
{
	public static function query($sql, $db = null)
	{
		return \Nzdrm\Connection::instance($db)->query($sql);
	}

	public static function escape($str, $db = null)
	{
		return \Nzdrm\Connection::instance($db)->escape($str);
	}

	public static function set_charset($charset, $db = null)
	{
		\Nzdrm\Connection::instance($db)->set_charset($charset);
	}

}
?>