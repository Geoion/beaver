<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver\Http;

/**
 * A representation for a HTTP response.
 *
 * @author You Ming
 */
class Response
{
    /**
     * Shared instance.
     *
     * @var Request
     */
    protected static $instance = null;

    /**
     * Creates a response of current http request.
     *
     * @return Response
     */
    public static function create()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
