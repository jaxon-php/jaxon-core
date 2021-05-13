<?php

/**
 * Context.php - Template context
 *
 * A context for a template being rendered.
 *
 * The "$this" var in a template will refer to an instance of this
 * class, which will then provide the template variables, and the
 * render() method, to render a template inside of another.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils\Template;

class Context
{
    /**
     * The constructor
     *
     * @param Engine $engine
     */
    public function __construct(Engine $engine)
    {
        $this->__engine__ = $engine;
    }

    /**
     * Render a template
     *
     * @param string        $sTemplate            The name of template to be rendered
     * @param array         $aVars                The template vars
     *
     * @return string
     */
    public function render($sTemplate, array $aVars = [])
    {
        return $this->__engine__->render($sTemplate, $aVars);
    }
}
