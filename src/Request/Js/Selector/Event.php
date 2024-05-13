<?php

namespace Jaxon\Request\Js\Selector;

use Jaxon\Request\Js\Call;

class Event
{
    /**
     * @var string
     */
    private $sName;

    /**
     * @var Call
     */
    private $xHandler;

    /**
     * The constructor.
     *
     * @param string $sName    The event name
     * @param Call $xHandler   The event handler
     */
    public function __construct(string $sName, Call $xHandler)
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
}
