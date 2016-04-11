<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver\Router;

use Beaver\Router;

/**
 * A router that dispatching action as method.
 *
 * @author You Ming
 */
class ActionRouter extends Router
{
    /**
     * @inheritdoc
     */
    protected function onDispatch()
    {
        $path = trim($_SERVER['PATH_INFO'], '/');

        $paths = [];
        foreach (explode('/', $path) as $name) {
            $paths[] = $this->parseName($name);
        }

        $pieces = count($paths);
        if ($pieces > 1) {
            $method = array_pop($paths);
            $controller = implode('\\', $paths);
        } elseif ($pieces == 1) {
            $method = null;
            $controller = array_pop($paths);
        } else {
            $method = null;
            $controller = null;
        }

        $this->setResult($controller, $method);
    }

    /**
     * Parses name.
     *
     * @param string $name
     * @return string
     */
    protected function parseName($name)
    {
        return ucfirst(preg_replace_callback('/_([a-zA-Z])/', function($match) {
            return strtoupper($match[1]);
        }, $name));
    }
}