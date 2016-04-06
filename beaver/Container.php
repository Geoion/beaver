<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver;

use Beaver\Exception\InstantiationException;
use ReflectionClass;
use ReflectionParameter;

/**
 * The dependency injection container.
 *
 * @author You Ming
 */
class Container
{
    /**
     * The aliases for types.
     *
     * @var array
     */
    protected $aliases = [];

    /**
     * The instances that have been shared.
     *
     * @var array
     */
    protected $instances = [];

    /**
     * The instance builders.
     *
     * @var array
     */
    protected $builders = [];

    /**
     * Sets an alias for a class.
     *
     * @param string $class The class name.
     * @param string $alias The alias.
     */
    public function setAlias($class, $alias)
    {
        $this->aliases[$alias] = $this->normalize($class);
    }

    /**
     * Extracts the class name from an alias if available.
     *
     * @param string $alias The alias for class.
     * @return string
     */
    protected function parseAlias($alias)
    {
        return isset($this->aliases[$alias]) ? $this->aliases[$alias] : $alias;
    }

    /**
     * Registers an instance builder with the container.
     *
     * @param string|array $class The class name.
     * @param callable|string $builder The instance builder.
     * @param bool $share If true, the instance will be shared.
     */
    public function register($class, $builder = null, $share = false)
    {
        $class = $this->normalize($class);
        $builder = $this->normalize($builder);

        // Extracts [$class => $alias] definition.
        if (is_array($class)) {
            $alias = current($class);
            $class = $this->normalize(key($class));

            $this->setAlias($class, $alias);
        }

        // Drops stale instance and alias.
        unset($this->instances[$class], $this->aliases[$class]);

        if (null === $builder) {
            $builder = $class;
        }

        $this->builders[$class] = [
            'builder' => $builder,
            'share' => $share
        ];
    }

    /**
     * Registers a shared instance in the container.
     *
     * @param string|array $class
     * @param object $instance
     */
    public function shareInstance($class, $instance)
    {
        $class = $this->normalize($class);

        if (is_array($class)) {
            $alias = current($class);
            $class = $this->normalize(key($class));

            $this->setAlias($class, $alias);
        }

        // Drops stale alias.
        unset($this->aliases[$class]);

        $this->instances[$class] = $instance;
    }

    /**
     * Determines whether a given type is shared.
     *
     * @param string $class
     * @return bool
     */
    public function isShared($class)
    {
        $class = $this->normalize($class);

        if (isset($this->instances[$class])) {
            return true;
        }

        if (!isset($this->builders[$class]['share'])) {
            return false;
        }

        return $this->$class[$class]['share'] === true;
    }

    /**
     * Gets an instance of given type from the container.
     *
     * @param string $class The class name.
     * @param array $arguments The extra arguments for constructor.
     * @return mixed
     */
    public function get($class, array $arguments = [])
    {
        $class = $this->normalize($class);
        $class = $this->parseAlias($class);

        // If an instance of the type has been shared as a singleton, just return it.
        if (isset($this->instances[$class])) {
            return $this->instances[$class];
        }

        $builder = $this->getBuilder($class);

        if ($this->isBuildable($builder, $class)) {
            $instance = $this->build($builder, $arguments);
            $this->onBuilt($instance);
        } else {
            $instance = $this->get($builder, $arguments);
        }

        // If the class marked sharing, make this instance share as a singleton.
        if ($this->isShared($class)) {
            $this->instances[$class] = $instance;
        }

        return $instance;
    }

    /**
     * Gets the builder for given class.
     *
     * @param string $class
     * @return mixed
     */
    protected function getBuilder($class)
    {
        return isset($this->builders[$class]) ? $this->builders[$class]['builder'] : $class;
    }

    /**
     * Determines whether the given builder is buildable.
     *
     * @param mixed $builder The instance builder.
     * @param string $class The class name.
     * @return bool
     */
    protected function isBuildable($builder, $class)
    {
        return $builder === $class || is_callable($builder);
    }

    /**
     * Instantiates an instance with the given builder.
     *
     * @param callable|string $builder
     * @param array $arguments
     * @return mixed
     * @throws InstantiationException
     */
    public function build($builder, array $arguments = [])
    {
        // If the builder is callable, just execute it and hand back the results of it.
        if (is_callable($builder)) {
            return call_user_func($builder, $this, $arguments);
        }

        $reflector = new ReflectionClass($builder);

        // Checks whether the class is instantiable.
        if (!$reflector->isInstantiable()) {
            throw new InstantiationException("$builder is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        // If there are no constructor, just instantiate the instance.
        if (null === $constructor) {
            return new $builder;
        }

        $parameters = $constructor->getParameters();
        $arguments = $this->reKeyArguments($parameters, $arguments);
        $dependencies = $this->resolveDependencies($parameters, $arguments);

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * If extra arguments are passed by numeric Id, re key them by parameter name.
     *
     * @param ReflectionParameter[] $parameters
     * @param array $arguments
     * @return array
     */
    protected function reKeyArguments(array $parameters, array $arguments)
    {
        foreach ($arguments as $key => $value) {
            if (is_numeric($key)) {
                unset($arguments[$key]);
                $arguments[$parameters[$key]->name] = $value;
            }
        }

        return $arguments;
    }

    /**
     * Resolves all the dependencies with the parameters.
     *
     * @param ReflectionParameter[] $parameters
     * @param array $arguments
     * @return array
     */
    protected function resolveDependencies(array $parameters, array $arguments = [])
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            if (array_key_exists($parameter->name, $arguments)) {
                $dependencies[] = $arguments[$parameter->name];
            } elseif (null === $parameter->getClass()) {
                $dependencies[] = $this->resolveScalar($parameter);
            } else {
                $dependencies[] = $this->resolveClass($parameter);
            }

        }
        return $dependencies;
    }

    /**
     * Resolves a scalar dependency.
     *
     * @param ReflectionParameter $parameter
     * @return mixed
     * @throws InstantiationException
     */
    protected function resolveScalar(ReflectionParameter $parameter)
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new InstantiationException('Unable to resole the dependency $' . $parameter->name . ' in '
            . $parameter->getDeclaringClass()->getName());
    }

    /**
     * Resolves a object dependency.
     *
     * @param ReflectionParameter $parameter
     * @return mixed
     * @throws InstantiationException
     */
    protected function resolveClass(ReflectionParameter $parameter)
    {
        try {
            return $this->get($parameter->getClass()->name);
        } catch (InstantiationException $e) {
            // Try to find a default value.
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            throw $e;
        }
    }

    /**
     * Normalizes the class name.
     *
     * @param mixed $service
     * @return mixed
     */
    protected function normalize($service)
    {
        return is_string($service) ? ltrim($service, '\\') : $service;
    }

    /**
     * Called when an instance has just built.
     *
     * @param object $instance
     */
    protected function onBuilt($instance)
    {
    }
}