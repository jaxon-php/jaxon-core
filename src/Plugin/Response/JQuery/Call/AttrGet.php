<?php

namespace Jaxon\Plugin\Response\JQuery\Call;

use JsonSerializable;

class AttrGet implements JsonSerializable
{
    /**
     * The attribute name
     *
     * @var string
     */
    private $sAttrName;

    /**
     * The constructor.
     *
     * @param string $sAttrName    The attribute name
     */
    public function __construct(string $sAttrName)
    {
        $this->sAttrName = $sAttrName;
    }

    /**
     * Convert this call to array, when converting the response into json.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            '_type' => 'attr',
            '_name' => $this->sAttrName,
        ];
    }
}
