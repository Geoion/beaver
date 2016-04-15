<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver\Logger;

use Beaver\Logger;
use SeasLog;

/**
 * A logger that adapt to SeasLog extension.
 *
 * @author You Ming
 *
 * [Options]
 *  basePath        : Base directory for logs.
 *  module          : Relative path to directory which logs will be stored.
 *  datetime        : The format of datetime in logs.
 */
class SeasLogger extends Logger
{
    /**
     * Relative path to directory which logs will be stored.
     *
     * @var string
     */
    protected $module;

    /**
     * @inheritdoc
     */
    public function connect(array $options = [])
    {
        $basePath = isset($options['basePath']) ? $options['basePath'] : $this->context->getLogDir();
        $this->module = isset($options['module']) ? $options['module'] : 'default';

        SeasLog::setBasePath($basePath);

        if (isset($options['datetime'])) {
            SeasLog::setDatetimeFormat($options['datetime']);
        }
    }

    /**
     * @inheritdoc
     */
    public function close()
    {
        SeasLog::flushBuffer();
    }

    /**
     * @inheritdoc
     */
    public function log($level, $message, array $content = [])
    {
        SeasLog::log($level, $message, $content, $this->module);
    }
}