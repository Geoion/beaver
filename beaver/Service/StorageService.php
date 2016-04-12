<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver\Service;

use Beaver\Exception\StorageException;
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
        $configs = [
            'class' => $this->getRegistry()->get('storage.class'),
            'options' => $this->getRegistry()->get('storage')
        ];

        if (empty($configs['class'])) {
            throw new StorageException('A class of storage must be defined in the registry.');
        }

        $this->configs = $configs;

        $this->provide('storage');
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
        return $this->getStorage();
    }
}