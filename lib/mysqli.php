<?php
/**
 * Nozdormu is a PHP5 cron wapper.
 *
 * @package    Nzdrm
 * @version    0.1
 * @author     vfo 
 */

namespace Nzdrm;

class MySQLi_connection extends \Nzdrm\Connection
{
	protected $_connection;
	protected $_config;
	protected $_connection_id;
	protected static $_current_databases = array();

	public function connect()
	{
		if ($this->_connection)
			return ;
		$this->_config = \Nzdrm\Nzdrm::$cnf['database'];
		extract($this->_config['connection']);
		unset($this->_config['connection']['username'], $this->_config['connection']['password']);
		$this->_connection = new \MySQLi($hostname, $username, $password, $database);
		if ($this->_connection->error)
			logger('ERROR', "DB error :".$this->_connection->error, $this->_connection->errno, __METHOD__);
		$this->_connection_id = sha1($hostname.'_'.$username.'_'.$password);
		if (!empty($this->_config['charset']))
			$this->set_charset($this->_config['charset']);
		static::$_current_databases[$this->_conection_id] = $database;
	}

	public function disconnect()
	{
		$status =  false;
		if ($this->connection instanceof \MySQLi)
		{
			$status = $this->_connection->close();
			$status = true;
		}
		return $status;

	}
	public function query($sql, $type, $as_result)
	{
		$this->_connection or $this->connect();		
		if (!empty($this->_config['profiling']))
		{
			$stack = array();
			foreach (debug_backtrace() AS $idx =>$page)
				if ($idx > 0 and !empty($page['file']))
					$stack[] = array('file'=>$page['file'], 'line'=>$page['line']);
			$bench = \Nzdrm\Profiler::start($this->_instance, $sql, $stack);
		}
		if (($result = $this->_connection->query($sql)) === false)
		{
			if (isset($bench))
				\Nzdrm\Profiler::delete($bench);
			logger('ERROR', '['.$this->_connection->errno.'] '.$this->_connection->error.' => '.$sql, __METHOD__);
		}
		if (isset($bench))
			\Nzdrm\Profiler::stop($bench);
		if ($type === \Nzdrm\Database::SELECT)
			return new \MySQLi_Result();
		elseif ($type === \Nzdrm\Database::INSERT)
			return array($this->_connection->insert_id,
                         $this->_connection->affected_rows,);
		else
			return $this->_connection->affected_rows;
	}
	public function set_charset($charset)
	{
		$this->_connection or $this->connect();	
		$status = $this->_connection->set_charset($charset);
		if ($status === false)
			logger('ERROR', "DB error :".$this->_connection->error, $this->_connection->errno, __METHOD__);
	}
	public function escape($value)
	{
		$this->_connection or $this->connect();

		if (($value = $this->_connection->real_escape_string((string) $value)) === false)
			logger('ERROR', "DB error :".$this->_connection->error, $this->_connection->errno, __METHOD__);
		return "'$value'";

	}
	public function error_info()
	{
		$errno = $this->_connection->errno;
		return array($errno, empty($errno)? null : $errno, empty($errno) ? null : $this->_connection->error);
	}
}