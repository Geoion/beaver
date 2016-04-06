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

/**
 * The common tools for arrays.
 *
 * @author You Ming
 */
class Arrays
{
    /**
     * Determines whether the given value is array accessible.
     *
     * @param mixed $array
     * @return bool
     */
    public static function isAccessible($array)
    {
        return is_array($array) || $array instanceof ArrayAccess;
    }

    /**
     * Determines if an array is associative.
     *
     * @param array $array
     * @return bool
     */
    public static function isAssoc(array $array)
    {
        $keys = array_keys($array);
        return array_keys($keys) !== $keys;
    }

    /**
     * Determines whether the given key exists in the given array.
     *
     * @param ArrayAccess|array $array
     * @param string|int $key
     * @return bool
     */
    public static function exists($array, $key)
    {
        return $array instanceof ArrayAccess ? $array->offsetExists($key) : array_key_exists($key, $array);
    }

    /**
     * Gets an item from the given array using "dot" notation.
     *
     * @param array $array
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function dotGet($array, $key, $default = null)
    {
        if (null === $key) {
            return $array;
        }

        // Only find with full key in top level.
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $k) {
            if (!is_array($array) || !array_key_exists($k, $array)) {
                return $default;
            }
            $array = $array[$k];
        }

        return $array;
    }

    /**
     * Sets an item to the given value using "dot" notation.
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return array
     */
    public static function dotSet(&$array, $key, $value)
    {
        if (null === $key) {
            return $array = $value;
        }
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $k = array_shift($keys);
            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value.
            if (!isset($array[$k]) || !is_array($array[$k])) {
                $array[$k] = [];
            }
            $array = &$array[$k];
        }
        $array[array_shift($keys)] = $value;
        return $array;
    }

    /**
     * Determines whether an item exists in the given array using "dot" notation.
     *
     * @param array $array
     * @param string $key
     * @return bool
     */
    public static function dotExist(array $array, $key)
    {
        if (null === $key) {
            return $array;
        }
        // Only find with full key in top level.
        if (array_key_exists($key, $array)) {
            return true;
        }
        foreach (explode('.', $key) as $k) {
            if (!is_array($array) || !array_key_exists($k, $array)) {
                return false;
            }
            $array = $array[$k];
        }
        return true;
    }

    /**
     * Deletes an item in array using "dot" notation.
     *
     * @param array $array
     * @param string $key
     */
    public static function dotDelete(array &$array, $key)
    {
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $k = array_shift($keys);
            // If the key doesn't exist at this depth, just return.
            if (!isset($array[$k]) || !is_array($array[$k])) {
                return;
            }
            $array = &$array[$k];
        }
        unset($array[array_shift($keys)]);
    }
}