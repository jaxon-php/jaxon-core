<?php

/**
 * Template.php - Trait for template functions
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Features;

trait Template
{
    /**
     * Render a template
     *
     * @param string        $sTemplate            The name of template to be rendered
     * @param array         $aVars                The template vars
     *
     * @return string        The template content
     */
    public function render($sTemplate, array $aVars = [])
    {
        return jaxon()->di()->getTemplateEngine()->render($sTemplate, $aVars);
    }
}
