<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver\Facade;

use Beaver\Facade;

/**
 * A facade of Logger.
 *
 * @method static void log($level, $message, array $content = [])
 * @method static void emergency($message, array $content = [])
 * @method static void alert($message, array $content = [])
 * @method static void critical($message, array $content = [])
 * @method static void error($message, array $content = [])
 * @method static void warning($message, array $content = [])
 * @method static void notice($message, array $content = [])
 * @method static void info($message, array $content = [])
 * @method static void debug($message, array $content = [])
 *
 * @author You Ming
 */
final class Logger extends Facade
{
    /**
     * @inheritdoc
     */
    protected static function getAccessor()
    {
        return 'logger';
    }
}
