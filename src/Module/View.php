<?php

namespace Jaxon\Module;

use View\Store;
use View\Facade;

class View extends Facade
{
    /**
     * Render a view
     * 
     * @param Store         $store        A store populated with the view data
     * 
     * @return string        The string representation of the view
     */
    public function make(Store $store)
    {
        // TODO: render the template with the Jaxon\Utils\Template class.
        return "";
    }
}
