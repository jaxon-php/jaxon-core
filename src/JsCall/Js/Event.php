<?php

namespace Jaxon\JsCall\Js;

use Jaxon\JsCall\JsExpr;
use JsonSerializable;
use Stringable;

class Event implements JsonSerializable, Stringable
{
    /**
     * @var string
     */
    private $sName;

    /**
     * @var JsExpr
     */
    private $xHandler;

    /**
     * The constructor.
     *
     * @param string $sName    The event name
     * @param JsExpr $xHandler   The event handler
     */
    public function __construct(string $sName, JsExpr $xHandler)
    {
        $this->sName = $sName;
        $this->xHandler = $xHandler;
    }

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
            'func' => $this->xHandler->jsonSerialize(),
        ];
    }

    /**
     * Returns a string representation of this call
     *
     * @return string
     */
    public function __toString(): string
    {
        return "on('{$this->sName}', () => { " . $this->xHandler->__toString() . '; })';
    }
}
