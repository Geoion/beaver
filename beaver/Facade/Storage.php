<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver\Facade;

use Beaver\Facade;

/**
 * A facade of Storage.
 *
 * @method static array|bool listBuckets()
 * @method static array|bool listItems($bucket, $skip = 0, $limit = -1)
 * @method static array|bool info($bucket, $name)
 * @method static string|bool read($bucket, $name)
 * @method static bool write($bucket, $name, $data)
 * @method static bool append($bucket, $name, $data)
 * @method static bool exist($bucket, $name)
 * @method static bool delete($bucket, $name)
 * @method static bool flush($bucket)
 *
 * @author You Ming
 */
final class Storage extends Facade
{
    /**
     * @inheritdoc
     */
    protected static function getAccessor()
    {
        return 'storage';
    }
}
