<?php

namespace Jaxon\JQuery\Dom\Call;

use Jaxon\Request\JsCall;

class Method extends JsCall
{
    /**
     * The constructor.
     *
     * @param string        $sMethod            The jQuery function
     * @param array         $aArguments         The arguments of the jQuery function
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
