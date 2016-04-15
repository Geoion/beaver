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
use Beaver\Util\Arrays;
use Smarty;

/**
 * A render that adapt to Smarty.
 *
 * @author You Ming
 *
 * [Options]
 *  debug           : Debug mode.
 *  template        : Template options.
 *      directory       : The directory for templates.
 *      extension       : The extension of template files.
 *      theme           : Default theme.
 *  cache           : Template cache options.
 *      directory       : The directory for template caches.
 *  compile         : Template compile options.
 *      directory       : The directory for compiled template files.
 */
class SmartyRender extends Render
{
    /**
     * @inheritdoc
     */
    protected function onRender($template, $theme, array $variables, array $options = [])
    {
        $template = $template . '.' . $this->getTemplateExt();

        $smarty = new Smarty();

        $this->configure($smarty, $theme, $options);

        $smarty->assign($variables);
        $smarty->display($template);
    }

    /**
     * Configures Smarty.
     *
     * @param Smarty $smarty
     * @param string $theme
     * @param array $options
     */
    protected function configure(Smarty $smarty, $theme, array $options)
    {
        $debug = Arrays::dotGet($options, 'debug', false);
        $smarty->setDebugging($debug);

        $templateDirs = [
            $this->getTemplateDirectory() . $theme,
            $this->getTemplateDirectory() . $this->getDefaultTheme(),
            $this->getTemplateDirectory(),
        ];
        $smarty->setTemplateDir($templateDirs);
        
        $cacheDir = Arrays::dotGet($options, 'cache.directory', $this->context->getCacheDir() . 'template' . DIRECTORY_SEPARATOR);
        $smarty->setCacheDir($cacheDir);
        
        $compileDir = Arrays::dotGet($options, 'compile.directory', $this->context->getCacheDir() . 'template_c' . DIRECTORY_SEPARATOR);
        $smarty->setCompileDir($compileDir);
    }
}