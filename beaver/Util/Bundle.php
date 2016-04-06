<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver\Util;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * A container for key/value pairs.
 *
 * @author youmingdot
 */
class Bundle implements ArrayAccess, IteratorAggregate, Countable
{
    /**
     * Bundle storage.
     *
     * @var array
     */
    protected $pairs;

    /**
     * Constructor.
     *
     * @param array $pairs
     */
    public function __construct(array $pairs = [])
    {
        $this->pairs = $pairs;
    }

    /**
     * Gets the pairs.
     *
     * @return array
     */
    public function all()
    {
        return $this->pairs;
    }

    /**
     * Gets the keys.
     *
     * @return array
     */
    public function keys()
    {
        return array_keys($this->pairs);
    }

    /**
     * Replaces the current pairs by a new pairs.
     *
     * @param array $pairs
     */
    public function replace(array $pairs = [])
    {
        $this->pairs = $pairs;
    }

    /**
     * Adds pairs.
     *
     * @param array $pairs An array of pairs
     */
    public function add(array $pairs = [])
    {
        $this->pairs = array_replace($this->pairs, $pairs);
    }

    /**
     * Gets a value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (isset($this->pairs[$key]) || array_key_exists($key, $this->pairs)) {
            return $this->pairs[$key];
        }

        return $default;
    }

    /**
     * Sets a value by key.
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->pairs[$key] = $value;
    }

    /**
     * Determines whether the pairs is defined.
     *
     * @param string $key
     * @return bool True if the pairs exists, false otherwise
     */
    public function exist($key)
    {
        return isset($this->pairs[$key]) || array_key_exists($key, $this->pairs);
    }

    /**
     * Deletes a pairs.
     *
     * @param string $key
     */
    public function delete($key)
    {
        unset($this->pairs[$key]);
    }

    /**
     * Gets the value converted to integer.
     *
     * @param string $key
     * @param int $default
     * @return int
     */
    public function getInt($key, $default = 0)
    {
        return (int) $this->get($key, $default);
    }

    /**
     * Gets the value converted to boolean.
     *
     * @param string $key
     * @param bool $default
     * @return bool
     */
    public function getBoolean($key, $default = false)
    {
        return $this->getWithFilter($key, $default, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Gets the value converted to string.
     *
     * @param string $key
     * @param string $default
     * @return string
     */
    public function getString($key, $default = '')
    {
        return (string) $this->get($key, $default);
    }

    /**
     * Gets the value applied filter.
     *
     * @param string $key
     * @param mixed $default
     * @param int $filter Filter constant
     * @param mixed $options Filter options
     * @return mixed
     */
    public function getWithFilter($key, $default = null, $filter = FILTER_DEFAULT, $options = [])
    {
        $value = $this->get($key, $default);
        // Support filter_var shortcuts.
        if (!is_array($options) && $options) {
            $options = ['flags' => $options];
        }
        // Adds a convenience check for arrays.
        if (is_array($value) && !isset($options['flags'])) {
            $options['flags'] = FILTER_REQUIRE_ARRAY;
        }
        return filter_var($value, $filter, $options);
    }

    /**
     * Gets all pairs as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->pairs;
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->pairs);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return count($this->pairs);
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * @inheritdoc
     */
    public function __isset($name)
    {
        return $this->exist($name);
    }

    /**
     * @inheritdoc
     */
    public function __unset($name)
    {
        $this->delete($name);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return $this->exist($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        $this->delete($offset);
    }
}
