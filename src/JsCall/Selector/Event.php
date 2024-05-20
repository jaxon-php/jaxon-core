<?php

namespace Jaxon\JsCall\Selector;

use Jaxon\JsCall\AbstractCall;
use JsonSerializable;
use Stringable;

class Event implements JsonSerializable, Stringable
{
    /**
     * @var string
     */
    private $sName;

    /**
     * @var AbstractCall
     */
    private $xHandler;

    /**
     * The constructor.
     *
     * @param string $sName    The event name
     * @param AbstractCall $xHandler   The event handler
     */
    public function __construct(string $sName, AbstractCall $xHandler)
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
        return ".on('{$this->sName}', () => { " . $this->xHandler->__toString() . '; })';
    }
}
