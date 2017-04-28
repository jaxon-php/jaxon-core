<?php

namespace Jaxon\Module;

use Jaxon\Module\Interfaces\View as ViewInterface;
use Jaxon\Utils\Traits\Template;

class View implements ViewInterface
{
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
        // This method is provided by the Template trait
        jaxon()->addViewNamespace($sNamespace, $sDirectory, $sExtension);
    }

    /**
     * Render a view
     * 
     * @param Store         $store        A store populated with the view data
     * 
     * @return string        The string representation of the view
     */
    public function render(View\Store $store)
    {
        $sViewName = $store->getViewName();
        $sNamespace = $store->getNamespace();
        // In this view renderer, the namespace must always be prepended to the view name.
        if(substr($sViewName, 0, strlen($sNamespace) + 2) != $sNamespace . '::')
        {
            $sViewName = $sNamespace . '::' . $sViewName;
        }
        // Render the template
        return trim(jaxon()->render($sViewName, $store->getViewData()), " \t\n");
    }
}
