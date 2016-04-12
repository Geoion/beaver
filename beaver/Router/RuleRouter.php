<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver\Router;

use Beaver\Http\Request;
use Beaver\Router;

/**
 * A router which dispatching action with given rules.
 *
 * @author You Ming
 */
class RuleRouter extends Router
{
    const TYPE_MAP = 1;
    const TYPE_STANDARD = 2;
    const TYPE_REGEX = 3;

    /**
     * @inheritdoc
     */
    protected function onDispatch()
    {
        $request = $this->context->getRequest();
        $rules = $this->getRegistry()->get('router.rules', []);

        $result = $this->matchRules($request, $rules);

        if ($result) {
            $controller = $result[0];
            $method = $result[1];

            // Resolved request arguments.
            $this->context->getRequest()->setAttributes($result[2], false);
        } else {
            $method = null;
            $controller = null;
        }

        $this->setResult($controller, $method);
    }

    /**
     * Gets a callable.
     *
     * @param string $callable
     * @return callable|null
     */
    protected function getCallable($callable)
    {
        if (is_string($callable) && $pos = strpos($callable, '.')) {
            $name = substr($callable, 0, $pos - 1);
            $method = substr($callable, $pos + 1);

            $object = $this->context->get($name);
            $callable = [$object, $method];
        }

        return is_callable($callable) ? $callable : null;
    }

    /**
     * Matches path with rules.
     *
     * @param Request $request
     * @param array $rules
     * @return array
     */
    protected function matchRules($request, $rules)
    {
        $path = trim($_SERVER['PATH_INFO'], '/');
        $paths = explode('/', $path);
        $method = $request->getMethod();

        foreach ($rules as $rule) {
            // Checks method.
            if (isset($rule['method']) && !$this->checkMethod($method, $rule['method'])) {
                continue;
            }

            $result = false;
            $type = isset($rule['type']) ? $rule['type'] : 'standard';
            switch ($type) {
                case 'map':
                    $result = $this->matchMapRule($path, $rule);
                    break;
                case 'standard':
                    $result = $this->matchSimpleRule($paths, $rule);
                    break;
                case 'regex':
                    $result = $this->matchRegexRule($path, $rule);
                    break;
            }

            if ($result) {
                return $result;
            }
        }

        return false;
    }

    /**
     * Checks method.
     *
     * @param string $method.
     * @param string|array $methods
     * @return bool
     */
    protected function checkMethod($method, $methods)
    {
        if (is_array($methods)) {
            return in_array($method, $methods);
        } else {
            return $method = $methods;
        }
    }

    /**
     * Matches a map rule.
     *
     * @param string $path
     * @param array $rule
     * @return array|bool
     */
    protected function matchMapRule($path, $rule)
    {
        if ($path !== $rule['rule']) {
            return false;
        }

        $controller = isset($rule['controller']) ? $rule['controller'] : null;
        $action = isset($rule['action']) ? $rule['action'] : null;
        $arguments = isset($rule['arguments']) ? $rule['arguments'] : [];

        return [$controller, $action, $arguments];
    }

    /**
     * Matches a simple rule.
     *
     * @param array $paths
     * @param array $rule
     * @return array|bool
     */
    protected function matchSimpleRule($paths, $rule)
    {
        $pieces = explode('/', $rule['rule']);

        $arguments = [];
        $value = reset($paths);
        foreach ($pieces as $piece) {
            if (':' !== $piece[0]) {
                if ($value !== $piece) {
                    return false;
                }
            } else {
                if ('[' === $piece[1]) {
                    $optional = true;
                    $name = substr($piece, 2, -1);
                } else {
                    $optional = false;
                    $name = substr($piece, 1);
                }

                if (isset($rule['parameters'][$name])) {
                    $parameter = $rule['parameters'][$name];

                    if (isset($parameter['filter']) && !$this->checkFilter($parameter['filter'], $value)) {
                        if ($optional) {
                            // Goto? Why not.
                            goto next;
                        } else {
                            return false;
                        }
                    }

                    if (isset($parameter['applier'])) {
                        $value = $this->apply($parameter['applier'], $value);
                    }
                }

                $arguments[$name] = $value;
            }

            next:
            $value = next($paths);
        }

        if (false !== $value && isset($rule['full']) && $rule['full']) {
            return false;
        }

        if (isset($rule['arguments'])) {
            $arguments = array_merge($rule['arguments'], $arguments);
        }

        $controller = isset($rule['controller']) ? $rule['controller'] : null;
        $action = isset($rule['action']) ? $rule['action'] : null;

        return [$controller, $action, $arguments];
    }

    /**
     * Matches a regex rule.
     *
     * @param array $path
     * @param array $rule
     * @return array|bool
     */
    protected function matchRegexRule($path, $rule)
    {
        if (preg_match($rule['rule'], $path, $matches)) {
            // Other arguments.
            $arguments = [];

            // Matched arguments.
            if (isset($route['parameters'])) {
                foreach ($route['parameters'] as $name => $parameter) {
                    if (is_array($parameter)) {
                        $value = $matches[$parameter['id']];

                        if (isset($parameter['filter']) && !$this->checkFilter($parameter['filter'], $value)) {
                            return false;
                        }
                        
                        if (isset($parameter['applier'])) {
                            $value = $this->apply($parameter['applier'], $value);
                        }

                        $arguments[$name] = $value;
                    } else {
                        $arguments[$name] = $matches[$parameter];
                    }
                }
            }

            if (isset($rule['arguments'])) {
                $arguments = array_merge($rule['arguments'], $arguments);
            }

            $controller = isset($rule['controller']) ? $rule['controller'] : null;
            $action = isset($rule['action']) ? $rule['action'] : null;

            return [$controller, $action, $arguments];
        } else {
            return false;
        }
    }

    /**
     * Checks whether the value is adapted to a given filter.
     *
     * @param string $filter
     * @param mixed $value
     * @return bool
     */
    protected function checkFilter($filter, $value)
    {
        if ('-d' === $filter) {
            return preg_match('/^\d*$/', $value);
        } elseif (is_array($filter)) {
            if (null !== $filter[0]) {
                return in_array($value, $filter);
            } else {
                return !in_array($value, $filter);
            }
        }

        $filter = $this->getCallable($filter);

        return null === $filter || call_user_func($filter, $value);
    }

    /**
     * Apply a value with a callable .
     *
     * @param string $applier
     * @param mixed $value
     * @return mixed
     */
    protected function apply($applier, $value)
    {
        $applier = $this->getCallable($applier);

        return null === $applier ? $applier : call_user_func($applier, $value);
    }
}