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

/**
 * A render output response as JSON text.
 *
 * @author You Ming
 */
class JsonRender extends Render
{
    /**
     * @inheritdoc
     */
    protected $contentType = 'text/json';

    /**
     * @inheritdoc
     */
    protected function onRender($template, $theme, array $variables, array $options = [])
    {
        $flags = isset($options['flags']) ? $options['flags'] : JSON_UNESCAPED_SLASHES;
        $depth = isset($options['depth']) ? $options['depth'] : 512;

        echo json_encode($variables, $flags, $depth);
    }
}
