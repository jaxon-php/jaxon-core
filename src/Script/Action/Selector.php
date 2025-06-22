<?php

namespace Jaxon\Script\Action;

use JsonSerializable;

use function is_a;
use function trim;

class Selector implements JsonSerializable
{
    /**
     * @var array
     */
    private $aCall;

    /**
     * The constructor.
     *
     * @param string $sMode    The selector mode: 'jq' or 'js'
     * @param string $sPath    The selector path
     * @param mixed $xContext    A context associated to the selector
     */
    public function __construct(string $sMode, string $sPath, $xContext = null)
    {
        $sName = trim($sPath) ?: 'this';
        $this->aCall = ['_type' => 'select', '_name' => $sName, 'mode' => $sMode];
        if($sName !== 'this' && $xContext !== null)
        {
            $this->aCall['context'] = is_a($xContext, JsonSerializable::class) ?
                $xContext->jsonSerialize() : $xContext;
        }
    }

    /**
     * Convert this call to array, when converting the response into json.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->aCall;
    }
}
