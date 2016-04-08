<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver\Cache;

use Beaver\Cache;
use Redis;

/**
 * A cache that caching data with a Redis server. Needs phpredis extension.
 *
 * @author You Ming
 *
 * [Options]
 *  server      : The info for Redis server.
 *      host        : The host name.
 *      port        : The port number.
 *  serializer  : Type of serializer.
 *  prefix      : Prefix for all key.
 *  expiry      : Default expiry time.
 */
class RedisCache extends Cache
{
    /**
     * A handler for Redis.
     *
     * @var Redis
     */
    protected $redis;

    /**
     * A default expiry time.
     *
     * @var int
     */
    protected $expiry = 0;

    /**
     * Parses a server info.
     *
     * @param array $options
     * @return array
     */
    protected function parseServer($options)
    {
        if (isset($options['server'])) {
            $server = $options['server'];

            if (is_array($server)) {
                return [$server['host'], $server['port']];
            } elseif (false !== strpos($server, ':')) {
                $result = explode(':', $server);
                return [$result[0], (int) $result[1]];
            } else {
                return [$server, 6379];
            }
        } else {
            return ['127.0.0.1', 6379];
        }
    }

    /**
     * @inheritdoc
     */
    public function connect(array $options = [])
    {
        $servers = $this->parseServer($options);

        $this->redis = new Redis();
        if (!$this->redis->pconnect($servers[0], $servers[1], 0, 'cache')) {
            return false;
        }

        if (isset($options['server']['password'])) {
            $this->redis->auth($options['server']['password']);
        }

        if (isset($options['server']['db'])) {
            $this->redis->select($options['server']['db']);
        }

        if (isset($options['serializer'])) {
            $this->redis->setOption(Redis::OPT_SERIALIZER, $options['serializer']);
        }

        if (isset($options['prefix'])) {
            $this->redis->setOption(Redis::OPT_SERIALIZER, $options['prefix']);
        }

        $this->expiry = isset($options['expiry']) ? $options['expiry'] : 0;

        return true;
    }

    /**
     * @inheritdoc
     */
    public function close()
    {
        $this->redis->close();
    }

    /**
     * @inheritdoc
     */
    public function get($name, $default = null)
    {
        $value = $this->redis->get($name);

        if (false === $value && !$this->exist($name)) {
            return $default;
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function set($name, $value, $expiry = null)
    {
        if (null === $expiry) {
            $expiry = $this->expiry;
        }

        if ($expiry > 0) {
            $this->redis->setex($name, $expiry, $value);
        } else {
            $this->redis->set($name, $value);
        }
    }

    /**
     * @inheritdoc
     */
    public function exist($name)
    {
        return $this->redis->exists($name);
    }

    /**
     * @inheritdoc
     */
    public function delete($name)
    {
        return $this->redis->delete($name) > 0;
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        return $this->redis->flushDB();
    }

    /**
     * Appends specified string to an item.
     *
     * @param string $name The cache name.
     * @param string $value The value to be appended.
     */
    public function append($name, $value)
    {
        $this->redis->append($name, $value);
    }
}
