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
use Throwable;

/**
 * Base class for handling uncaught exception.
 *
 * @author You Ming
 */
abstract class Catcher
{
    use ContextInjection;

    /**
     * Handles an exception.
     *
     * @param Throwable $throwable
     */
    abstract public function handleException(Throwable $throwable);
}