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