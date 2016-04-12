<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver;

use Beaver\Traits\ContextInjection;
use Beaver\Util\Arrays;

/**
 * The bridge to template engine.
 *
 * @author You Ming
 */
abstract class Render
{
    use ContextInjection;

    /**
     * Options for render.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Default charset for this render.
     *
     * @var string
     */
    protected $charset = 'utf-8';

    /**
     * Default Content-Type for this render.
     *
     * @var string
     */
    protected $contentType = 'text/html';

    /**
     * The directory path which storage templates.
     *
     * @var string
     */
    private $templateDirectory = null;

    /**
     * Gets the path for template directory.
     */
    protected function getTemplateDirectory()
    {
        if (null === $this->templateDirectory) {
            $directory = Arrays::dotGet($this->options, 'template.directory');

            if (null === $directory) {
                $directory = $this->context->getTemplateDir();
            }

            $this->templateDirectory = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }

        return $this->templateDirectory;
    }

    /**
     * Gets the template file's ext name.
     *
     * @return string
     */
    protected function getTemplateExt()
    {
        return Arrays::dotGet($this->options, 'template.extension', 'tpl');
    }

    /**
     * Gets the default theme for this render.
     *
     * @return string
     */
    protected function getDefaultTheme()
    {
        return Arrays::dotGet($this->options, 'template.theme', 'default');
    }

    /**
     * Gets the default charset for this render.
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Gets the default Content-Type for this render.
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Prepares render.
     *
     * @param array $options
     */
    public function prepare(array $options = [])
    {
        $this->options = $options;

        $this->onPrepare($options);
    }

    /**
     * Renders template.
     *
     * @param string $template
     * @param string $theme
     * @param array $variables
     * @param array $options
     */
    public function render($template, $theme, array $variables, array $options = [])
    {
        $options = array_merge($this->options, $options);

        if (null === $theme) {
            $theme = $this->getDefaultTheme();
        }

        $this->onRender($template, $theme, $variables, $options);
    }

    /**
     * Called when the render is preparing.
     *
     * @param array $options
     */
    protected function onPrepare(array $options)
    {
    }

    /**
     * Called when rendering.
     *
     * @param string $template
     * @param string $theme
     * @param array $variables
     * @param array $options
     */
    protected function onRender($template, $theme, array $variables, array $options = [])
    {
    }
}

