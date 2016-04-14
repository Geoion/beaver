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
 * HTTP 405 Method Not Allowed.
 *
 * @author You Ming
 */
class MethodNotAllowedException extends HttpException
{
    /**
     * Constructor.
     *
     * @param string $message
     */
    public function __construct($message = '')
    {
        parent::__construct(405, $message);
    }
}