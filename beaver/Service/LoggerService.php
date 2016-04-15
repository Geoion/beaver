<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver\Service;

use Beaver\Exception\LoggerException;
use Beaver\Logger;
use Beaver\Service;

/**
 * A service which provides logger operations.
 *
 * @author You Ming
 */
class LoggerService extends Service
{
    /**
     * Configs.
     *
     * @var array
     */
    protected $configs;

    /**
     * A handler of logger.
     *
     * @var Logger
     */
    protected $handler;

    /**
     * @inheritdoc
     */
    protected $defer = true;

    /**
     * Gets the handler for logger.
     *
     * @return Logger
     */
    public function getLogger()
    {
        return $this->handler;
    }

    /**
     * @inheritdoc
     */
    protected function onRegister()
    {
        $configs = [
            'class' => $this->getRegistry()->get('logger.class'),
            'options' => $this->getRegistry()->get('logger')
        ];

        if (empty($configs['class'])) {
            throw new LoggerException('A class of logger must be defined in the registry.');
        }

        $this->configs = $configs;

        $this->provide('logger');
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
        return $this->getLogger();
    }
}