<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver;

use Beaver\Traits\ContextInjection;

/**
 * Base class for logger. Abides by PSR-3.
 *
 * @author You Ming
 */
abstract class Logger
{
    use ContextInjection;

    const EMERGENCY = 'emergency';
    const ALERT     = 'alert';
    const CRITICAL  = 'critical';
    const ERROR     = 'error';
    const WARNING   = 'warning';
    const NOTICE    = 'notice';
    const INFO      = 'info';
    const DEBUG     = 'debug';

    /**
     * Connects to logger server.
     *
     * @param array $options
     * @return bool
     */
    abstract public function connect(array $options = []);

    /**
     * Closes the logger connection.
     */
    abstract public function close();

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $content
     */
    abstract public function log($level, $message, array $content = []);

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $content
     */
    public function emergency($message, array $content = [])
    {
        $this->log(self::EMERGENCY, $message, $content);
    }

    /**
     * Action must be taken immediately.
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $content
     */
    public function alert($message, array $content = [])
    {
        $this->log(self::ALERT, $message, $content);
    }

    /**
     * Critical conditions.
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $content
     */
    public function critical($message, array $content = [])
    {
        $this->log(self::CRITICAL, $message, $content);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $content
     */
    public function error($message, array $content = [])
    {
        $this->log(self::ERROR, $message, $content);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $content
     */
    public function warning($message, array $content = [])
    {
        $this->log(self::WARNING, $message, $content);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $content
     */
    public function notice($message, array $content = [])
    {
        $this->log(self::NOTICE, $message, $content);
    }

    /**
     * Interesting events.
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $content
     */
    public function info($message, array $content = [])
    {
        $this->log(self::INFO, $message, $content);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $content
     */
    public function debug($message, array $content = [])
    {
        $this->log(self::DEBUG, $message, $content);
    }
}