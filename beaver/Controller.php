<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver;

use Beaver\Exception\Http\NotFoundException;
use Beaver\Exception\MissingParameterException;
use Beaver\Traits\ContextInjection;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;

/**
 * Base class for controller.
 *
 * @author You Ming
 */
class Controller
{
    use ContextInjection;

    /**
     * The view to be rendered.
     *
     * @var View
     */
    protected $view;

    /**
     * Template.
     *
     * @var string
     */
    protected $template = null;

    /**
     * The name of running method.
     *
     * @var string
     */
    protected $runningMethod = null;

    /**
     * Assigns template variables to view.
     *
     * @param string|array $name
     * @param mixed $value
     */
    protected function assign($name, $value = null)
    {
        if (is_array($name)) {
            $this->view->setMulti($name);
        } else {
            $this->view->set($name, $value);
        }
    }

    /**
     * Sets the template name for view.
     *
     * @param string $template
     */
    protected function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * Resolves the name of template if not given.
     *
     * @return string
     */
    protected function resolveTemplate()
    {
        $router = $this->context->get(Router::class);
        if ($router) {
            /** @var Router $router */
            $controller = $router->getOriginalController();
            $method = $router->getOriginalMethod();

            $pieces = explode('\\', $controller);
            foreach ($pieces as & $piece) {
                $piece = $this->parseTemplateName($piece);
            }

            return implode('/', $pieces) . '/' . $this->parseTemplateName($method);
        } else {
            throw new RuntimeException('The name of template must be provided.');
        }
    }

    /**
     * Parses the name of template.
     *
     * @param string $name
     * @return string
     */
    protected function parseTemplateName($name)
    {
        return strtolower(trim(preg_replace('/[A-Z]/', '_\\0', $name), '_'));
    }

    /**
     * Renders template.
     *
     * @param string $template
     * @param array $options
     */
    protected function render($template = null, array $options = [])
    {
        if (null === $template) {
            $template = $this->template ? $this->template : $this->resolveTemplate();
        }

        $this->onRender($template, $options);

        $this->view->render($template, $options);
    }

    /**
     * Initializes this controller.
     *
     * @param string $method
     * @throws NotFoundException
     */
    public function initialize($method)
    {
        // Creates view instance.
        $viewClass = $this->getRegistry()->get('view.class', View::class);
        $this->view = $this->context->get($viewClass);

        $this->onCreate($method);
        
        // Checks method.
        if (null === $method) {
            $class = get_class($this);
            throw new NotFoundException("Dispatched result without method in $class.");
        }

        if (!method_exists($this, $method)) {
            $class = get_class($this);
            throw new NotFoundException("Method $method not found in $class.");
        }
    }

    /**
     * Running.
     *
     * @param string $method
     * @throws NotFoundException
     * @throws MissingParameterException
     */
    public function run($method)
    {
        $this->runningMethod = $method;

        $this->onStart($method);

        $m = new ReflectionMethod($this, $method);

        try {
            if ($m->isPublic() && !$m->isStatic()) {
                if ($m->getNumberOfParameters() > 0
                        && $this->getRegistry()->get('router.parameter.inject.enable', false)) {
                    // Injects attributes.
                    $attributes = $this->context->getRequest()->getAttributes()->toArray();
                    $parameters = $m->getParameters();
                    $injectName = $this->getRegistry()->get('router.parameter.inject.way', 'name') != 'order';

                    $arguments = [];
                    foreach ($parameters as $parameter) {
                        $name = $parameter->getName();
                        if (!$injectName && !empty($attributes)) {
                            $arguments[] = array_shift($attributes);
                        } elseif ($injectName && isset($attributes[$name])) {
                            $arguments[] = $attributes[$name];
                        } elseif ($parameter->isDefaultValueAvailable()) {
                            $arguments[] = $parameter->getDefaultValue();
                        } else {
                            throw new MissingParameterException($name);
                        }
                    }

                    $m->invokeArgs($this, $arguments);
                } else {
                    $m->invoke($this);
                }
            } else {
                $class = get_class($this);
                throw new ReflectionException("Method $method is not available in $class.");
            }
        } catch (ReflectionException $e) {
            throw new NotFoundException($e->getMessage());
        }

        $this->onStop();

        $this->runningMethod = null;
    }

    /**
     * Called when the controller is creating.
     *
     * @param string $method
     */
    protected function onCreate($method)
    {
    }

    /**
     * Called when the controller is starting.
     *
     * @param string $method
     */
    protected function onStart($method)
    {
    }

    /**
     * Called when start to render.
     *
     * @param string $template
     * @param array $options
     */
    protected function onRender($template, &$options)
    {
    }

    /**
     * Called when the controller is stopping.
     */
    protected function onStop()
    {
    }
}