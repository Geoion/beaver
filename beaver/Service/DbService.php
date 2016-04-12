<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver\Service;

use Beaver\Db;
use Beaver\Exception\DbException;
use Beaver\Service;

/**
 * A service which provides database operations.
 *
 * @author You Ming
 */
class DbService extends Service
{
    /**
     * Configs.
     *
     * @var array
     */
    protected $configs;

    /**
     * A handler of database.
     *
     * @var Db
     */
    protected $handler;

    /**
     * @inheritdoc
     */
    protected $defer = true;

    /**
     * Gets the handler for databse.
     *
     * @return Db
     */
    public function getDb()
    {
        return $this->handler;
    }

    /**
     * @inheritdoc
     */
    protected function onRegister()
    {
        $configs = [
            'class' => $this->getRegistry()->get('db.class'),
            'options' => $this->getRegistry()->get('db')
        ];

        if (empty($configs['class'])) {
            throw new DbException('A class of database must be defined in the registry.');
        }

        $this->configs = $configs;

        $this->provide('db');
    }

    /**
     * @inheritdoc
     */
    protected function onStart()
    {
        $this->handler = $this->context->get($this->configs['class']);
        $this->handler->connect($this->configs['options']);
    }

    /**
     * @inheritdoc
     */
    protected function onStop()
    {
        $this->handler->close();
    }

    /**
     * @inheritdoc
     */
    protected function onProvide($name)
    {
        return $this->getDb();
    }
}