<?php

/**
 * CallableRepository.php
 *
 * This class stores all the callable objects already created.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2019 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin\Request\CallableClass;

use Jaxon\App\AbstractCallable;
use Jaxon\App\Component;
use Jaxon\Di\ClassContainer;
use ReflectionClass;
use ReflectionMethod;

use function array_merge;
use function is_string;
use function is_subclass_of;
use function strlen;
use function strncmp;

class CallableRepository
{
    /**
     * The namespace options
     *
     * These are the options of the registered namespaces.
     *
     * @var array
     */
    protected $aNamespaceOptions = [];

    /**
     * The directory options
     *
     * These are the options of the registered directories.
     *
     * @var array
     */
    protected $aDirectoryOptions = [];

    /**
     * The namespaces
     *
     * These are all the namespaces found in registered directories.
     *
     * @var array
     */
    protected $aNamespaces = [];

    /**
     * The string that will be used to compute the js file hash
     *
     * @var string
     */
    protected $sHash = '';

    /**
     * @var array
     */
    private $aDefaultClassOptions = [
        'separator' => '.',
        'protected' => [],
        'functions' => [],
        'timestamp' => 0,
    ];

    /**
     * The methods that must not be exported to js
     *
     * @var array
     */
    private $aProtectedMethods = [];

    /**
     * The constructor
     *
     * @param ClassContainer $cls
     */
    public function __construct(protected ClassContainer $cls)
    {
        // The methods of the AbstractCallable class must not be exported
        $xAbstractCallable = new ReflectionClass(AbstractCallable::class);
        foreach($xAbstractCallable->getMethods(ReflectionMethod::IS_PUBLIC) as $xMethod)
        {
            $this->aProtectedMethods[] = $xMethod->getName();
        }
    }

    /**
     * @return array
     */
    public function getDirectoryOptions(): array
    {
        return $this->aDirectoryOptions;
    }

    /**
     * @param string $sDirectory
     * @param array $aOptions
     */
    public function setDirectoryOptions(string $sDirectory, array $aOptions): void
    {
        $this->aDirectoryOptions[$sDirectory] = $aOptions;
    }

    /**
     * @return array
     */
    public function getNamespaceOptions(): array
    {
        return $this->aNamespaceOptions;
    }

    /**
     * @param string $sNamespace
     * @param array $aOptions
     */
    public function setNamespaceOptions(string $sNamespace, array $aOptions): void
    {
        $this->aNamespaceOptions[$sNamespace] = $aOptions;
    }

    /**
     * Get all registered namespaces
     *
     * @return array
     */
    public function getNamespaces(): array
    {
        return $this->aNamespaces;
    }

    /**
     * Get the hash
     *
     * @return string
     */
    public function getHash(): string
    {
        return $this->sHash;
    }

    /**
     * Get a given class options from specified directory options
     *
     * @param string $sClassName    The class name
     * @param array $aClassOptions    The default class options
     * @param array $aDirectoryOptions    The directory options
     *
     * @return array
     */
    public function makeClassOptions(string $sClassName, array $aClassOptions, array $aDirectoryOptions): array
    {
        foreach($this->aDefaultClassOptions as $sOption => $xValue)
        {
            if(!isset($aClassOptions[$sOption]))
            {
                $aClassOptions[$sOption] = $xValue;
            }
        }
        $aClassOptions['excluded'] = (bool)($aClassOptions['excluded'] ?? false); // Convert to bool.
        if(is_string($aClassOptions['protected']))
        {
            $aClassOptions['protected'] = [$aClassOptions['protected']]; // Convert to array.
        }

        $aDirectoryOptions['functions'] = []; // The 'functions' section is not allowed here.
        $aOptionGroups = [
            $aDirectoryOptions, // Options at directory level
            $aDirectoryOptions['classes']['*'] ?? [], // Options for all classes
            $aDirectoryOptions['classes'][$sClassName] ?? [], // Options for this specific class
        ];
        foreach($aOptionGroups as $aOptionGroup)
        {
            if(isset($aOptionGroup['separator']))
            {
                $aClassOptions['separator'] = (string)$aOptionGroup['separator'];
            }
            if(isset($aOptionGroup['excluded']))
            {
                $aClassOptions['excluded'] = (bool)$aOptionGroup['excluded'];
            }
            if(isset($aOptionGroup['protected']))
            {
                if(is_string($aOptionGroup['protected']))
                {
                    $aOptionGroup['protected'] = [$aOptionGroup['protected']]; // Convert to array.
                }
                $aClassOptions['protected'] = array_merge($aClassOptions['protected'], $aOptionGroup['protected']);
            }
            if(isset($aOptionGroup['functions']))
            {
                $aClassOptions['functions'] = array_merge($aClassOptions['functions'], $aOptionGroup['functions']);
            }
        }

        return $aClassOptions;
    }

    /**
     *
     * @param string $sClassName    The class name
     * @param array $aClassOptions    The default class options
     * @param array $aDirectoryOptions    The directory options
     *
     * @return void
     */
    public function addClass(string $sClassName, array $aClassOptions, array $aDirectoryOptions = [])
    {
        $aOptions = $this->makeClassOptions($sClassName, $aClassOptions, $aDirectoryOptions);
        $this->sHash .= $sClassName . $aOptions['timestamp'];
        $this->cls->addClass($sClassName, $aOptions);
    }

    /**
     *
     * @param string $sNamespace    The namespace
     * @param array $aOptions    The associated options
     *
     * @return void
     */
    public function addNamespace(string $sNamespace, array $aOptions)
    {
        $this->aNamespaces[] = $sNamespace;
        $this->sHash .= $sNamespace . $aOptions['separator'];
    }

    /**
     * Find options for a class which is registered with namespace
     *
     * @param string $sClassName    The class name
     *
     * @return void
     */
    public function setNamespaceClassOptions(string $sClassName)
    {
        // Find the corresponding namespace
        foreach($this->aNamespaceOptions as $sNamespace => $aOptions)
        {
            // Check if the namespace matches the class.
            if(strncmp($sClassName, $sNamespace . '\\', strlen($sNamespace) + 1) === 0)
            {
                // Save the class options
                $this->cls->addClass($sClassName, $this->makeClassOptions($sClassName,
                    ['namespace' => $sNamespace], $aOptions));
                return;
            }
        }
    }

    /**
     * Find the options associated with a registered class name
     *
     * @param string $sClassName The class name
     *
     * @return array
     */
    public function getProtectedMethods(string $sClassName): array
    {
        // Don't export the html() public method for Component objects
        return is_subclass_of($sClassName, Component::class) ?
            [...$this->aProtectedMethods, 'html'] :
            (is_subclass_of($sClassName, AbstractCallable::class) ?
                $this->aProtectedMethods : []);
    }
}
