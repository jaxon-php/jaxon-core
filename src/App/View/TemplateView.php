<?php

/**
 * TemplateView.php
 *
 * A view wrapper for the template library provided by the jaxon-utils package.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\View;

use Jaxon\Utils\Template\TemplateEngine;

class TemplateView implements ViewInterface
{
    /**
     * The Jaxon template engine
     *
     * @var TemplateEngine
     */
    protected $xTemplateEngine;

    /**
     * The class constructor
     */
    public function __construct(TemplateEngine $xTemplateEngine)
    {
        $this->xTemplateEngine = $xTemplateEngine;
    }

    /**
     * Add a namespace to this view renderer
     *
     * @param string $sNamespace    The namespace name
     * @param string $sDirectory    The namespace directory
     * @param string $sExtension    The extension to append to template names
     *
     * @return void
     */
    public function addNamespace(string $sNamespace, string $sDirectory, string $sExtension = '')
    {
        $this->xTemplateEngine->addNamespace($sNamespace, $sDirectory, $sExtension);
    }

    /**
     * Render a view
     *
     * @param Store $store    A store populated with the view data
     *
     * @return string        The string representation of the view
     */
    public function render(Store $store): string
    {
        $sViewName = $store->getViewName();
        $sNamespace = $store->getNamespace();
        // In this view renderer, the namespace must always be prepended to the view name.
        if(substr($sViewName, 0, strlen($sNamespace) + 2) != $sNamespace . '::')
        {
            $sViewName = $sNamespace . '::' . $sViewName;
        }
        // Render the template
        return trim($this->xTemplateEngine->render($sViewName, $store->getViewData()), " \t\n");
    }
}
