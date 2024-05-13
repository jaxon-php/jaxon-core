<?php

namespace Jaxon\Plugin\Response\JQuery\Call;

use Jaxon\Request\Call\JsCall;

class Method extends JsCall
{
    /**
     * The constructor.
     *
     * @param string $sMethod    The jQuery function
     * @param array $aArguments    The arguments of the jQuery function
     */
    public function __construct(string $sMethod, array $aArguments)
    {
        parent::__construct($sMethod);
        // Add the arguments to the parameter list
        $this->addParameters($aArguments);
    }

    /**
     * Returns a string representation of this call
     *
     * @return string
     */
    public function __toString(): string
    {
        return '.' . parent::__toString();
    }
}
