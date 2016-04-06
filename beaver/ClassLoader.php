<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver;

/**
 * An auto loader with PSR-4.
 *
 * @author You Ming
 */
class ClassLoader
{
    /**
     * An array where the key is a namespace prefix and the value
     * is an array of base directories for classes in that namespace.
     *
     * @var array
     */
    protected $prefixes = [];

    /**
     * Adds a base directory for a namespace prefix.
     *
     * @param string $prefix The namespace prefix.
     * @param string $baseDir A base directory for class files in the namespace.
     * @param bool $prepend If true, the base directory will be added to head of the stack.
     */
    public function addNamespace($prefix, $baseDir, $prepend = false)
    {
        // Normalizes namespace prefix
        $prefix = trim($prefix, '\\') . '\\';

        // Normalizes the base directory with a directory separator
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        // Initializes the namespace prefix array
        if (!isset($this->prefixes[$prefix])) {
            $this->prefixes[$prefix] = [];
        }

        if ($prepend) {
            array_unshift($this->prefixes[$prefix], $baseDir);
        } else {
            array_push($this->prefixes[$prefix], $baseDir);
        }
    }

    /**
     * Register the loader with auto loader stack.
     *
     * @param bool $prepend If true, the auto loader will be
     */
    public function register($prepend = true)
    {
        spl_autoload_register([$this, 'loadClass'], true, $prepend);
    }

    /**
     * Unregisters the loader with auto loader stack.
     */
    public function unregister()
    {
        spl_autoload_unregister([$this, 'loadClass']);
    }

    /**
     * Loads the given class.
     *
     * @param string $className The class name.
     */
    public function loadClass($className)
    {
        $filePath = $this->findClass($className);
        if ($filePath) {
            include $filePath;
        }
    }

    /**
     * Finds the source file path for a given class.
     *
     * @param string $className The class name.
     * @return string|bool
     */
    protected function findClass($className)
    {
        $prefix = $className;
        $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $className);

        while (false !== $pos = strrpos($prefix, '\\')) {
            $prefix = substr($className, 0, $pos + 1);
            $relativePath = substr($classPath, $pos + 1);

            if (isset($this->prefixes[$prefix])) {
                foreach ($this->prefixes[$prefix] as $baseDir) {
                    $filePath = $baseDir . $relativePath . '.php';

                    // If the mapped file exists, return it.
                    if (is_file($filePath)) {
                        return $filePath;
                    }
                }
            }

            $prefix = rtrim($prefix, '\\');
        }

        return false;
    }
}