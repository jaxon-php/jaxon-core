<?php

/**
 * Template.php - Template engine
 *
 * Generate from templates with template vars.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils;

class Template
{
    protected $sTemplateDir;
    protected $xEngine;

    public function __construct($sTemplateDir)
    {
        $this->xEngine = new \Latte\Engine;
        $this->sTemplateDir = trim($sTemplateDir);
    }

    /**
     * Set a cache directory for the template engine
     *
     * @param string        $sCacheDir            The cache directory
     *
     * @return void
     */
    public function setCacheDir($sCacheDir)
    {
        $sCacheDir = (string)$sCacheDir;
        if(is_writable($sCacheDir))
        {
            $this->xEngine->setTempDirectory($sCacheDir);
        }
    }

    /**
     * Render a template
     *
     * @param string        $sTemplate            The name of template to be rendered
     * @param string        $aVars                The template vars
     *
     * @return string        The template content
     */
    public function render($sTemplate, array $aVars = array())
    {
        $sRendered = $this->xEngine->renderToString($this->sTemplateDir . '/' . $sTemplate, $aVars);
        return $sRendered;
    }
}
