<?php

/**
 * Metadata.php
 *
 * Callable class metadata.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Metadata;

use function array_filter;
use function array_keys;
use function array_values;
use function count;

class Metadata implements MetadataInterface
{
    /**
     * @var array<Data\ExcludeData>
     */
    private array $aExcludes = [];

    /**
     * @var array<Data\ContainerData>
     */
    private array $aContainers = [];

    /**
     * @var array<Data\DatabagData>
     */
    private array $aDatabags = [];

    /**
     * @var array<Data\CallbackData>
     */
    private array $aCallbacks = [];

    /**
     * @var array<Data\BeforeData>
     */
    private array $aBefores = [];

    /**
     * @var array<Data\AfterData>
     */
    private array $aAfters = [];

    /**
     * @var array<Data\UploadData>
     */
    private array $aUploads = [];

    // /**
    //  * @param bool $bIsExcluded
    //  * @param array $aProperties
    //  * @param array $aProtectedMethods
    //  */
    // public function __construct(private bool $bIsExcluded,
    //     private array $aProperties, private array $aProtectedMethods)
    // {}

    /**
     * @param string $sMethod
     *
     * @return Data\ExcludeData
     */
    public function exclude(string $sMethod = '*'): Data\ExcludeData
    {
        if(!isset($this->aExcludes[$sMethod]))
        {
            $this->aExcludes[$sMethod] = new Data\ExcludeData();
        }
        return $this->aExcludes[$sMethod];
    }

    /**
     * @param string $sMethod
     *
     * @return Data\ContainerData
     */
    public function container(string $sMethod = '*'): Data\ContainerData
    {
        if(!isset($this->aContainers[$sMethod]))
        {
            $this->aContainers[$sMethod] = new Data\ContainerData();
        }
        return $this->aContainers[$sMethod];
    }

    /**
     * @param string $sMethod
     *
     * @return Data\DatabagData
     */
    public function databag(string $sMethod = '*'): Data\DatabagData
    {
        if(!isset($this->aDatabags[$sMethod]))
        {
            $this->aDatabags[$sMethod] = new Data\DatabagData();
        }
        return $this->aDatabags[$sMethod];
    }

    /**
     * @param string $sMethod
     *
     * @return Data\CallbackData
     */
    public function callback(string $sMethod = '*'): Data\CallbackData
    {
        if(!isset($this->aCallbacks[$sMethod]))
        {
            $this->aCallbacks[$sMethod] = new Data\CallbackData();
        }
        return $this->aCallbacks[$sMethod];
    }

    /**
     * @param string $sMethod
     *
     * @return Data\BeforeData
     */
    public function before(string $sMethod = '*'): Data\BeforeData
    {
        if(!isset($this->aBefores[$sMethod]))
        {
            $this->aBefores[$sMethod] = new Data\BeforeData();
        }
        return $this->aBefores[$sMethod];
    }

    /**
     * @param string $sMethod
     *
     * @return Data\AfterData
     */
    public function after(string $sMethod = '*'): Data\AfterData
    {
        if(!isset($this->aAfters[$sMethod]))
        {
            $this->aAfters[$sMethod] = new Data\AfterData();
        }
        return $this->aAfters[$sMethod];
    }

    /**
     * @param string $sMethod
     *
     * @return Data\UploadData
     */
    public function upload(string $sMethod = '*'): Data\UploadData
    {
        if(!isset($this->aUploads[$sMethod]))
        {
            $this->aUploads[$sMethod] = new Data\UploadData();
        }
        return $this->aUploads[$sMethod];
    }

    /**
     * @inheritDoc
     */
    public function isExcluded(): bool
    {
        return isset($this->aExcludes['*']) && $this->aExcludes['*']->getValue() === true;
    }

    /**
     * @inheritDoc
     */
    public function getProperties(): array
    {
        $aAttributes = [
            // $this->aExcludes,
            $this->aContainers,
            $this->aDatabags,
            $this->aCallbacks,
            $this->aBefores,
            $this->aAfters,
            $this->aUploads,
        ];
        $aProperties = [];
        $aClassProperties = [];

        foreach($aAttributes as $aValues)
        {
            foreach($aValues as $sMethod => $xData)
            {
                if($sMethod === '*')
                {
                    $aClassProperties[$xData->getName()] = $xData->getValue();
                    continue;
                }
                $aProperties[$sMethod][$xData->getName()] = $xData->getValue();
            }
        }

        if(count($aClassProperties) > 0)
        {
            $aProperties['*'] = $aClassProperties;
        }

        return $aProperties;
    }

    /**
     * @inheritDoc
     */
    public function getProtectedMethods(): array
    {
        $aMethods = array_keys($this->aExcludes);
        return array_values(array_filter($aMethods, fn(string $sName) =>
            $sName !== '*' && $this->aExcludes[$sName]->getValue() === true));
    }

    /**
     * @return array
     */
    public function encode(): array
    {
        $aAttributes = [
            'exclude' => $this->aExcludes,
            'container' => $this->aContainers,
            'databag' => $this->aDatabags,
            'callback' => $this->aCallbacks,
            'before' => $this->aBefores,
            'after' => $this->aAfters,
            'upload' => $this->aUploads,
        ];

        $sVar = '$'; // The dollar car.
        $aCalls = [
            "{$sVar}xMetadata = new " . Metadata::class . '();'
        ];
        foreach($aAttributes as $sAttr => $aValues)
        {
            if(count($aValues) === 0)
            {
                continue;
            }
            foreach($aValues as $sMethod => $xData)
            {
                $aCalls[] = "{$sVar}xData = {$sVar}xMetadata->{$sAttr}('$sMethod');";
                foreach($xData->encode("{$sVar}xData") as $sCall)
                {
                    $aCalls[] = $sCall;
                }
            }
        }
        $aCalls[] = "return {$sVar}xMetadata;";
        return $aCalls;
    }
}
