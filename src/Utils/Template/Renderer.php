<?php

/**
 * Renderer.php - Template renderer
 *
 * Render PHP templates.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils\Template;

class Renderer
{
    /**
     * Render a template
     *
     * @param string        $sPath                The path to the template
     * @param string        $aVars                The template vars
     *
     * @return string        The template content
     */
    public function render($sPath, array $aVars = array())
    {
        // Make the template vars available as attributes
        foreach($aVars as $sName => $xValue)
        {
            $sName = (string)$sName;
            $this->$sName = $xValue;
        }
        // Render the template
        ob_start();
        include($sPath);
        $sRendered = ob_get_clean();
        return $sRendered;
    }
}
