<?php

namespace Nzdrm;

class Profiler
{

        protected static $profiler = null;
        protected static $logs = array(
                                    'console'=>array(),
                                    'queries' => array(),
                                    'query_count' => 0,
                                    'log_count' => 0,
                                    'error_count' => 0,
                                    'speed_count'=>0);

        protected static $query = null;

        public static function init()
        {
                        static::mark(__METHOD__.' Start');
                        \Nzdrm\Nzdrm::$profiling = true;
        }

        public static function mark($label)
        {
               self::logSpeed($label);
        }

        public static function mark_memory($var = false, $name = 'PHP')
        {
                self::logMemory($var, $name);
        }

        public static function console($text)
        {
                self::log($text);
        }

        public static function app_total()
        {

        }
          public static function log($data) {
                $logItem = array(
                        "data" => $data,
                        "type" => 'log'
                );
                self::addToConsoleAndIncrement('log_count', $logItem);
        }



        public static function logMemory($object = false, $name = 'Memory Usage') {
                $memory = ($object) ? strlen(serialize($object)) : memory_get_usage();
                $logItem = array(
                        "data" => $memory,
                        "type" => 'memory',
                        "name" => $name,
                        "dataType" => gettype($object)
                );
                self::addToConsoleAndIncrement('memory_count', $logItem);
        }

        public static function logSpeed($name = 'Point in Time') {
                $logItem = array(
                        "data" => self::getMicroTime(),
                        "type" => 'speed',
                        "name" => $name
                );
                self::addToConsoleAndIncrement('speed_count', $logItem);
        }

      public static function getMicroTime() {
                $time = microtime();
                $time = explode(' ', $time);
                return $time[1] + $time[0];
        }

        public static function addToConsoleAndIncrement($log, $item) {
                if(!isset(static::$logs))
                        die(var_dump(static::$logs));
                 //self::init();
                static::$logs['console'][] = $item;
                static::$logs[$log] += 1;
        }

        public static function getLogs() {
                if(!isset(static::$logs)) self::init();
                return static::$logs;
        }
        public static function displayLogs() {
             
        }
}