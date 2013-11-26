<?php
/**
 * Part of Nozdormu.
 *
 * @package    Nzdrm
 * @version    0.1
 * @author     vfo
 */

/**
 * Shortcut for writing to the Log
 *
 * @param        int|string        the error level
 * @param        string        the error message
 * @param        string        information about the method
 * @return        bool
 */


if ( ! function_exists('logger'))
{
        function logger($level, $msg, $method = null)
        {
                static $labels = array(
                        100 => 'DEBUG',
                        200 => 'INFO',
                        250 => 'NOTICE',
                        300 => 'WARNING',
                        400 => 'ERROR',
                        500 => 'CRITICAL',
                        550 => 'ALERT',
                        600 => 'EMERGENCY',
                        700 => 'ALL',
                );

                if ((is_int($level) and ! isset($labels[$level])) or (is_string($level) and ! array_search(strtoupper($level), $labels)))
                {
                        logger('WARNING','Invalid level "'.$level.'" passed to logger()');
                        return false;
                }

                if(is_string($level))
                        $level = array_search(strtoupper($level), $labels);

                $loglabels = \Nzdrm\Nzdrm::$log_threshold;

                if ($loglabels == 0)
                        return false;

                if (\Nzdrm\Nzdrm::$profiling)
                        \Nzdrm\Profiler::log($method.' - '.$msg);

                // if it's not an array, assume it's an "up to" level
                if ( ! is_array($loglabels))
                {
                        $a = array();
                        foreach ($labels as $l => $label)
                                $l >= $loglabels and $a[] = $l;
                        $loglabels = $a;
                }
                if ( ! in_array($level, $loglabels))
                        return false;

                return \Nzdrm\Log::log($level, $msg, $method);
        }
}