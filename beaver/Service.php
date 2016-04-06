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
 * Base class for service.
 *
 * @author You Ming
 */
class Service
{
    use ContextInjection;

    /**
     * Indicates whether loading of the service is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Whether this service is started.
     *
     * @var bool
     */
    private $started = false;

    /**
     * Determines whether this service is starting deferred.
     *
     * @return bool
     */
    public function isDeferred()
    {
        return $this->defer;
    }

    /**
     * Determines whether this service is started.
     *
     * @return bool
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * Registers this service to an app instance.
     */
    public function register()
    {
        $this->onRegister();
    }

    /**
     * Starts this service.
     */
    public function start()
    {
        if (!$this->started) {
            $this->onStart();
            $this->started = true;
        }
    }

    /**
     * Stops this service.
     */
    public function stop()
    {
        if ($this->started) {
            $this->onStop();
            $this->started = false;
        }
    }

    /**
     * Called when this service is registering.
     */
    protected function onRegister()
    {
    }

    /**
     * Called when this service is starting.
     */
    protected function onStart()
    {
    }

    /**
     * Called when this service is stopping.
     */
    protected function onStop()
    {
    }
}