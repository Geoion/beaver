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
    const VERSION = '0.1.2';

    /**
     * The integer version.
     */
    const VERSION_INT = 102;

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
            $output = '';
            do {
                $class = get_class($exception);
                $message = $exception->getMessage();
                $file = $exception->getFile();
                $line = $exception->getLine();
                $traces = $exception->getTraceAsString();
                $output .= "$class : $message\n$file Line $line\n\n$traces\n\n\n";
            } while (null !== $exception = $exception->getPrevious());

            $this->halt($output);
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
        ob_start();
        debug_print_backtrace();
        $trace = ob_get_clean();

        $output = "Error $code : $message\n$file Line $line\n\n$trace";

        $this->halt($output);
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
                    ob_start();
                    debug_print_backtrace();
                    $trace = ob_get_clean();

                    $output = "Error {$e['type']} : {$e['message']}\n{$e['file']} Line {$e['line']}\n\n$trace";
                    $this->halt($output);
                    break;
            }
        }
    }

    /**
     * Halt.
     *
     * @param string $output
     */
    protected function halt($output)
    {
        ob_end_clean();

        // Sends the error head.
        header('HTTP/1.1 500 Internal Server Error');
        header('Status:500 Internal Server Error');

        if ($this->debug) {
            // Outputs error info in debug mode.
            $output = "{$_SERVER['HTTP_HOST']}:{$_SERVER['SERVER_PORT']} {$_SERVER['REQUEST_URI']}\n\n$output";

            echo nl2br(htmlspecialchars($output));
        } else {
            echo 'How bigger is Beaver!';
        }

        exit;
    }
}