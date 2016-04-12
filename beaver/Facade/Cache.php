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
 * A facade of cache.
 *
 * @method static mixed get($name, $default = null);
 * @method static bool set($name, $value, $expiry = null);
 * @method static bool exist($name)
 * @method static bool delete($name);
 * @method static bool clear();
 *
 * @author You Ming
 */
final class Cache extends Facade
{
    /**
     * @inheritdoc
     */
    protected static function getAccessor()
    {
        return 'cache';
    }
}
