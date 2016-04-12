<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver\Render;

use Beaver\Render;
use RuntimeException;

/**
 * A simple render use PHP native syntax.
 *
 * @author You Ming
 *
 * [Options]
 *  template        : Template options.
 *      directory       : The directory for templates.
 *      extension       : The extension of template files.
 *      theme           : Default theme.
 */
class NativeRender extends Render
{
    /**
     * @inheritdoc
     */
    protected function onRender($template, $theme, array $variables, array $options = [])
    {
        $filePath = $this->getTemplatePath($theme, $template);

        if (!is_file($filePath)) {
            $filePath = $this->getTemplatePath($this->getDefaultTheme(), $template);

            if (!is_file($filePath)) {
                throw new RuntimeException("Template [$template] not found with theme [$theme].");
            }
        }

        // Injects variables.
        extract($variables, EXTR_OVERWRITE);

        include $filePath;
    }

    /**
     * Gets the template file for given theme and template name.
     *
     * @param string $theme
     * @param string $template
     * @return string
     */
    protected function getTemplatePath($theme, $template)
    {
        $template = str_replace('/', DIRECTORY_SEPARATOR, $template);

        return $this->getTemplateDirectory() . $theme . DIRECTORY_SEPARATOR . $template . '.' . $this->getTemplateExt();
    }
}