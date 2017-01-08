<?php

namespace Jaxon\JQuery\Dom;

use Jaxon\Jaxon, Jaxon\Request\Request, Jaxon\Request\Interfaces\Parameter;

class Action extends Request
{
    /**
     * The constructor.
     * 
     * @param string        $sMethod            The jQuery function
     * @param string        $aArguments         The arguments of the jQuery function
     */
    public function __construct($sMethod, $aArguments)
    {
        parent::__construct($sMethod, 'jquery');
        foreach($aArguments as $xArgument)
        {
            if($xArgument instanceof Request)
            {
                $this->addParameter(Jaxon::JS_VALUE, 'function(){' . $xArgument->getScript() . ';}');
            }
            else if($xArgument instanceof Parameter)
            {
                $this->addParameter($xArgument->getType(), $xArgument->getValue());
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
        $this->useSingleQuotes();
    }
}
