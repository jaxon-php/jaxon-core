<?php

/**
 * Metadata.php
 *
 * Jaxon component metadata.
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

class Metadata
{
    /**
     * @var array<string, array<string, Data\AbstractData>>
     */
    private array $aAttributes = [
        'exclude' => [],
        'container' => [],
        'databag' => [],
        'callback' => [],
        'before' => [],
        'after' => [],
        'upload' => [],
    ];

    /**
     * @return array<string, array<string, Data\AbstractData>>
     */
    public function getAttributes(): array
    {
        return $this->aAttributes;
    }

    /**
     * @param string $sMethod
     *
     * @return Data\ExcludeData
     */
    public function exclude(string $sMethod = '*'): Data\ExcludeData
    {
        return $this->aAttributes['exclude'][$sMethod] ??
            $this->aAttributes['exclude'][$sMethod] = new Data\ExcludeData();
    }

    /**
     * @param string $sMethod
     *
     * @return Data\ContainerData
     */
    public function container(string $sMethod = '*'): Data\ContainerData
    {
        return $this->aAttributes['container'][$sMethod] ??
            $this->aAttributes['container'][$sMethod] = new Data\ContainerData();
    }

    /**
     * @param string $sMethod
     *
     * @return Data\DatabagData
     */
    public function databag(string $sMethod = '*'): Data\DatabagData
    {
        return $this->aAttributes['databag'][$sMethod] ??
            $this->aAttributes['databag'][$sMethod] = new Data\DatabagData();
    }

    /**
     * @param string $sMethod
     *
     * @return Data\CallbackData
     */
    public function callback(string $sMethod = '*'): Data\CallbackData
    {
        return $this->aAttributes['callback'][$sMethod] ??
            $this->aAttributes['callback'][$sMethod] = new Data\CallbackData();
    }

    /**
     * @param string $sMethod
     *
     * @return Data\BeforeData
     */
    public function before(string $sMethod = '*'): Data\BeforeData
    {
        return $this->aAttributes['before'][$sMethod] ??
            $this->aAttributes['before'][$sMethod] = new Data\BeforeData();
    }

    /**
     * @param string $sMethod
     *
     * @return Data\AfterData
     */
    public function after(string $sMethod = '*'): Data\AfterData
    {
        return $this->aAttributes['after'][$sMethod] ??
            $this->aAttributes['after'][$sMethod] = new Data\AfterData();
    }

    /**
     * @param string $sMethod
     *
     * @return Data\UploadData
     */
    public function upload(string $sMethod = '*'): Data\UploadData
    {
        return $this->aAttributes['upload'][$sMethod] ??
            $this->aAttributes['upload'][$sMethod] = new Data\UploadData();
    }

    /**
     * True if the class is excluded
     *
     * @return bool
     */
    public function isExcluded(): bool
    {
        $xData = $this->aAttributes['exclude']['*'] ?? null;
        return $xData !== null && $xData->getValue() === true;
    }

    /**
     * Get the properties of the class methods
     *
     * @return array
     */
    public function getProperties(): array
    {
        $aProperties = [];
        $aClassProperties = [];
        foreach($this->aAttributes as $sType => $aValues)
        {
            if($sType === 'exclude')
            {
                continue;
            }

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
     * Get the protected methods
     *
     * @return array
     */
    public function getProtectedMethods(): array
    {
        /** @var array<Data\ExcludeData> */
        $aAttributes = $this->aAttributes['exclude'];
        $aMethods = array_keys($aAttributes);
        return array_values(array_filter($aMethods, fn(string $sName) =>
            $sName !== '*' && $aAttributes[$sName]->getValue() === true));
    }
}
