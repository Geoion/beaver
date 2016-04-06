<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver;

use Beaver\Http\Request;
use Beaver\Http\Response;
use Throwable;

/**
 * The heart of Beaver framework, .
 *
 * @author You Ming
 */
class Beaver
{
    /**
     * The version code of Beaver.
     */
    const VERSION = '0.1.0';

    /**
     * The integer version.
     */
    const VERSION_INT = 100;

    /**
     * Debug mode.
     *
     * @var bool
     */
    protected $debug = false;

    /**
     * The registry.
     *
     * @var Registry
     */
    protected $registry;

    /**
     * The global exception catcher.
     *
     * @var Catcher
     */
    protected $catcher;

    /**
     * The runtime environment.
     *
     * @var Context
     */
    protected $context;

    /**
     * Constructor.
     *
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Whether in debug mode.
     *
     * @return bool
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * Sets the global exception catcher.
     *
     * @param Catcher $catcher
     */
    public function setCatcher(Catcher $catcher)
    {
        $this->catcher = $catcher;
    }

    /**
     * Initializes the framework.
     */
    protected function initialize()
    {
        error_reporting(0);

        header('X-Powered-By: Beaver ' . self::VERSION);

        define('DS', DIRECTORY_SEPARATOR);

        register_shutdown_function([$this, 'onShutdown']);
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);

        $this->debug = $this->registry->get('app.debug', false);

        ini_set('session.auto_start', 0);

        // Creates global context.
        $this->context = new Context($this, $this->registry);
        $this->context->shareInstance([Context::class => 'context'], $this->context);
        $this->context->shareInstance([Registry::class => 'registry'], $this->registry);

        // Creates request and response.
        $this->context->shareInstance([Request::class => 'request'], Request::create());
        $this->context->shareInstance([Response::class => 'response'], Response::create());
    }

    /**
     * Prepares an app instance.
     *
     * @return App
     */
    protected function prepareApp()
    {
        $appClass = $this->registry->get('app.class', App::class);
        /** @var App $app */
        $app = $this->context->get($appClass);
        $this->context->shareInstance($appClass, $app);
        $this->context->shareInstance([App::class => 'app'], $app);

        // Initializes app
        $app->initialize();

        return $app;
    }

    /**
     * Runs an app.
     *
     * @param App $app
     */
    protected function runApp(App $app)
    {
        $app->run();
    }

    /**
     * Running.
     */
    public function run()
    {
        $this->initialize();
        
        $app = $this->prepareApp();

        $this->runApp($app);
    }

    /**
     * Handles uncaught exception.
     *
     * @param Throwable $exception
     */
    public function handleException(Throwable $exception)
    {
        if ($this->catcher) {
            $this->catcher->handleException($exception);
        } else {
            $this->halt($exception->getMessage(), $exception->getTraceAsString(), $exception->getFile(),
                $exception->getLine());
        }
    }

    /**
     * Handles an error.
     *
     * @param int $code The error code.
     * @param string $message
     * @param string $file
     * @param int $line
     */
    public function handleError($code, $message, $file, $line)
    {
        $this->halt("$code: $message", null, $file, $line);
    }

    /**
     * Called when the request handling is over.
     */
    public function onShutdown()
    {
        if ($e = error_get_last()) {
            switch ($e['type']) {
                case E_ERROR:
                case E_PARSE:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                case E_USER_ERROR:
                    $this->halt($e['message'], null, $e['file'], $e['line']);
                    break;
            }
        }
    }

    /**
     * Halt.
     *
     * @param string $message
     * @param string $trace
     * @param string $file
     * @param int $line
     */
    protected function halt($message, $trace, $file, $line)
    {
        ob_end_clean();

        // Sends the error head.
        header('HTTP/1.1 500 Internal Server Error');
        header('Status:500 Internal Server Error');

        if ($this->debug) {
            if (empty($trace)) {
                ob_start();
                debug_print_backtrace();
                $trace = ob_get_clean();
            }

            // Outputs error info in debug mode.
            $message = "{$_SERVER['HTTP_HOST']}:{$_SERVER['SERVER_PORT']} {$_SERVER['REQUEST_URI']}\n\n"
                . "{$message}\n{$file} Line {$line}\n\n{$trace}";

            echo nl2br(htmlspecialchars($message));
        } else {
            echo 'How bigger is Beaver!';
        }

        exit;
    }
}