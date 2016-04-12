<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver\Model;

use Beaver\Db;
use Beaver\Model;
use RuntimeException;

/**
 * A model for database data.
 *
 * @author You Ming
 */
class DbModel extends Model
{
    /**
     * The name of database's table.
     *
     * @var string
     */
    protected static $tableName;

    /**
     * The handler for database.
     *
     * @var Db
     */
    protected static $db = null;

    /**
     * Resolves a handler for database. The handler must be connected before return.
     *
     * @return Db
     */
    protected static function resolveDb()
    {
        throw new RuntimeException('Subclass must implement resolveDb method.');
    }

    /**
     * Gets a handler for this model.
     *
     * @return Db
     */
    protected static function getDb()
    {
        if (null === static::$db) {
            static::$db = static::resolveDb();
        }

        return static::$db;
    }

    /**
     * Counts the number of records.
     *
     * @param array $where
     * @param array $options
     * @return mixed
     */
    protected function count($where = [], $options = [])
    {
        return static::getDb()->count(static::$tableName, $where, $options);
    }

    /**
     * Inserts an record.
     *
     * @param array $data
     * @param array $options
     * @return mixed
     */
    protected function insertOne($data, $options = [])
    {
        return static::getDb()->insertOne(static::$tableName, $data, $options);
    }

    /**
     * Inserts multiple records.
     *
     * @param array $data
     * @param array $options
     * @return mixed
     */
    protected function insertMany(array $data, $options = [])
    {
        return static::getDb()->insertMany(static::$tableName, $data, $options);
    }

    /**
     * Queries and returns a single record.
     *
     * @param array|string $where
     * @param array|string $fields
     * @param array $options
     * @return mixed
     */
    protected function findOne($where, $fields = [], $options = [])
    {
        return static::getDb()->findOne(static::$tableName, $where, $fields, $options);
    }

    /**
     * Queries and returns an array for the result set.
     *
     * @param array|string $where
     * @param array|string $fields
     * @param array $options
     * @return mixed
     */
    protected function findMany($where, $fields = [], $options = [])
    {
        return static::getDb()->findMany(static::$tableName, $where, $fields, $options);
    }

    /**
     * Update first record based on a given criteria.
     *
     * @param array|string $where
     * @param array|string $data
     * @param array $options
     * @return mixed
     */
    protected function updateOne($where, $data, $options = [])
    {
        return static::getDb()->updateOne(static::$tableName, $where, $data, $options);
    }

    /**
     * Updates records based on a given criteria.
     *
     * @param array|string $where
     * @param array|string $data
     * @param array $options
     * @return mixed
     */
    protected function updateMany($where, $data, $options = [])
    {
        return static::getDb()->updateMany(static::$tableName, $where, $data, $options);
    }

    /**
     * Delete first record from this collection based on a given criteria.
     *
     * @param array|string $where
     * @param array $options
     * @return mixed
     */
    protected function deleteOne($where, $options = [])
    {
        return static::getDb()->deleteOne(static::$tableName, $where, $options);
    }

    /**
     * Deletes records from this collection.
     *
     * @param array|string $where
     * @param array $options
     * @return mixed
     */
    protected function deleteMany($where, $options = [])
    {
        return static::getDb()->deleteMany(static::$tableName, $where, $options);
    }
}