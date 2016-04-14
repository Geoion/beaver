<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver\Exception\Http;

/**
 * HTTP 503 Service Unavailable.
 *
 * @author You Ming
 */
class ServiceUnavailableException extends HttpException
{
    /**
     * Constructor.
     * 
     * @param string $message
     */
    public function __construct($message = '')
    {
        parent::__construct(503, $message);
    }
}