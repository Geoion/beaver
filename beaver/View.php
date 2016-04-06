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
use RuntimeException;

/**
 * The view.
 *
 * @author You Ming
 */
class View implements ArrayAccess
{
    use ContextInjection;

    /**
     * The variables for render.
     *
     * @var array
     */
    protected $variables = [];

    /**
     * The theme of template.
     *
     * @var string
     */
    protected $theme = null;

    /**
     * The render.
     *
     * @var Render
     */
    protected $render = null;

    /**
     * Cache strategy for this page.
     *
     * @var string
     */
    protected $cacheControl = null;

    /**
     * Sets a template variables in this view.
     * 
     * @param string $name
     * @param mixed $value
     */
    public function set($name, $value)
    {
        $this->variables[$name] = $value;
    }

    /**
     * Sets multi variables in this view.
     * 
     * @param array $variables
     */
    public function setMulti(array $variables)
    {
        $this->variables = array_merge($this->variables, $variables);
    }

    /**
     * Gets a template variables for a given name in this view.
     *
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        return isset($this->variables[$name]) ? $this->variables[$name] : null;
    }

    /**
     * Gets all template variables in this view.
     *
     * @return array
     */
    public function getAll()
    {
        return $this->variables;
    }

    /**
     * Checks whether a template variable is existing in this view.
     *
     * @param string $name
     * @return bool
     */
    public function exist($name)
    {
        return isset($this->variables[$name]);
    }

    /**
     * Removes a template variable in this view.
     *
     * @param string $name
     */
    public function delete($name)
    {
        unset($this->variables[$name]);
    }

    /**
     * Removes all template variables in this view.
     */
    public function deleteAll()
    {
        $this->variables = [];
    }

    /**
     * Sets cache strategy for this page.
     *
     * @param string $cacheControl
     */
    public function setCacheControl($cacheControl)
    {
        $this->cacheControl = $cacheControl;
    }

    /**
     * Sets a theme for template.
     *
     * @param string $theme
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    /**
     * Sets a render for this view.
     *
     * @param string|Render $render
     */
    public function setRender($render)
    {
        $this->render = $render;
    }

    /**
     * Gets the render for this view.
     */
    protected function getRender()
    {
        if (is_object($this->render)) {
            return $this->render;
        }

        if (null === $this->render) {
            $this->render = $this->getRegistry()->get('view.render.class');
        }

        if (is_string($this->render)) {
            $this->render = $this->context->get($this->render);
            // Prepares render.
            $this->render->prepare($this->getRegistry()->get('view.render', []));

            return $this->render;
        } else {
            throw new RuntimeException('A render must be provided before render.');
        }
    }

    /**
     * Renders the template and output to response.
     *
     * @param string $template
     * @param array $options
     */
    public function render($template, array $options = [])
    {
        $content = $this->fetch($template, $options);

        $this->onOutput($content);

        $charset = isset($options['charset']) ? $options['charset'] : null;
        $contentType = isset($options['contentType']) ? $options['contentType'] : null;
        $this->display($content, $charset, $contentType);
    }

    /**
     * Fetches a template render result.
     *
     * @param string $template
     * @param array $options
     * @return string
     */
    public function fetch($template, array $options = [])
    {
        $variables = $this->variables;
        if (isset($options['extraVars'])) {
            $variables = array_merge($variables, $options['extraVars']);
            unset($options['extraVars']);
        }

        ob_start();
        ob_implicit_flush(false);

        $this->getRender()->render($template, $this->theme, $variables, $options);

        $content = ob_get_clean();

        $this->onFetch($content);

        return $content;
    }

    /**
     * Outputs the response content.
     *
     * @param string $content
     * @param string $charset
     * @param string $contentType
     */
    protected function display($content, $charset = null, $contentType = null)
    {
        if (null === $charset) {
            $charset = $this->getRegistry()->get('view.response.charset', 'utf-8');
        }

        if (null === $contentType) {
            $contentType = $this->getRegistry()->get('view.response.contentType', 'text/html');
        }

        $cacheControl = $this->cacheControl ? $this->cacheControl
            : $this->getRegistry()->get('view.response.cacheControl', 'private');

        header('Content-Type: ' . $contentType . '; charset=' . $charset);
        header('Cache-Control: ' . $cacheControl);

        echo $content;
    }

    /**
     * Called when fetch a template's content.
     *
     * @param $content
     */
    protected function onFetch(&$content)
    {
    }

    /**
     * Called just before output template to response.
     *
     * @param string $content
     */
    protected function onOutput(&$content)
    {
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @inheritdoc
     */
    public function __isset($name)
    {
        return $this->exist($name);
    }

    /**
     * @inheritdoc
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
        $this->exist($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        $this->get($offset);
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
        $this->offsetUnset($offset);
    }
}