<?php

namespace Jaxon\JQuery\Dom;

use Jaxon\Jaxon, Jaxon\Request\Request, Jaxon\Request\Parameter, ArrayAccess;

class Action extends Request
{
    /**
     * The jQuery function
     *
     * @var string
     */
    protected $sMethod;

    /**
     * The arguments of the jQuery function
     *
     * @var array
     */
    protected $aArguments;

    /**
     * The constructor.
     * 
     * @param string        $sMethod            The jQuery function
     * @param string        $aArguments         The arguments of the jQuery function
     */
    public function __construct($sMethod, $aArguments)
    {
        parent::__construct($sMethod, $aArguments);
        $this->sMethod = $sMethod;
        $this->aArguments = $aArguments;
    }

    /**
     * Return a string representation of the call to this jQuery function
     *
     * @return string
     */
    public function getScript()
    {
        $this->useSingleQuotes();
        foreach($this->aArguments as $xArgument)
        {
            if($xArgument instanceof Element)
            {
                $this->addParameter(Jaxon::JS_VALUE, $xArgument->getScript());
            }
            else if($xArgument instanceof Parameter)
            {
                $this->addParameter($xArgument->getType(), $xArgument->getValue());
            }
            else if($xArgument instanceof Request)
            {
                $this->addParameter(Jaxon::JS_VALUE, 'function(){' . $xArgument->getScript() . ';}');
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
        return $this->sMethod . '(' . implode(', ', $this->aParameters) . ')';
    }
}
