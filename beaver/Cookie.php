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
 * A operator for cookies.
 *
 * @author You Ming
 */
class Cookie
{
    /**
     * The time the cookie expires.
     *
     * @var int
     */
    protected $expiry;

    /**
     * The domain that the cookies is available to.
     *
     * @var string
     */
    protected $domain;

    /**
     * The path on the server in which the cookie will be available on.
     *
     * @var string
     */
    protected $path;

    /**
     * Indicates that the cookies should only be transmitted over a secure HTTPS connection
     * from the client.
     *
     * @var bool
     */
    protected $secure;

    /**
     * Whether the cookies can be accessible only through the HTTP protocol.
     *
     * @var bool
     */
    protected $httpOnly;

    /**
     * Constructor.
     * 
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->expiry = isset($options['expiry']) ? $options['expiry'] : 0;
        $this->domain = isset($options['domain']) ? $options['domain'] : '';
        $this->path = isset($options['path']) ? $options['path'] : '';
        $this->secure = isset($options['secure']) ? $options['secure'] : false;
        $this->httpOnly = isset($options['httpOnly']) ? $options['httpOnly'] : false;
    }

    /**
     * Gets a value that defined in cookies with given name.
     *
     * @param string $name The name of the cookie.
     * @param mixed $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
    }

    /**
     * Sets a cookie.
     *
     * @param string $name The name of the cookie.
     * @param string $value The value of the cookie.
     * @param int $expiry The time the cookie expires.
     * @param string $path The path on the server in which the cookie will be available on.
     * @param string $domain The domain that the cookie is available to.
     * @param bool $secure Indicates that the cookies should only be transmitted over a secure
     *      HTTPS connection from the client.
     * @param bool $httpOnly Whether the cookies can be accessible only through the HTTP protocol.
     * @return bool
     */
    public function set($name, $value = '', $expiry = null, $path = null, $domain = null, $secure = null,
        $httpOnly = null)
    {
        if (null === $value || '' === $value) {
            return $this->delete($name);
        }

        if (null === $expiry) {
            $this->expiry;
        }
        if (null === $path) {
            $this->path;
        }
        if (null === $domain) {
            $this->domain;
        }
        if (null === $secure) {
            $this->secure;
        }
        if (null === $httpOnly) {
            $this->httpOnly;
        }

        if (0 !== $expiry) {
            $expiry = time() + $expiry;
        }

        return setcookie($name, $value, $expiry, $path, $domain, $secure, $httpOnly);
    }

    /**
     * Determines whether a cookie has been set.
     *
     * @param string $name
     * @return bool
     */
    public function exist($name)
    {
        return isset($_COOKIE[$name]);
    }

    /**
     * Deletes a cookie.
     *
     * @param string $name
     * @return bool
     */
    public function delete($name)
    {
        return setcookie($name);
    }

    /**
     * Clears all cookies.
     */
    public function clear()
    {
        foreach ($_COOKIE as $name => $value)
        {
            $this->delete($name);
        }
    }
}