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
 * A facade of registry.
 *
 * @method static mixed get($name, $default = null);
 * @method static void set($name, $value);
 * @method static bool exist($name)
 * @method static void delete($name);
 *
 * @author You Ming
 */
class Registry extends Facade
{
    /**
     * @inheritdoc
     */
    protected static function getAccessor()
    {
        return \Beaver\Registry::class;
    }
}