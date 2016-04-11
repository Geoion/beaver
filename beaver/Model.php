<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver;

/**
 * Base class for classes representing relational data in terms of object and also
 * provides operations for database.
 *
 * @author You Ming
 */
class Model
{
    /**
     * The relational data.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Sets the data.
     *
     * @param array $data
     * @param bool $replace If true, the old data will be replaced. If false, the new data
     *      and the old data will be merged.
     */
    public function setData(array $data, $replace = true)
    {
        $this->data = $replace ? $data : array_merge($this->data, $data);
    }

    /**
     * Gets all data in this model as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }

    /**
     * @inheritdoc
     */
    public function __isset($name)
    {
        return isset($this->data[$name]) || array_key_exists($name, $this->data);
    }

    /**
     * @inheritdoc
     */
    public function __unset($name)
    {
        unset($this->data[$name]);
    }
}