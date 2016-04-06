<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver;

use ArrayAccess;

/**
 * Base class for manage registry entries, as a global configuration provider.
 *
 * @author You Ming
 */
abstract class Registry implements ArrayAccess
{
    /**
     * Gets an entry with the given name in this registry.
     *
     * @param string $name The name of entry.
     * @param mixed $default
     * @return mixed
     */
    abstract public function get($name, $default = null);

    /**
     * Sets an entry in this registry.
     *
     * @param string $name The name of entry.
     * @param mixed $value The value to be set.
     */
    abstract public function set($name, $value);

    /**
     * Checks whether the entry with given name existing in this registry.
     *
     * @param string $name The name of entry.
     * @return bool
     */
    abstract public function exist($name);

    /**
     * Deletes a registry entry with given name.
     *
     * @param string $name The name of entry.
     */
    abstract public function delete($name);

    /**
     * @inheritdoc
     */
    public function offsetExists($name)
    {
        return $this->exist($name);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($name)
    {
        return $this->get($name);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($name)
    {
        $this->delete($name);
    }
}