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
 * A facade of Session.
 *
 * @method static void setId($id)
 * @method static string getId()
 * @method static bool start()
 * @method static void reset()
 * @method static void commit()
 * @method static void clear()
 * @method static bool regenerate($deleteOld = false)
 * @method static void set($name, $value)
 * @method static mixed get($name, $default = null)
 * @method static bool exist($name)
 * @method static void delete($name)
 *
 * @author You Ming
 */
final class Session extends Facade
{
    /**
     * @inheritdoc
     */
    protected static function getAccessor()
    {
        return 'session';
    }
}

