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
 * A router which dispatching action with given rules.
 *
 * @author You Ming
 */
class RuleRouter extends Router
{
    /**
     * @inheritdoc
     */
    protected function onDispatch()
    {
        $path = trim($_SERVER['PATH_INFO'], '/');

        $rules = $this->getRegistry()->get('router.rules', []);
        $rules = $this->parseRules($rules);

        $result = $this->matchPath($path, $rules);

        $paths = explode('/', $result[0]);
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

        // Resolved request arguments.
        $this->context->getRequest()->setAttributes($result[1], false);

        $this->setResult($controller, $method);
    }

    /**
     * Gets a callable.
     *
     * @param string $callable
     * @return array|bool
     */
    protected function getCallable($callable)
    {
        if (is_string($callable)) {
            if ($pos = strpos($callable, '.')) {
                $name = substr($callable, 0, $pos - 1);
                $method = substr($callable, $pos + 1);

                $object = $this->context->get($name);
                $callable = [$object, $method];
            } elseif ($pos = strpos($callable, '@')) {
                $name = substr($callable, 0, $pos - 1);
                $method = substr($callable, $pos + 1);

                $name = $this->context->parseAlias($name);
                $callable = [$name, $method];
            }
        }

        return is_callable($callable) ? $callable : null;
    }

    /**
     * Parses all rules for operation.
     *
     * @param array $rules
     * @return array
     */
    protected function parseRules($rules)
    {
        $result = [];

        foreach ($rules as $rule => $route) {
            if (!is_array($route)) {
                $route = [$route];
            }

            if (isset($route[1]) && is_string($route[1])) {
                parse_str($route[1], $route[1]);
            }
            $parameters = isset($route[1]) ? $route[1] : [];

            if (false !== strpos($route[0], '?')) {
                list($route[0], $query) = explode('?', $route[0], 2);
                if ($query) {
                    parse_str($query, $queries);
                    $parameters = array_merge($parameters, $queries);
                }
            }

            if ('/' === $rule[0]) {
                $matches = [];
                foreach ($parameters as $name => $value) {
                    if (is_string($value) && $value && ':' === $value[0]) {
                        if ($pos = strpos($value, '|')) {
                            $apply = substr($value, $pos + 1);
                            $value = substr($value, 1, $pos - 1);
                        }

                        $value = substr($value, 1);
                        $value = (int) $value;

                        if ($value) {
                            if (isset($apply)) {
                                $matches[$name] = [$value, $apply];
                            } else {
                                $matches[$name] = $value;
                            }

                            unset($parameters[$name]);
                        }
                    }
                }

                $result['regex'][$rule] = [$route[0], $matches, $parameters];
            } else {
                if ('>' === $rule[0]) {
                    $result['map'][$rule] = [$route[0], $parameters];
                } else {
                    $matches = [];
                    foreach (explode('/', rtrim($rule, '$')) as $piece) {
                        if (':' === $piece[0] || '[' === $piece[0]) {
                            if ('[' === $piece[0]) {
                                $piece = trim($piece, '[]');
                                $optional = 2;
                            } else {
                                $optional = 1;
                            }

                            if ($pos = strpos($piece, '|')) {
                                $apply = substr($piece, $pos + 1);
                                $piece = substr($piece, 1, $pos - 1);
                            }

                            if ($pos = strpos($piece, '~')) {
                                $filter = explode(',', substr($piece, $pos + 1));
                                $name = substr($piece, 1, $pos - 1);
                            } elseif ($pos = strpos($piece, '\\')) {
                                $filter = substr($piece, $pos + 1);
                                $name = substr($piece, 1, $pos - 1);

                                if ('d' === substr($piece, -1)) {
                                    $filter = '-d';
                                }
                            } else {
                                $name = substr($piece, 1);
                            }

                            if (isset($apply)) {
                                $filter = isset($filter) ? $filter : '-';
                                $matches[] = [$name, $optional, $filter, $apply];
                                unset($filter, $apply);
                            } elseif (isset($filter)) {
                                $matches[] = [$name, $optional, $filter];
                                unset($filter);
                            } else {
                                $matches[] = [$name, $optional];
                            }
                        } else {
                            $matches[] = [$piece, 0];
                        }
                    }

                    $result['simple'][$rule] = [$route[0], $matches, $parameters];
                }
            }
        }

        return $result;
    }

    /**
     * Matches path with rules.
     *
     * @param string $path
     * @param array $rules
     * @return array
     */
    protected function matchPath($path, $rules)
    {
        if (isset($rules['map']['>' . $path])) {
            $path = '>' . $path;
            $result = $rules['map'][$path][0];
            $arguments = isset($rules['map'][$path][1]) ? $rules['map'][$path][1] : [];

            return [$result, $arguments];
        }

        if (isset($rules['simple'])) {
            $paths = explode('/', $path);
            foreach ($rules['simple'] as $rule => $route) {
                $result = $this->matchSimpleRule($paths, $rule, $route);
                if ($result) {
                    return $result;
                }
            }
        }

        if (isset($rules['regex'])) {
            foreach ($rules['regex'] as $rule => $route) {
                $result = $this->matchRegexRule($path, $rule, $route);
                if ($result) {
                    return $result;
                }
            }
        }

        // Default.
        return ['', []];
    }

    /**
     * Matches a simple rule.
     *
     * @param array $paths
     * @param string $rule
     * @param array $route
     * @return array|bool
     */
    protected function matchSimpleRule($paths, $rule, $route)
    {
        if ('$' === substr($rule, -1) && count($route[1]) != count($paths)) {
            return false;
        }

        $arguments = [];
        $pos = 0;
        foreach ($route[1] as $value) {
            $piece = isset($paths[$pos]) ? $paths[$pos] : null;

            if (0 === $value[1]) {
                if (0 !== strcasecmp($paths[$pos], $value[0])) {
                    return false;
                }
            } else {
                $optional = $value[1] === 2;

                if (null !== $piece) {
                    if (isset($value[2]) && '-' !== $value[2]) { // Filter
                        $excluded = false;

                        if ('-d' === $value[2] && !preg_match('/^\d*$/', $piece)) {
                            $excluded = true;
                        } elseif (is_array($value[2]) && in_array($piece, $value[2])) {
                            $excluded = true;
                        } elseif ($filter = $this->getCallable($value[2])) {
                            if (!call_user_func($filter, $piece, $this->context)) {
                                $excluded = true;
                            }
                        }

                        if ($excluded) {
                            if ($optional) {
                                continue;
                            } else {
                                return false;
                            }
                        }
                    }

                    if (isset($value[3]) && $apply = $this->getCallable($value[3])) {
                        $piece = call_user_func($apply, $piece, $this->context);
                    }

                    $arguments[$value[0]] = $piece;

                } elseif (!$optional) {
                    return false;
                }
            }

            $pos++;
        }

        if (isset($route[2])) {
            $arguments = array_merge($route[2], $arguments);
        }

        return [$route[0], $arguments];
    }

    /**
     * Matches a regex rule.
     *
     * @param array $path
     * @param string $rule
     * @param array $route
     * @return array|bool
     */
    protected function matchRegexRule($path, $rule, $route)
    {
        if (preg_match($rule, $path, $matches)) {
            $arguments = [];
            // Other arguments.
            if (isset($route[2])) {
                $arguments = $route[2];
            }
            // Matched arguments.
            if (isset($route[1])) {
                foreach ($route[1] as $name => $value) {
                    if (is_array($value)) {
                        if (isset($value[1]) && $apply = $this->getCallable($value[1])) {
                            $arguments[$name] = call_user_func($apply, $matches[$value[0]], $this->context);
                        } else {
                            $arguments[$name] = $matches[$value[0]];
                        }
                    } else {
                        $arguments[$name] = $matches[$value];
                    }
                }
            }

            return [$route[0], $arguments];
        } else {
            return false;
        }
    }
}