<?php

namespace Jaxon\Ui\Template;

use Jaxon\Contracts\View as ViewContract;
use Jaxon\Ui\View\Store;
use Jaxon\Utils\Template\Engine;

class View implements ViewContract
{
    /**
     * The Jaxon template engine
     *
     * @var Engine
     */
    protected $xEngine;

    /**
     * The class constructor
     */
    public function __construct(Engine $xEngine)
    {
        $this->xEngine = $xEngine;
    }

    /**
     * Add a namespace to this view renderer
     *
     * @param string        $sNamespace         The namespace name
     * @param string        $sDirectory         The namespace directory
     * @param string        $sExtension         The extension to append to template names
     *
     * @return void
     */
    public function addNamespace(string $sNamespace, string $sDirectory, string $sExtension = '')
    {
        $this->xEngine->addNamespace($sNamespace, $sDirectory, $sExtension);
    }

    /**
     * Render a view
     *
     * @param Store         $store        A store populated with the view data
     *
     * @return string        The string representation of the view
     */
    public function render(Store $store)
    {
        $sViewName = $store->getViewName();
        $sNamespace = $store->getNamespace();
        // In this view renderer, the namespace must always be prepended to the view name.
        if(substr($sViewName, 0, strlen($sNamespace) + 2) != $sNamespace . '::')
        {
            $sViewName = $sNamespace . '::' . $sViewName;
        }
        // Render the template
        return trim($this->xEngine->render($sViewName, $store->getViewData()), " \t\n");
    }
}
