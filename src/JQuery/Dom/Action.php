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
        foreach($aArguments as $xArgument)
        {
            if($xArgument instanceof Request)
            {
                $this->addParameter(Jaxon::JS_VALUE, 'function(){' . $xArgument->getScript() . ';}');
            }
            else if($xArgument instanceof Parameter)
            {
                $this->pushParameter($xArgument);
            }
            else if(is_numeric($xArgument))
            {
                $this->addParameter(Jaxon::NUMERIC_VALUE, $xArgument);
            }
            else if(is_string($xArgument))
            {
                $this->addParameter(Jaxon::QUOTED_VALUE, $xArgument);
            }
            else if(is_bool($xArgument))
            {
                $this->addParameter(Jaxon::BOOL_VALUE, $xArgument);
            }
            else if(is_array($xArgument) || is_object($xArgument))
            {
                $this->addParameter(Jaxon::JS_VALUE, $xArgument);
            }
        }
    }
}
