<?php

namespace Jaxon\App\DataBag;

use Jaxon\Plugin\Response\DataBag\DataBagPlugin;
use JsonSerializable;

use function array_map;
use function is_array;
use function key_exists;

class DataBag implements JsonSerializable
{
    /**
     * @var DataBagPlugin
     */
    protected $xPlugin;

    /**
     * @var array
     */
    protected $aData = [];

    /**
     * @var bool
     */
    protected $bTouched = false;

    /**
     * The constructor
     *
     * @param array $aData
     */
    public function __construct(DataBagPlugin $xPlugin, array $aData)
    {
        $this->xPlugin = $xPlugin;
        // Ensure all contents are arrays.
        $this->aData = array_map(function($aValue) {
            return is_array($aValue) ? $aValue : [];
        }, $aData);
    }

    /**
     * @return bool
     */
    public function touched(): bool
    {
        return $this->bTouched;
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->aData;
    }

    /**
     * @param string $sBag
     *
     * @return void
     */
    public function clear(string $sBag)
    {
        $this->aData[$sBag] = [];
        $this->xPlugin->addCommand('databag.clear', ['bag' => $sBag]);
    }

    /**
     * @param string $sBag
     * @param string $sKey
     * @param mixed $xValue
     *
     * @return void
     */
    public function set(string $sBag, string $sKey, $xValue)
    {
        $this->bTouched = true;
        $this->aData[$sBag][$sKey] = $xValue;
    }

    /**
     * @param string $sBag
     * @param string $sKey
     * @param mixed $xValue
     *
     * @return void
     */
    public function new(string $sBag, string $sKey, $xValue)
    {
        // Set the value only if it doesn't already exist.
        if(!isset($this->aData[$sBag]) || !key_exists($sKey, $this->aData[$sBag]))
        {
            $this->set($sBag, $sKey, $xValue);
        }
    }

    /**
     * @param string $sBag
     * @param string $sKey
     * @param mixed $xValue
     *
     * @return mixed
     */
    public function get(string $sBag, string $sKey, $xValue = null)
    {
        return $this->aData[$sBag][$sKey] ?? $xValue;
    }

    /**
     * Convert this call to array, when converting the response into json.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->aData;
    }
}
