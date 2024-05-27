<?php

namespace Jaxon\JsCall\Js;

use JsonSerializable;
use Stringable;

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
     * @param string $sPath    The jQuery selector path
     * @param mixed $xContext    A context associated to the selector
     */
    public function __construct(string $sPath, $xContext)
    {
        $this->sScript = $this->getPathAsStr($sPath, $xContext);

        $sName = $sPath ?? 'this';
        $this->aCall = ['_type' => 'select', '_name' => $sName];
        if(($xContext))
        {
            $this->aCall['context'] = is_a($xContext, JsonSerializable::class) ?
                $xContext->jsonSerialize() : $xContext;
        }
    }

    /**
     * Get the selector js.
     *
     * @param string $sPath    The jQuery selector path
     * @param mixed $xContext    A context associated to the selector
     *
     * @return string
     */
    private function getPathAsStr(string $sPath, $xContext)
    {
        $jQuery = 'jaxon.jq'; // The JQuery selector
        if(!$sPath)
        {
            // If an empty selector is given, use the event target instead
            return "$jQuery(e.currentTarget)";
        }
        if(!$xContext)
        {
            return "$jQuery('" . $sPath . "')";
        }

        $sContext = is_a($xContext, self::class) ? $xContext->getScript() :
            "$jQuery('" . trim("{$xContext}") . "')";
        return "$jQuery('{$sPath}', $sContext)";
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
