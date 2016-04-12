<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver\Service;

use Beaver\Cache;
use Beaver\Exception\CacheException;
use Beaver\Service;

/**
 * A service which provides cache operations.
 *
 * @author You Ming
 */
class CacheService extends Service
{
    /**
     * Configs.
     *
     * @var array
     */
    protected $configs;

    /**
     * A handler of caching.
     *
     * @var Cache
     */
    protected $handler;

    /**
     * @inheritdoc
     */
    protected $defer = true;

    /**
     * Gets the handler for caching.
     *
     * @return Cache
     */
    public function getCache()
    {
        return $this->handler;
    }

    /**
     * @inheritdoc
     */
    protected function onRegister()
    {
        $configs = [
            'class' => $this->getRegistry()->get('cache.class'),
            'options' => $this->getRegistry()->get('cache')
        ];

        if (empty($configs['class'])) {
            throw new CacheException('A class of cache must be defined in the registry.');
        }

        $this->configs = $configs;

        $this->provide('cache');
    }

    /**
     * @inheritdoc
     */
    protected function onStart()
    {
        $this->handler = $this->context->get($this->configs['class']);
        $this->handler->connect($this->configs['options']);
    }

    /**
     * @inheritdoc
     */
    protected function onStop()
    {
        $this->handler->close();
    }

    /**
     * @inheritdoc
     */
    protected function onProvide($name)
    {
        return $this->getCache();
    }
}