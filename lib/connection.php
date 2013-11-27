<?php
/**
 * Nozdormu is a PHP5 cron wapper.
 *
 * @package    Nzdrm
 * @version    0.1
 * @author     vfo 
 */

namespace Nzdrm;

abstract class Connection
{
	public static $instances = array();
	protected $_instance;
	protected $_conn;

	public static function instance($name)
	{
		if ($name === null)
			$name = \Nzdrm\Nzdrm::$cnf['database']['name'];
		if (!isset($instances[$name]))
		{
			if (!isset(\Nzdrm\Nzdrm::$cnf['database']['driver']))
			{
				logger('NOTICE', 'Database type not defined, assumed to be Mysqli');
				\Nzdrm\Nzdrm::$cnf['database']['driver'] = 'MySQLi';
			}
			require_once(strtolower(\Nzdrm\Nzdrm::$cnf['database']['driver']).'.php');
			$driver = '\Nzdrm\\'.\Nzdrm\Nzdrm::$cnf['database']['driver'].'_Connection';
			new $driver($name);
		}
		return static::$intances[$name];
	}

	protected function __construct($name)
	{
		$this->_instance = $name;
		static::$instances[$name] = $this;
	}
   	
   	final public function __destruct()
    {
                $this->disconnect();
    }

    final public function __toString()
    {
                return $this->_instance;
    }

	abstract public function connect();
	abstract public function disconnect();
	abstract public function query($sql);
	abstract public function escape($value);
	abstract public function set_charset($charset);
}

?>