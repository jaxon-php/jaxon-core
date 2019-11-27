<?php

/**
 * Renderer.php - Template renderer interface
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2019 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Contracts\Template;

interface Renderer
{
    /**
     * Render a template
     *
     * @param string        $sTemplate            The name of template to be rendered
     * @param array         $aVars                The template vars
     *
     * @return string
     */
    public function render($sTemplate, array $aVars = []);
}
