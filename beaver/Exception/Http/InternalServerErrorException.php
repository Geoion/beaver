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
 * HTTP 500 Internal Server Error.
 *
 * @author You Ming
 */
class InternalServerErrorException extends HttpException
{
    /**
     * Constructor.
     * 
     * @param string $message
     */
    public function __construct($message = '')
    {
        parent::__construct(500, $message);
    }
}