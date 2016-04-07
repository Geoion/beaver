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
        $configs = [];

        $configs['class'] = $this->getRegistry()->get('service.cache.class');
        $configs['options'] = $this->getRegistry()->get('service.cache');

        $this->configs = $configs;
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
}