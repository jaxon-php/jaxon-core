<?php

/**
 * ComponentOptions.php
 *
 * Options of a callable object.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin\Request\CallableClass;

use Jaxon\App\Metadata\Metadata;

use function array_diff;
use function array_intersect;
use function array_map;
use function array_merge;
use function array_unique;
use function count;
use function explode;
use function in_array;
use function is_array;
use function is_string;
use function json_encode;
use function substr;
use function str_replace;
use function trim;

class ComponentOptions
{
    /**
     * Check if the js code for this object must be generated
     *
     * @var bool
     */
    private $bExcluded = false;

    /**
     * The character to use as separator in javascript class names
     *
     * @var string
     */
    private $sSeparator = '.';

    /**
     * A list of methods of the user registered callable object the library can export to javascript
     *
     * @var array
     */
    private $aPublicMethods = [];

    /**
     * A list of methods of the user registered callable object the library must not export to javascript
     *
     * @var array
     */
    private $aProtectedMethods = [];

    /**
     * The methods in the export attributes
     *
     * @var array
     */
    private $aExportMethods = [];

    /**
     * A list of methods to call before processing the request
     *
     * @var array
     */
    private $aBeforeMethods = [];

    /**
     * A list of methods to call after processing the request
     *
     * @var array
     */
    private $aAfterMethods = [];

    /**
     * The javascript class options
     *
     * @var array
     */
    private $aJsOptions = [];

    /**
     * The DI options
     *
     * @var array
     */
    private $aDiOptions = [];

    /**
     * The constructor
     *
     * @param array $aMethods
     * @param array $aOptions
     * @param Metadata|null $xMetadata
     */
    public function __construct(array $aMethods, array $aOptions, Metadata|null $xMetadata)
    {
        $this->bExcluded = ($xMetadata?->isExcluded() ?? false) ||
            (bool)($aOptions['excluded'] ?? false);
        if($this->bExcluded)
        {
            return;
        }

        $sSeparator = $aOptions['separator'];
        if($sSeparator === '_' || $sSeparator === '.')
        {
            $this->sSeparator = $sSeparator;
        }
        $this->addProtectedMethods($aOptions['protected']);

        foreach($aOptions['functions'] as $sNames => $aFunctionOptions)
        {
            // Names are in a comma-separated list.
            $aFunctionNames = explode(',', $sNames);
            foreach($aFunctionNames as $sFunctionName)
            {
                $this->addFunctionOptions($sFunctionName, $aFunctionOptions);
            }
        }

        if($xMetadata !== null)
        {
            $this->aExportMethods = $xMetadata->getExportMethods();
            $this->addProtectedMethods($xMetadata->getProtectedMethods());
            foreach($xMetadata->getProperties() as $sFunctionName => $aFunctionOptions)
            {
                $this->addFunctionOptions($sFunctionName, $aFunctionOptions);
            }
        }

        $this->aPublicMethods = $this->filterPublicMethods($aMethods);
    }

    /**
     * @param array|string $xMethods
     *
     * @return void
     */
    private function addProtectedMethods(array|string $xMethods): void
    {
        $this->aProtectedMethods = array_merge($this->aProtectedMethods,
            !is_array($xMethods) ? [trim((string)$xMethods)] :
            array_map(fn($sMethod) => trim((string)$sMethod), $xMethods));
    }

    /**
     * @param array $aMethods
     *
     * @return array
     */
    private function filterPublicMethods(array $aMethods): array
    {
        if($this->bExcluded || in_array('*', $this->aProtectedMethods))
        {
            return [];
        }

        $aBaseMethods = $aMethods[1];
        $aMethods = $aMethods[0];

        if(isset($this->aExportMethods['only']))
        {
            $aMethods = array_intersect($aMethods, $this->aExportMethods['only']);
        }
        $aMethods = array_diff($aMethods, $this->aProtectedMethods,
            $this->aExportMethods['except'] ?? []);
        if(count($aBaseMethods) > 0 && isset($this->aExportMethods['base']))
        {
            $aBaseMethods = array_diff($aBaseMethods, $this->aExportMethods['base']);
        }

        return array_diff($aMethods, $aBaseMethods);
    }

    /**
     * @param string $sMethodName
     *
     * @return bool
     */
    public function isPublicMethod(string $sMethodName): bool
    {
        return in_array($sMethodName, $this->aPublicMethods);
    }

    /**
     * Check if the js code for this object must be generated
     *
     * @return bool
     */
    public function excluded(): bool
    {
        return $this->bExcluded;
    }

    /**
     * @return string
     */
    public function separator(): string
    {
        return $this->sSeparator;
    }

    /**
     * @return array
     */
    public function beforeMethods(): array
    {
        return $this->aBeforeMethods;
    }

    /**
     * @return array
     */
    public function afterMethods(): array
    {
        return $this->aAfterMethods;
    }

    /**
     * @return array
     */
    public function diOptions(): array
    {
        return $this->aDiOptions;
    }

    /**
     * @return array
     */
    public function jsOptions(): array
    {
        return $this->aJsOptions;
    }

    /**
     * Set hook methods
     *
     * @param array $aHookMethods    The array of hook methods
     * @param string|array $xValue    The value of the configuration option
     *
     * @return void
     */
    private function setHookMethods(array &$aHookMethods, $xValue): void
    {
        foreach($xValue as $sCalledMethod => $xMethodToCall)
        {
            if(!isset($aHookMethods[$sCalledMethod]))
            {
                $aHookMethods[$sCalledMethod] = [];
            }
            if(is_array($xMethodToCall))
            {
                $aHookMethods[$sCalledMethod] = array_merge($aHookMethods[$sCalledMethod], $xMethodToCall);
            }
            elseif(is_string($xMethodToCall))
            {
                $aHookMethods[$sCalledMethod][] = $xMethodToCall;
            }
        }
    }

    /**
     * @param array $aDiOptions
     */
    private function addDiOption(array $aDiOptions): void
    {
        $this->aDiOptions = array_merge($this->aDiOptions, $aDiOptions);
    }

    /**
     * Set configuration options / call options for each method
     *
     * @param string $sName    The name of the configuration option
     * @param string|array $xValue    The value of the configuration option
     *
     * @return void
     */
    private function addOption(string $sName, $xValue): void
    {
        switch($sName)
        {
        // Set the methods to call before processing the request
        case '__before':
            $this->setHookMethods($this->aBeforeMethods, $xValue);
            break;
        // Set the methods to call after processing the request
        case '__after':
            $this->setHookMethods($this->aAfterMethods, $xValue);
            break;
        // Set the attributes to inject in the callable object
        case '__di':
            $this->addDiOption($xValue);
            break;
        default:
            break;
        }
    }

    /**
     * @param string $sFunctionName
     * @param string $sOptionName
     * @param mixed $xOptionValue
     *
     * @return void
     */
    private function _addJsArrayOption(string $sFunctionName, string $sOptionName, $xOptionValue): void
    {
        if(is_string($xOptionValue))
        {
            $xOptionValue = [$xOptionValue];
        }
        if(!is_array($xOptionValue))
        {
            return; // Do not save.
        }

        $aOptions = $this->aJsOptions[$sFunctionName][$sOptionName] ?? [];
        $this->aJsOptions[$sFunctionName][$sOptionName] = array_merge($aOptions, $xOptionValue);
    }

    /**
     * @param string $sFunctionName
     * @param string $sOptionName
     * @param mixed $xOptionValue
     *
     * @return void
     */
    private function _setJsOption(string $sFunctionName, string $sOptionName, $xOptionValue): void
    {
        $this->aJsOptions[$sFunctionName][$sOptionName] = $xOptionValue;
    }

    /**
     * @param string $sFunctionName
     * @param string $sOptionName
     * @param mixed $xOptionValue
     *
     * @return void
     */
    private function addJsOption(string $sFunctionName, string $sOptionName, $xOptionValue): void
    {
        switch($sOptionName)
        {
        case 'excluded':
            if((bool)$xOptionValue)
            {
                $this->addProtectedMethods($sFunctionName);
            }
            break;
        // For databags and callbacks, all the value are merged in a single array.
        case 'bags':
        case 'callback':
            $this->_addJsArrayOption($sFunctionName, $sOptionName, $xOptionValue);
            return;
        // For all the other options, only the last value is kept.
        default:
            $this->_setJsOption($sFunctionName, $sOptionName, $xOptionValue);
        }
    }

    /**
     * @param string $sFunctionName
     * @param array $aFunctionOptions
     *
     * @return void
     */
    private function addFunctionOptions(string $sFunctionName, array $aFunctionOptions): void
    {
        foreach($aFunctionOptions as $sOptionName => $xOptionValue)
        {
            substr($sOptionName, 0, 2) === '__' ?
                // Options for PHP classes. They start with "__".
                $this->addOption($sOptionName, [$sFunctionName => $xOptionValue]) :
                // Options for javascript code.
                $this->addJsOption($sFunctionName, $sOptionName, $xOptionValue);
        }
    }

    /**
     * @param string $sMethodName
     *
     * @return array
     */
    private function getMethodOptions(string $sMethodName): array
    {
        // First take the common options.
        $aOptions = array_merge($this->aJsOptions['*'] ?? []); // Clone the array
        // Then add the method options.
        $aMethodOptions = $this->aJsOptions[$sMethodName] ?? [];
        foreach($aMethodOptions as $sOptionName => $xOptionValue)
        {
            // For databags and callbacks, merge the values in a single array.
            // For all the other options, keep the last value.
            $aOptions[$sOptionName] = !in_array($sOptionName, ['bags', 'callback']) ?
                $xOptionValue :
                array_unique(array_merge($aOptions[$sOptionName] ?? [], $xOptionValue));
        }
        // Since callbacks are js object names, they need a special formatting.
        if(isset($aOptions['callback']))
        {
            $aOptions['callback'] = str_replace('"', '', json_encode($aOptions['callback']));
        }

        return array_map(fn($xOption) =>
            is_array($xOption) ? json_encode($xOption) : $xOption, $aOptions);
    }

    /**
     * Return a list of methods of the component to export to javascript
     *
     * @return array
     */
    public function getCallableMethods(): array
    {
        // Get the method options, and convert each of them to
        // a string to be displayed in the js script template.
        return array_map(fn($sMethodName) => [
            'name' => $sMethodName,
            'options' => $this->getMethodOptions($sMethodName),
        ], $this->aPublicMethods);
    }
}
