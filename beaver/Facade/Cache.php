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
use Beaver\Service\CacheService;

/**
 * A facade of cache.
 *
 * @method static mixed get($name, $default = null);
 * @method static bool set($name, $value, $expiry = null);
 * @method static bool exist($name)
 * @method static bool delete($name);
 * @method static bool clear($name);
 *
 * @author You Ming
 */
final class Cache extends Facade
{
    /**
     * @inheritdoc
     */
    protected static function getAccessor()
    {
        /** @var CacheService $service */
        $service = static::$context->getService(CacheService::class);
        if (null === $service) {
            throw new FacadeException('The cache facade needs CacheService.');
        }

        return $service->getCache();
    }
}
