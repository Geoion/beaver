<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver\Http;

use Beaver\Util\Bundle;
use LogicException;

/**
 * A representation for a HTTP request.
 *
 * @author You Ming
 */
class Request
{
    // Methods.
    const GET     = 'GET';
    const POST    = 'POST';
    const PUT     = 'PUT';
    const PATCH   = 'PATCH';
    const DELETE  = 'DELETE';
    const HEAD    = 'HEAD';
    const OPTIONS = 'OPTIONS';
    const TRACE   = 'TRACE';
    const CONNECT = 'CONNECT';
    const PURGE   = 'PURGE';

    /**
     * Shared instance.
     *
     * @var Request
     */
    protected static $instance = null;

    /**
     * Headers.
     *
     * @var Bundle
     */
    protected $headers;

    /**
     * Request method.
     *
     * @var string
     */
    protected $method;

    /**
     * Query string parameters.
     *
     * @var Bundle
     */
    protected $queries;

    /**
     * Request body parameters.
     *
     * @var Bundle
     */
    protected $forms;

    /**
     * Server and environment parameters.
     *
     * @var ServerBundle
     */
    protected $servers;

    /**
     * Custom parameters.
     *
     * @var Bundle
     */
    protected $attributes;

    /**
     * Cookies.
     *
     * @var Bundle
     */
    protected $cookies;

    /**
     * Body.
     *
     * @var string|resource
     */
    protected $body;

    /**
     * Constructor.
     */
    public function __construct()
    {
        // With the php's bug #66606, the php's built-in web server
        // stores the Content-Type and Content-Length header values in
        // HTTP_CONTENT_TYPE and HTTP_CONTENT_LENGTH fields.
        $servers = $_SERVER;
        if ('cli-server' === PHP_SAPI) {
            if (array_key_exists('HTTP_CONTENT_LENGTH', $_SERVER)) {
                $server['CONTENT_LENGTH'] = $_SERVER['HTTP_CONTENT_LENGTH'];
            }
            if (array_key_exists('HTTP_CONTENT_TYPE', $_SERVER)) {
                $server['CONTENT_TYPE'] = $_SERVER['HTTP_CONTENT_TYPE'];
            }
        }

        $this->queries = new Bundle($_GET);
        $this->forms = new Bundle($_POST);
        $this->attributes = new Bundle();
        $this->servers = new ServerBundle($servers);
        $this->headers = new Bundle($this->servers->getHeaders());
        $this->cookies = new Bundle($_COOKIE);

        $this->body = null;
    }

    /**
     * Sets multiple attribute parameters.
     *
     * @param array $attributes
     * @param bool $replace
     */
    public function setAttributes(array $attributes, $replace = false)
    {
        if ($replace) {
            $this->attributes->replace($attributes);
        } else {
            $this->attributes->add($attributes);
        }
    }

    /**
     * Sets an attribute parameter.
     *
     * @param string $name
     * @param mixed $value
     */
    public function setAttribute($name, $value)
    {
        $this->attributes->set($name, $value);
    }

    /**
     * Gets the attribute parameters for this request.
     *
     * @return Bundle
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Gets the request method.
     *
     * @return string
     */
    public function getMethod()
    {
        if (null === $this->method) {
            $this->method = strtoupper($this->servers->get('REQUEST_METHOD', 'GET'));
        }

        return $this->method;
    }

    /**
     * Gets the request body.
     *
     * @param bool $asResource If true, a resource will be returned
     * @return string|resource
     */
    public function getBody($asResource = false)
    {
        if (PHP_VERSION_ID < 50600 && false === $this->body) {
            throw new LogicException('getBody() can only be called once when using the resource and PHP below 5.6.');
        }

        $isRes = is_resource($this->body);

        if ($asResource) {
            if ($isRes) {
                rewind($this->body);

                return $this->body;
            }

            if (is_string($this->body)) {
                $resource = fopen('php://temp', 'r+');
                fwrite($resource, $this->body);
                rewind($resource);

                return $resource;
            }

            $this->body = false;

            return fopen('php://input', 'rb');
        }

        if ($isRes) {
            rewind($this->body);

            return stream_get_contents($this->body);
        }

        if (null === $this->body) {
            $this->body = file_get_contents('php://input');
        }

        return $this->body;
    }

    /**
     * Creates a request of current http request.
     *
     * @return Request
     */
    public static function create()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}