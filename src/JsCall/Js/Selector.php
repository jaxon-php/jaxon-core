<?php

namespace Jaxon\JsCall\Js;

use JsonSerializable;
use Stringable;

use function is_a;
use function trim;

class Selector implements JsonSerializable, Stringable
{
    /**
     * @var string
     */
    private $sScript;

    /**
     * @var array
     */
    private $aCall;

    /**
     * The constructor.
     *
     * @param string $sPath    The selector path
     * @param string $sMode    The selector mode: 'jq' or 'js'
     * @param mixed $xContext    A context associated to the selector
     */
    public function __construct(string $sPath, string $sMode, $xContext = null)
    {
        $sName = $sPath ?? 'this';
        $this->aCall = ['_type' => 'select', '_name' => $sName, 'mode' => $sMode];
        if(($sPath) && ($xContext))
        {
            $this->aCall['context'] = is_a($xContext, JsonSerializable::class) ?
                $xContext->jsonSerialize() : $xContext;
        }

        $this->sScript = $this->getPathAsStr($sName, $sMode, $xContext);
    }

    /**
     * Get the selector js.
     *
     * @param string $sPath    The jQuery selector path
     * @param string $sMode    The selector mode: 'jq' or 'js'
     * @param mixed $xContext    A context associated to the selector
     *
     * @return string
     */
    private function getPathAsStr(string $sPath, string $sMode, $xContext)
    {
        if(!$xContext)
        {
            return $sMode === 'jq' ? "jaxon.jq('$sPath')" : $sPath;
        }

        $sContext = is_a($xContext, self::class) ? $xContext->getScript() :
            "jaxon.jq('" . trim("{$xContext}") . "')";
        return "jaxon.jq('{$sPath}', $sContext)";
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

    /**
     * Returns a string representation of this call
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->sScript;
    }
}
