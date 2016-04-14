<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver;

use ArrayAccess;
use Beaver\Traits\ContextInjection;

/**
 * Base class for sessions operator.
 *
 * @author You Ming
 *
 * [Options]
 *  name            : The name of the session.
 *  path            : Session data path.
 *  expiry          : Lifetime of the session cookie.
 *  useTransSid     : Whether transparent sid support is enabled or not.
 *  useCookies      : Whether the module will use cookies to store the session id on the client side.
 *  cache.limiter   : The cache control method used for session pages.
 *  cache.expiry    : The lifetime for cached session pages in minutes.
 *  cookie.lifetime : The lifetime of the cookie.
 *  cookie.path     : Specifies path to set in the session cookie.
 *  cookie.domain   : Specifies the domain to set in the session cookie.
 *  cookie.secure   : Whether cookies should only be sent over secure connections.
 *  cookie.httpOnly : Marks the cookie as accessible only through the HTTP protocol.
 */
class Session implements ArrayAccess
{
    use ContextInjection;

    /**
     * Sets the current session id.
     *
     * @param string $id
     */
    public function setId($id) {
        session_id($id);
    }

    /**
     * Gets the current session id.
     *
     * @return string
     */
    public function getId()
    {
        return session_id();
    }

    /**
     * 启动 SESSION
     * @return bool
     */
    public function start()
    {
        return session_start();
    }

    /**
     * Reinitialize session array with original values.
     */
    public function reset()
    {
        session_reset();
    }

    /**
     * Write session data and end session.
     */
    public function commit()
    {
        session_write_close();
    }

    /**
     * Clears all session variables
     *
     * @return bool
     */
    public function clear()
    {
        session_unset();
        return session_destroy();
    }

    /**
     * Updates the current session id with a newly generated one.
     *
     * @param bool $deleteOld Whether to delete the old associated session file or not.
     * @return bool
     */
    public function regenerate($deleteOld = false)
    {
        return session_regenerate_id($deleteOld);
    }

    /**
     * Connects to the session driver.
     *
     * @param array $options
     */
    public function connect($options = [])
    {
        if (isset($options['name'])) {
            session_name($options['name']);
        }
        if (isset($options['path'])) {
            session_save_path($options['path']);
        }
        if (isset($options['expiry'])) {
            ini_set('session.gc_maxlifetime',   $options['expiry']);
            ini_set('session.cookie_lifetime',  $options['expiry']);
        }
        if (isset($options['useTransSid'])) {
            ini_set('session.use_trans_sid', $options['useTransSid'] ? 1 : 0);
        }
        if (isset($options['useCookies'])) {
            ini_set('session.use_cookies', $options['useCookies'] ? 1 : 0);
        }
        if (isset($options['cache']['limiter'])) {
            session_cache_limiter($options['cache']['limiter']);
        }
        if (isset($options['cache']['expiry'])) {
            session_cache_expire($options['cache']['expiry']);
        }
        if (isset($options['cookie'])) {
            $lifetime = isset($options['cookie']['lifetime']) ? $options['cookie']['lifetime'] : 0;
            $path = isset($options['cookie']['path']) ? $options['cookie']['path'] : '';
            $domain = isset($options['cookie']['domain']) ? $options['cookie']['domain'] : '';
            $secure = isset($options['cookie']['secure']) ? $options['cookie']['secure'] : false;
            $httpOnly = isset($options['cookie']['httpOnly']) ? $options['cookie']['httpOnly'] : false;

            session_set_cookie_params($lifetime, $path, $domain, $secure, $httpOnly);
        }
    }

    /**
     * Sets a session.
     *
     * @param string $name
     * @param mixed $value
     */
    public function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    /**
     * Gets a session.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return isset($_SESSION[$name]) ? $_SESSION[$name] : $default;
    }

    /**
     * Checks whether a session is existed.
     *
     * @param string $name
     * @return bool
     */
    public static function exist($name)
    {
        return isset($_SESSION[$name]);
    }

    /**
     * Deletes a session.
     *
     * @param string $name
     */
    public static function delete($name)
    {
        unset($_SESSION[$name]);
    }

    /**
     * Gets a session.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Sets a session.
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * Checks whether a session is existed.
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->exist($name);
    }

    /**
     * Deletes a session.
     *
     * @param string $name
     * @return bool
     */
    public function __unset($name)
    {
        $this->delete($name);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return $this->exist($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        $this->delete($offset);
    }
}
