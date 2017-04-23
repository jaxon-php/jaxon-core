<?php

namespace Jaxon\Module;

use Jaxon\Module\Interfaces\View as ViewRenderer;
use Jaxon\Utils\Traits\Template;

class View implements ViewRenderer
{
    use Template;

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
        // This method is provided by the Config trait
        $this->addViewNamespace($sNamespace, $sDirectory, $sExtension);
    }

    /**
     * Render a view
     * 
     * @param Store         $store        A store populated with the view data
     * 
     * @return string        The string representation of the view
     */
    public function make(View\Store $store)
    {
        // Render the template
        return trim($this->render($store->getViewName(), $store->getViewData()), " \t\n");
    }
}
