<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver;

use RuntimeException;

/**
 * A facade to use instance's method as static.
 *
 * @author You Ming
 */
class Facade
{
    /**
     * The current context.
     *
     * @var Context
     */
    protected static $context;

    /**
     * An array contains resolved instances.
     *
     * @var array
     */
    public static $instances = [];

    /**
     * Binds a context.
     *
     * @param Context $context
     */
    public static function bindContext(Context $context)
    {
        static::$context = $context;
    }

    /**
     * Hot swaps the underlying instance behind the facade.
     *
     * @param object $instance
     */
    protected static function swap($instance)
    {
        $called = get_called_class();
        
        static::$instances[$called] = $instance;
    }

    /**
     * Gets the object behind the facade.
     * 
     * @return object
     */
    public static function getFacadeObject()
    {
        $called = get_called_class();

        if (!isset(static::$instances[$called])) {
            $accessor = static::getAccessor();
            if (!is_object($accessor)) {
                $accessor = static::$context->get($accessor);
            }

            static::$instances[$called] = $accessor;
        }

        return static::$instances[$called];
    }

    /**
     * Gets the registered name of the component.
     *
     * @return string|object
     */
    protected static function getAccessor()
    {
        throw new RuntimeException('Subclass must implement getAccessor method.');
    }

    /**
     * Handles dynamic, static calls to the instance.
     *
     * @param string $method The name of method.
     * @param $arguments
     */
    public static function __callStatic($method, $arguments)
    {
        $instance = static::getFacadeObject();
        
        if (!$instance) {
            throw new RuntimeException('A facade object has not been resolved.');
        }
        
        return call_user_func_array([$instance, $method], $arguments);
    }
}

