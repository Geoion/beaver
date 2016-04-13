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
 * Base class for url router. You can provide your own implementation by specifying
 * its name in your registry.
 *
 * @author You Ming
 */
abstract class Router
{
    use ContextInjection;
    
    /**
     * The controller class name.
     *
     * @var string
     */
    private $controller;

    /**
     * The method name.
     *
     * @var string
     */
    private $method;

    /**
     * The original controller name from dispatch result.
     *
     * @var string
     */
    private $originalController;

    /**
     * The original method name from dispatch result.
     *
     * @var string
     */
    private $originalMethod;

    /**
     * Dispatches url.
     */
    public function dispatch()
    {
        // Resets result.
        $this->controller = null;
        $this->method = null;

        $path = $_SERVER['PATH_INFO'];

        $this->onDispatch($path);
    }

    /**
     * Called when dispatching controller.
     *
     * @param string $path
     */
    protected function onDispatch($path)
    {
    }

    /**
     * Sets the dispatched result.
     *
     * @param string $controller
     * @param string $method
     */
    protected function setResult($controller, $method)
    {
        if (empty($controller)) {
            $controller = $this->getRegistry()->get('router.controller.default', 'Home');
        }

        $this->originalController = $controller;

        if (null !== $controller) {
            $packageName = $this->context->getPackageName();
            $namespace = $this->getRegistry()->get('router.controller.namespace', 'Controller');
            $postfix = $this->getRegistry()->get('router.controller.postfix', 'Controller');
            $controller = '\\' . $packageName . '\\' . $namespace . '\\' . $controller . $postfix;
        }

        if (empty($method)) {
            $method = $this->getRegistry()->get('router.method.default', 'index');
        }
        
        $this->originalMethod = $method;

        if (null !== $method) {
            $prefix = $this->getRegistry()->get('router.method.prefix', '');
            $postfix = $this->getRegistry()->get('router.method.postfix', '');

            $method = lcfirst($prefix . $method . $postfix);
        }
        
        $this->controller = $controller;
        $this->method = $method;
    }

    /**
     * Gets the dispatched result.
     *
     * @return array
     */
    public function getResult()
    {
        return [
            'controller' => $this->controller,
            'method' => $this->method
        ];
    }

    /**
     * Gets the controller class name from dispatched result.
     *
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Gets the method name from dispatched result.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Gets the original controller name from dispatched result.
     *
     * @return string
     */
    public function getOriginalController()
    {
        return $this->originalController;
    }

    /**
     * Gets the original method name from dispatched result.
     *
     * @return string
     */
    public function getOriginalMethod()
    {
        return $this->originalMethod;
    }
}