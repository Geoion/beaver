<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver\Registry;

use Beaver\Registry;
use Beaver\Util\Arrays;

/**
 * A registry which reads registry entries in different file, scoped by the start of
 * the name of registry entry. The value of a entry in this registry can be
 * modified, but it only valid in current request lifecycle.
 *
 * @author You Ming
 */
class FileRegistry extends Registry
{
    /**
     * The directory that stores all entries in each file by scope.
     *
     * @var string
     */
    protected $directory;

    /**
     * The array contains all entries.
     *
     * @var array
     */
    protected $entries = [];

    /**
     * Constructor.
     *
     * @param string $directory
     */
    public function __construct($directory)
    {
        $this->directory = realpath($directory) . DIRECTORY_SEPARATOR;
    }

    /**
     * @inheritdoc
     */
    public function get($name, $default = null)
    {
        list($scope, $name) = explode('.', $name, 2);

        if (!isset($this->entries[$scope])) {
            $this->loadScope($scope);
        }

        return Arrays::dotGet($this->entries[$scope], $name, $default);
    }

    /**
     * @inheritdoc
     */
    public function set($name, $value)
    {
        list($scope, $name) = explode('.', $name, 2);

        if (!isset($this->entries[$scope])) {
            $this->loadScope($scope);
        }

        Arrays::dotSet($this->entries[$scope], $name, $value);
    }

    /**
     * @inheritdoc
     */
    public function exist($name)
    {
        list($scope, $name) = explode('.', $name, 2);

        if (!isset($this->entries[$scope])) {
            $this->loadScope($scope);
        }

        return Arrays::dotExist($this->entries[$scope], $name);
    }

    /**
     * @inheritdoc
     */
    public function delete($name)
    {
        list($scope, $name) = explode('.', $name, 2);

        if (!isset($this->entries[$scope])) {
            $this->loadScope($scope);
        }

        Arrays::dotDelete($this->entries[$scope], $name);
    }

    // Loads all registry entries from file in a given scope.
    protected function loadScope($scope)
    {
        $path = $this->directory . $scope . '.php';

        if (is_file($path)) {
            $entries = include $path;
        } else {
            $entries = [];
        }

        $this->entries[$scope] = $entries;
    }
}