<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver;

use Beaver\Exception\Http\NotFoundException;
use Beaver\Traits\ContextInjection;

/**
 * Base class for those who need to maintain global application state. You can
 * provide your own implementation by specifying its name in your registry.
 *
 * @author You Ming
 */
class App
{
    use ContextInjection;

    /**
     * Initializes this app.
     */
    public function initialize()
    {
        if ($this->getRegistry()->exist('app.poweredBy')) {
            header('X-Powered-By: ' . $this->getRegistry()->get('app.poweredBy'));
        }

        date_default_timezone_set($this->getRegistry()->get('app.timezone', 'Asia/Shanghai'));

        // Initializes facade.
        Facade::bindContext($this->context);

        // Register cookie operator.
        $this->context->singleton([Cookie::class => 'cookie'], function ($context) {
            $options = $context->get(Registry::class)->get('cookie');
            return new Cookie($options);
        });

        // Initializes services.
        $services = $this->getRegistry()->get('app.services', []);
        foreach ($services as $service) {
            $this->context->registerService($service);
        }

        $this->onCreate();
    }

    /**
     * Running.
     */
    public function run()
    {
        // Dispatches url.
        $result = $this->dispatch();
        $controller = $result['controller'];
        $method = $result['method'];

        $this->onDispatched($controller, $method);
        
        // Checks controller.
        if (null === $controller) {
            throw new NotFoundException("Dispatched with none result.");
        } elseif (!class_exists($controller)) {
            throw new NotFoundException("Controller $controller not found.");
        }
        
        $this->control($controller, $method);
        
        $this->onStop();

        // Closes all services.
        $this->context->unregisterServices();
    }

    /**
     * Dispatches controller.
     *
     * @return array
     */
    protected function dispatch()
    {
        $routerClass = $this->getRegistry()->get('router.class');
        /** @var Router $router */
        $router = $this->context->get($routerClass);
        // Shares it.
        $this->context->shareInstance([Router::class => 'router'], $router);
        // Dispatches url.
        $router->dispatch();
        
        return $router->getResult();
    }

    /**
     * Hands over control to the controller.
     *
     * @param string $controller The class name of dispatched controller.
     * @param string $method The name of method to be run.
     * @throws NotFoundException
     */
    protected function control($controller, $method)
    {
        /** @var Controller $ctrl */
        $c = $this->context->get($controller);
        // Initializes controller.
        $c->initialize($method);

        $this->onStart();

        $c->run($method);
    }

    /**
     * Called when the app is creating.
     */
    protected function onCreate()
    {
    }

    /**
     * Called when url dispatching has just completed.
     *
     * @param string $controller The class name of dispatched controller.
     * @param string $method The name of method to be run.
     */
    protected function onDispatched($controller, $method)
    {
    }

    /**
     * Called when the method in controller start to run .
     */
    protected function onStart()
    {
    }

    /**
     * Called when the app is stopping.
     */
    protected function onStop()
    {
    }
}