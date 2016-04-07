<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver\Service;

use Beaver\Storage;
use Beaver\Service;

/**
 * A service which provides storage operations.
 *
 * @author You Ming
 */
class StorageService extends Service
{
    /**
     * Configs.
     *
     * @var array
     */
    protected $configs;

    /**
     * A handler of storage.
     *
     * @var Storage
     */
    protected $handler;

    /**
     * @inheritdoc
     */
    protected $defer = true;

    /**
     * Gets the handler for storage.
     *
     * @return Storage
     */
    public function getStorage()
    {
        return $this->handler;
    }

    /**
     * @inheritdoc
     */
    protected function onRegister()
    {
        $configs = [];

        $configs['class'] = $this->getRegistry()->get('service.storage.class');
        $configs['options'] = $this->getRegistry()->get('service.storage');

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