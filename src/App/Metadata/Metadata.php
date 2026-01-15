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
        'export' => [],
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
     * @return Data\ExportData
     */
    public function export(string $sMethod = '*'): Data\ExportData
    {
        $sMethod = '*'; // On classes only
        return $this->aAttributes['export'][$sMethod] ??= new Data\ExportData();
    }

    /**
     * @param string $sMethod
     *
     * @return Data\ContainerData
     */
    public function container(string $sMethod = '*'): Data\ContainerData
    {
        return $this->aAttributes['container'][$sMethod] ??= new Data\ContainerData();
    }

    /**
     * @param string $sMethod
     *
     * @return Data\DatabagData
     */
    public function databag(string $sMethod = '*'): Data\DatabagData
    {
        return $this->aAttributes['databag'][$sMethod] ??= new Data\DatabagData();
    }

    /**
     * @param string $sMethod
     *
     * @return Data\CallbackData
     */
    public function callback(string $sMethod = '*'): Data\CallbackData
    {
        return $this->aAttributes['callback'][$sMethod] ??= new Data\CallbackData();
    }

    /**
     * @param string $sMethod
     *
     * @return Data\BeforeData
     */
    public function before(string $sMethod = '*'): Data\BeforeData
    {
        return $this->aAttributes['before'][$sMethod] ??= new Data\BeforeData();
    }

    /**
     * @param string $sMethod
     *
     * @return Data\AfterData
     */
    public function after(string $sMethod = '*'): Data\AfterData
    {
        return $this->aAttributes['after'][$sMethod] ??= new Data\AfterData();
    }

    /**
     * @param string $sMethod
     *
     * @return Data\UploadData
     */
    public function upload(string $sMethod = '*'): Data\UploadData
    {
        return $this->aAttributes['upload'][$sMethod] ??= new Data\UploadData();
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
            // These attributes are processed in the getExportMethods() method.
            if($sType === 'exclude' || $sType === 'export')
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
     * Get the methods in the export attributes
     *
     * @return array
     */
    public function getExportMethods(): array
    {
        /** @var array<Data\ExcludeData> */
        $aAttributes = $this->aAttributes['exclude'];
        $aExcludeMethods = array_keys($aAttributes);
        $aExcludeMethods = array_values(array_filter($aExcludeMethods,
            fn(string $sName) => $sName !== '*' &&
                $aAttributes[$sName]->getValue() === true));

        /** @var Data\ExportData|null */
        $xExportData = $this->aAttributes['export']['*'] ?? null;
        $aExportMethods = $xExportData?->getValue() ?? [];

        $aExceptMethods = $aExportMethods['except'] ?? [];
        $aExportMethods['except'] = [...$aExcludeMethods, ...$aExceptMethods];
        return $aExportMethods;
    }

    /**
     * Get the exluded methods
     *
     * @return array
     */
    public function getExceptMethods(): array
    {
        return $this->getExportMethods()['except'];
    }

    /**
     * Get the export base methods
     *
     * @return array
     */
    public function getExportBaseMethods(): array
    {
        return $this->getExportMethods()['base'] ?? [];
    }

    /**
     * Get the export only methods
     *
     * @return array
     */
    public function getExportOnlyMethods(): array
    {
        return $this->getExportMethods()['only'] ?? [];
    }
}
