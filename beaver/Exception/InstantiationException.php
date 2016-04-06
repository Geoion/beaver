<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver\Exception;

use Exception;

/**
 * Thrown when the application tries to create an instance of a class,
 * but the specified class object cannot be instantiated.
 *
 * @author You Ming
 */
class InstantiationException extends Exception
{
}