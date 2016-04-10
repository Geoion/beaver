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

/**
 * Class to global information about runtime environment, also as a subclass
 * of the dependency injection container.
 *
 * @author You Ming
 */
class Context extends Container
{
    /**
     * The running kernel.
     *
     * @var Beaver
     */
    protected $beaver;

    /**
     * The registry.
     *
     * @var Registry
     */
    protected $registry;

    /**
     * An array contains registered services.
     *
     * @var array
     */
    protected $services = [];

    /**
     * Directories in context.
     *
     * @var array
     */
    protected $directories = [];

    /**
     * Constructor.
     *
     * @param Beaver $beaver
     * @param Registry $registry
     */
    public function __construct(Beaver $beaver, Registry $registry)
    {
        $this->beaver = $beaver;
        $this->registry = $registry;
    }

    /**
     * Whether in debug mode.
     *
     * @return bool
     */
    public function isDebug()
    {
        return $this->beaver->isDebug();
    }

    /**
     * Gets the registry of current application.
     *
     * @return Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * Gets the namespace of current application.
     *
     * @return string
     */
    public function getPackageName()
    {
        return $this->registry->get('app.package');
    }

    /**
     * Gets the base directory of current application.
     *
     * @return string
     */
    public function getBaseDir()
    {
        if (!isset($this->directories['base'])) {
            $baseDir = $this->registry->get('app.root');
            if (null === $baseDir) {
                $baseDir = __DIR__ . '/../';
            }

            $this->directories['base'] = realpath($baseDir) . DS;
        }

        return $this->directories['base'];
    }

    /**
     * Gets directory for resources.
     *
     * @return string
     */
    public function getPublicDir()
    {
        return $this->getDirectory('public');
    }

    /**
     * Gets directory for caching.
     *
     * @return string
     */
    public function getCacheDir()
    {
        return $this->getDirectory('cache');
    }

    /**
     * Gets directory for templates.
     *
     * @return string
     */
    public function getTemplateDir()
    {
        return $this->getDirectory('template');
    }

    /**
     * Gets directory for file storage.
     *
     * @return string
     */
    public function getStorageDir()
    {
        return $this->getDirectory('storage');
    }

    /**
     * Gets app directory.
     *
     * @param string $name
     * @return string
     */
    private function getDirectory($name)
    {
        if (!isset($this->directories[$name])) {
            $directory = $this->registry->get('app.directory.' . $name, $this->getBaseDir() . $name);

            $this->directories[$name] = realpath($directory) . DS;
        }

        return $this->directories[$name];
    }

    /**
     * Gets the request for current request.
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->get(Request::class);
    }

    /**
     * Gets the response for current request.
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->get(Response::class);
    }

    /**
     * Registers a service in this context.
     *
     * @param string|Service $service A service instance or a class name of service.
     */
    public function registerService($service)
    {
        if (is_string($service)) {
            $name = $service;
            $service = $this->get($service);
        } else {
            $name = get_class($service);
        }

        $service->bindContext($this);
        $service->register();

        if (!$service->isDeferred()) {
            $service->start();
        }

        $this->services[$name] = $service;
    }

    /**
     * Unregisters all services in this context.
     */
    public function unregisterServices()
    {
        foreach ($this->services as $service) {
            if ($service->isStarted()) {
                $service->stop();
            }
        }

        $this->services = [];
    }

    /**
     * Gets a service.
     *
     * @param string $service
     * @return Service
     */
    public function getService($service)
    {
        if (isset($this->services[$service])) {
            $service = $this->services[$service];

            if (!$service->isStarted()) {
                $service->start();
            }

            return $service;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    protected function onBuilt($instance)
    {
        if (method_exists($instance, 'bindContext')) {
            $this->inject([$instance, 'bindContext'], ['context' => $this]);
        }
    }
}