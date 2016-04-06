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
 * Base class for cache.
 *
 * @author You Ming
 */
abstract class Cache
{
    use ContextInjection;

    /**
     * Opens cache manager for using.
     *
     * @param array $options
     * @return bool
     */
    abstract public function open(array $options = []);

    /**
     * Closes cache manager.
     */
    abstract public function close();

    /**
     * Gets an item from the cache.
     *
     * @param string $name The cache name.
     * @param mixed $default The value returned when the item do not exist in the cache.
     * @return mixed
     */
    abstract public function get($name, $default = null);

    /**
     * Sets an item in the cache.
     *
     * @param string $name The cache name.
     * @param mixed $value The cache value.
     * @param int $expiry
     * @return bool
     */
    abstract public function set($name, $value, $expiry = null);

    /**
     * Checks whether an item exists in the cache.
     *
     * @param string $name The cache name.
     * @return bool
     */
    abstract public function exist($name);

    /**
     * Deletes item.
     *
     * @param string $name The cache name.
     * @return bool
     */
    abstract public function delete($name);

    /**
     * Clears all cache item.
     *
     * @return bool
     */
    abstract public function clear();

    /**
     * Gets an item from the cache.
     *
     * @param string $name The cache name.
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Sets an item in the cache.
     *
     * @param string $name The cache name.
     * @param mixed $value The cache value.
     * @return bool
     */
    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    /**
     * Checks whether an item exists in the cache.
     *
     * @param string $name The cache name.
     * @return bool
     */
    public function __isset($name)
    {
        return $this->exist($name);
    }
    
    /**
     * Deletes item.
     *
     * @param string $name The cache name.
     * @return bool
     */
    public function __unset($name)
    {
        return $this->delete($name);
    }
}