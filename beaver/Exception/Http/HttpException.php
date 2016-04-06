<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver\Exception\Http;

use Exception;

/**
 * An exception for HTTP error.
 *
 * @author You Ming
 */
class HttpException extends Exception
{
    /**
     * The status code of http response.
     * 
     * @var int
     */
    protected $status;

    /**
     * Constructor.
     * 
     * @param string $status
     * @param int $message
     */
    public function __construct($status, $message)
    {
        parent::__construct($message, $status);

        $this->status = $status;
    }

    /**
     * Gets the response status.
     * 
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }
}