<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver\Facade;

use Beaver\Exception\FacadeException;
use Beaver\Facade;
use Beaver\Service\StorageService;

/**
 * A facade of storage.
 *
 * @method static array|bool listBuckets();
 * @method static array|bool listItems($bucket, $skip = 0, $limit = -1);
 * @method static array|bool info($bucket, $name);
 * @method static string|bool read($bucket, $name);
 * @method static bool write($bucket, $name, $data);
 * @method static bool append($bucket, $name, $data);
 * @method static bool exist($bucket, $name);
 * @method static bool delete($bucket, $name);
 * @method static bool flush($bucket);
 *
 * @author You Ming
 */
class Storage extends Facade
{
    /**
     * A handler for cache.
     *
     * @var \Beaver\Storage
     */
    protected static $handler = null;

    /**
     * Gets cache handler from cache service.
     */
    protected static function getHandler()
    {
        /** @var StorageService $service */
        $service = static::$context->getService(StorageService::class);
        if (null === $service) {
            throw new FacadeException('The storage facade needs StorageService.');
        }

        return $service->getStorage();
    }

    /**
     * @inheritdoc
     */
    protected static function getAccessor()
    {
        if (null === static::$handler) {
            static::$handler = static::getHandler();
        }

        return static::$handler;
    }
}
