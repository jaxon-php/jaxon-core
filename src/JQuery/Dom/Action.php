<?php

namespace Jaxon\JQuery\Dom;

use Jaxon\Jaxon;
use Jaxon\Request\JsCall;
use Jaxon\Request\Request;
use Jaxon\Request\Interfaces\Parameter;

class Action extends JsCall
{
    /**
     * The constructor.
     * 
     * @param string        $sMethod            The jQuery function
     * @param string        $aArguments         The arguments of the jQuery function
     */
    public function __construct($sMethod, $aArguments)
    {
        parent::__construct($sMethod);
        // Always use single quotes
        $this->useSingleQuotes();
        // Add the arguments to the parameter list
        $this->addParameters($aArguments);
    }
}
