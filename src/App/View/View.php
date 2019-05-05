<?php

namespace Jaxon\App\View;

use Jaxon\Contracts\App\View as ViewContract;
use Jaxon\Utils\Template\Template;

class View implements ViewContract
{
    /**
     * @var Template        The Jaxon template renderer
     */
    protected $xTemplate;

    /**
     * The class constructor
     */
    public function __construct(Template $xTemplate)
    {
        $this->xTemplate = $xTemplate;
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
    public function addNamespace($sNamespace, $sDirectory, $sExtension = '')
    {
        $this->xTemplate->addNamespace($sNamespace, $sDirectory, $sExtension);
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
        return trim($this->xTemplate->render($sViewName, $store->getViewData()), " \t\n");
    }
}
