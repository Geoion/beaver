<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver\Traits;

use Beaver\Context;
use Beaver\Registry;

/**
 * The trait for object that wants a context while resolving from container.
 *
 * @author You Ming
 */
trait ContextInjection
{
    /**
     * The current context.
     *
     * @var Context
     */
    protected $context;

    /**
     * Binds a context for this object.
     *
     * @param Context $context
     */
    public function bindContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Gets registry.
     *
     * @return Registry
     */
    public function getRegistry()
    {
        return $this->context->getRegistry();
    }
}