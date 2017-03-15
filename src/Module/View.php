<?php

namespace Jaxon\Module;

use Jaxon\Utils\Traits\Template;

class View extends View\Facade
{
    public function __construct()
    {
        parent::__construct();
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
        return trim(jaxon()->render($store->getViewPath(), $store->getViewData()), " \t\n");
    }
}
