<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver\Service;

use Beaver\Service;
use Beaver\Session;

/**
 * A service which provides session operations.
 *
 * @author You Ming
 */
class SessionService extends Service
{
    /**
     * Configs.
     *
     * @var array
     */
    protected $configs;

    /**
     * A handler of session.
     *
     * @var Session
     */
    protected $handler;

    /**
     * @inheritdoc
     */
    protected $defer = true;

    /**
     * Gets the handler for session.
     *
     * @return Session
     */
    public function getSession()
    {
        return $this->handler;
    }

    /**
     * @inheritdoc
     */
    protected function onRegister()
    {
        $configs = [
            'class' => $this->getRegistry()->get('session.class'),
            'options' => $this->getRegistry()->get('session')
        ];

        if (empty($configs['class'])) {
            $configs['class'] = Session::class;
        }

        $this->configs = $configs;

        $this->provide('session');
    }

    /**
     * @inheritdoc
     */
    protected function onStart()
    {
        $this->handler = $this->context->get($this->configs['class']);
        $this->handler->connect($this->configs['options']);
        $this->handler->start();
    }

    /**
     * @inheritdoc
     */
    protected function onStop()
    {
        $this->handler->commit();
    }

    /**
     * @inheritdoc
     */
    protected function onProvide($name)
    {
        return $this->getSession();
    }
}