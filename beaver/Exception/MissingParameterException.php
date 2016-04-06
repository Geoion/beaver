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
 * Throws when calling method but a required parameter is missing.
 *
 * @author You Ming
 */
class MissingParameterException extends Exception
{
    /**
     * The name of missing parameter.
     *
     * @var string
     */
    protected $parameter;

    /**
     * Constructor.
     *
     * @param string $parameter
     */
    public function __construct($parameter) {
        parent::__construct("Missing required parameter [$parameter]");
        $this->parameter = $parameter;
    }

    /**
     * Gets the name of missing parameter.
     *
     * @return string
     */
    public function getParameter() {
        return $this->parameter;
    }
}