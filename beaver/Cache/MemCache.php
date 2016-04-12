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
use Memcached;

/**
 * A cache that caching data with a Memcached server. Needs memcached extension.
 *
 * @author You Ming
 *
 * [Options]
 *  server      : The info for Memcached server.
 *      host        : The host name.
 *      port        : The port number.
 *  serializer  : Type of serializer.
 *  prefix      : Prefix for all key.
 *  expiry      : Default expiry time.
 *  compressor  : Type of compressor.
 */
class MemCache extends Cache
{
    /**
     * A handler for Memcached.
     *
     * @var Memcached
     */
    protected $memcached;

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
                return $server;
            } elseif (false !== strpos($server, ':')) {
                $result = explode(':', $server);
                return [$result[0], (int) $result[1]];
            } else {
                return [$server, 11211];
            }
        } else {
            return ['127.0.0.1', 11211];
        }
    }

    /**
     * @inheritdoc
     */
    public function connect(array $options = [])
    {
        $server = $this->parseServer($options);
        
        $this->memcached = new Memcached();
        if (!$this->memcached->addServer($server[0], $server[1])) {
            return false;
        }

        if (isset($options['serializer'])) {
            $this->memcached->setOption(Memcached::OPT_SERIALIZER, $options['serializer']);
        }

        if (isset($options['prefix'])) {
            $this->memcached->setOption(Memcached::OPT_PREFIX_KEY, $options['prefix']);
        }

        if (isset($options['compressor'])) {
            $this->memcached->setOption(Memcached::OPT_COMPRESSION, true);
            $this->memcached->setOption(Memcached::OPT_COMPRESSION_TYPE, $options['compressor']);
        }

        $this->expiry = isset($options['expiry']) ? $options['expiry'] : 0;

        return true;
    }

    /**
     * @inheritdoc
     */
    public function close()
    {
    }

    /**
     * @inheritdoc
     */
    public function get($name, $default = null)
    {
        $value = $this->memcached->get($name);

        if (false !== $value || $this->memcached->getResultCode() != Memcached::RES_NOTFOUND) {
            return $value;
        }

        return $default;
    }

    /**
     * @inheritdoc
     */
    public function set($name, $value, $expiry = null)
    {
        if (null === $expiry) {
            $expiry = $this->expiry;
        }

        return $this->memcached->set($name, $value, $expiry);
    }

    /**
     * @inheritdoc
     */
    public function exist($name)
    {
        return false !== $this->memcached->get($name) || $this->memcached->getResultCode() != Memcached::RES_NOTFOUND;
    }

    /**
     * @inheritdoc
     */
    public function delete($name)
    {
        return $this->memcached->delete($name);
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        return $this->memcached->flush();
    }
}