<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver;

use Beaver\Exception\DbException;
use Beaver\Traits\ContextInjection;
use Throwable;

/**
 * Base class for database operating.
 *
 * @author You Ming
 */
abstract class Db
{
    use ContextInjection;

    /**
     * Debug mode.
     *
     * @var bool
     */
    protected $debug = false;

    /**
     * Last error message.
     *
     * @var string
     */
    protected $lastError = '';

    /**
     * Sets debug mode.
     *
     * @param bool $debug
     */
    protected function setDebug($debug)
    {
        $this->debug = $debug;
    }

    /**
     * Connects to database server.
     *
     * @param array $options
     * @return bool
     */
    abstract public function connect(array $options = []);

    /**
     * Closes the database connection.
     *
     * @return bool
     */
    abstract public function close();

    /**
     * Selects a database for operating.
     *
     * @param mixed $db
     * @param array $options
     * @return mixed
     */
    abstract public function selectDb($db, $options = []);

    /**
     * Counts the number of records in a table.
     *
     * @param string $table
     * @param array $where
     * @param array $options
     * @return mixed
     */
    abstract public function count($table, $where = [], $options = []);

    /**
     * Inserts an record into a table.
     *
     * @param string $table
     * @param array $data
     * @param array $options
     * @return mixed
     */
    abstract public function insertOne($table, $data, $options = []);

    /**
     * Inserts multiple records into a table.
     *
     * @param string $table
     * @param array $data
     * @param array $options
     * @return mixed
     */
    abstract public function insertMany($table, array $data, $options = []);

    /**
     * Queries a table, returning a single record.
     *
     * @param string $table
     * @param array|string $where
     * @param array|string $fields
     * @param array $options
     * @return mixed
     */
    abstract public function findOne($table, $where, $fields = [], $options = []);

    /**
     * Queries a table, returning an array for the result set.
     *
     * @param string $table
     * @param array|string $where
     * @param array|string $fields
     * @param array $options
     * @return mixed
     */
    abstract public function findMany($table, $where, $fields = [], $options = []);

    /**
     * Update first record based on a given criteria.
     *
     * @param string $table
     * @param array|string $where
     * @param array|string $data
     * @param array $options
     * @return mixed
     */
    abstract public function updateOne($table, $where, $data, $options = []);

    /**
     * Updates records based on a given criteria.
     *
     * @param string $table
     * @param array|string $where
     * @param array|string $data
     * @param array $options
     * @return mixed
     */
    abstract public function updateMany($table, $where, $data, $options = []);

    /**
     * Delete first record from this collection based on a given criteria.
     *
     * @param string $table
     * @param array|string $where
     * @param array $option
     * @return mixed
     */
    abstract public function deleteOne($table, $where, $option = []);

    /**
     * Deletes records from this collection.
     *
     * @param string $table
     * @param array|string $where
     * @param array $option
     * @return mixed
     */
    abstract public function deleteMany($table, $where, $option = []);

    /**
     * Saves error message.
     *
     * @param string $error
     * @throws DbException
     */
    protected function saveError($error)
    {
        if ($error instanceof Throwable) {
            $this->lastError = $error->getMessage();
            $e = $error;
        } else {
            $this->lastError = $error;
            $e = null;
        }
        
        if ($this->debug) {
            throw new DbException($this->lastError, 0, $e);
        }
    }

    /**
     * Gets last error.
     *
     * @return string
     */
    public function getError()
    {
        return $this->lastError;
    }
}