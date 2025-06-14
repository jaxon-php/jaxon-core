<?php

namespace Jaxon\Script\Action;

use Jaxon\Script\JsExpr;
use JsonSerializable;

class Event implements JsonSerializable
{
    /**
     * The constructor.
     *
     * @param string $sMode    The event mode: 'jq' or 'js'
     * @param string $sName    The event name
     * @param JsExpr $xHandler   The event handler
     */
    public function __construct(private string $sMode,
        private string $sName, private JsExpr $xHandler)
    {}

    /**
     * Convert this call to array, when converting the response into json.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            '_type' => 'event',
            '_name' => $this->sName,
            'mode' => $this->sMode,
            'func' => $this->xHandler->jsonSerialize(),
        ];
    }
}
