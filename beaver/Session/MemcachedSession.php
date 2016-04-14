<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver\Session;

use Beaver\Cache\MemCache;
use Beaver\Session;
use SessionHandlerInterface;

/**
 * A session operator which handle sessions with MemCache.
 *
 * @author You Ming
 *
 * [Options]
 *  prefix          : The prefix for name.
 *  driverOptions   : The driver options.
 */
class MemcachedSession extends Session implements SessionHandlerInterface
{
    /**
     * The handler for memcached.
     * 
     * @var MemCache
     */
    protected $handler;
    
    /**
     * The prefix of stored name.
     *
     * @var string
     */
    protected $scope = '';

    public function connect($options = [])
    {
        parent::connect($options);
        
        $driverOptions = isset($options['driverOptions']) ? $options['driverOptions'] : [];
        if (isset($options['prefix'])) {
            $driverOptions['prefix'] = $options['prefix'];
        }
        if (isset($options['expiry'])) {
            $driverOptions['expiry'] = $options['expiry'];
        }

        $this->handler = $this->context->get(MemCache::class);
        $this->handler->connect($driverOptions);
        
        session_set_save_handler($this);
    }

    /**
     * @inheritdoc
     */
    public function close()
    {
        $this->handler->close();
    }

    /**
     * @inheritdoc
     */
    public function destroy($sessionId)
    {
        return $this->handler->delete($this->scope . $sessionId);
    }

    /**
     * @inheritdoc
     */
    public function gc($maxLifeTime)
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function open($savePath, $name)
    {
        $this->scope = $savePath . $name;
        return true;
    }

    /**
     * @inheritdoc
     */
    public function read($sessionId)
    {
        return $this->handler->get($this->scope . $sessionId);
    }

    /**
     * @inheritdoc
     */
    public function write($sessionId, $sessionData)
    {
        return $this->handler->set($this->scope . $sessionId, $sessionData);
    }
}

