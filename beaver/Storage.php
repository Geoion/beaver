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
 * Base class for storage.
 *
 * @author You Ming
 */
abstract class Storage
{
    use ContextInjection;

    /**
     * Connects to the storage server.
     *
     * @param array $options
     * @return bool
     */
    abstract public function connect(array $options = []);

    /**
     * Closes the connection to the storage server.
     */
    abstract public function close();

    /**
     * Gets a list of names for all buckets.
     *
     * @return array|bool
     */
    abstract public function listBuckets();

    /**
     * Gets a list of names for all item in a given bucket. Never try to list items
     * in a bucket which may contains large amount of items.
     *
     * @param string $bucket The name of bucket.
     * @param int $skip
     * @param int $limit
     * @return array|bool
     */
    abstract public function listItems($bucket, $skip = 0, $limit = -1);

    /**
     * Gets information of a storage item.
     *
     * @param string $bucket
     * @param string $name
     * @return array|bool
     */
    abstract public function info($bucket, $name);

    /**
     * Reads a storage item from a given bucket.
     *
     * @param string $bucket The name of bucket.
     * @param string $name The name of item.
     * @return string|bool
     */
    abstract public function read($bucket, $name);

    /**
     * Writes data to a storage item.
     *
     * @param string $bucket The name of bucket.
     * @param string $name The name of item.
     * @param string $data
     * @return bool
     */
    abstract public function write($bucket, $name, $data);

    /**
     * Appends data to a storage item.
     *
     * @param string $bucket The name of bucket.
     * @param string $name The name of item.
     * @param string $data
     * @return bool
     */
    abstract public function append($bucket, $name, $data);

    /**
     * Checks whether an item exists in the storage.
     *
     * @param string $bucket The name of bucket.
     * @param string $name The name of item.
     * @return bool
     */
    abstract public function exist($bucket, $name);

    /**
     * Deletes storage item in a given bucket.
     *
     * @param string $bucket The name of bucket.
     * @param string $name The name of item.
     * @return bool
     */
    abstract public function delete($bucket, $name);

    /**
     * Flushes all item in a bucket.
     *
     * @param string $bucket The name of bucket.
     * @return bool
     */
    abstract public function flush($bucket);
}