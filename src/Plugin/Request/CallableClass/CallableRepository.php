<?php

/**
 * CallableRepository.php - Jaxon callable object repository
 *
 * This class stores all the callable object already created.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2019 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin\Request\CallableClass;

use Jaxon\App\CallableClass;
use Jaxon\App\I18n\Translator;
use Jaxon\Di\Container;
use Jaxon\Exception\SetupException;

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
     * The DI container
     *
     * @var Container
     */
    protected $di;

    /**
     * @var Translator
     */
    protected $xTranslator;

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
     * The classes
     *
     * These are all the classes, both registered and found in registered directories.
     *
     * @var array
     */
    protected $aClasses = [];

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
    private $aDefaultClassOptions = ['separator' => '.', 'protected' => [], 'functions' => [], 'timestamp' => 0];

    /**
     * The methods that must not be exported to js
     *
     * @var array
     */
    private $aProtectedMethods = [];

    /**
     * The constructor
     *
     * @param Container $di
     * @param Translator $xTranslator
     */
    public function __construct(Container $di, Translator $xTranslator)
    {
        $this->di = $di;
        $this->xTranslator = $xTranslator;

        // The methods of the CallableClass class must not be exported
        $xCallableClass = new ReflectionClass(CallableClass::class);
        foreach($xCallableClass->getMethods(ReflectionMethod::IS_PUBLIC) as $xMethod)
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
                $aClassOptions['separator'] = $aOptionGroup['separator'];
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
        $this->aClasses[$sClassName] = $this->makeClassOptions($sClassName, $aClassOptions, $aDirectoryOptions);
        $this->sHash .= $sClassName . $this->aClasses[$sClassName]['timestamp'];
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
    private function getNamespaceClassOptions(string $sClassName)
    {
        // Find the corresponding namespace
        foreach($this->aNamespaceOptions as $sNamespace => $aOptions)
        {
            // Check if the namespace matches the class.
            if(strncmp($sClassName, $sNamespace . '\\', strlen($sNamespace) + 1) === 0)
            {
                // Save the class options
                $this->aClasses[$sClassName] = $this->makeClassOptions($sClassName,
                    ['namespace' => $sNamespace], $aOptions);
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
     * @throws SetupException
     */
    public function getClassOptions(string $sClassName): array
    {
        // Find options for a class registered with namespace.
        if(!isset($this->aClasses[$sClassName]))
        {
            $this->getNamespaceClassOptions($sClassName);
            if(!isset($this->aClasses[$sClassName]))
            {
                // Find options for a class registered without namespace.
                // We then need to parse all classes to be able to find one.
                $this->di->getCallableRegistry()->parseDirectories();
            }
        }
        if(isset($this->aClasses[$sClassName]))
        {
            return $this->aClasses[$sClassName];
        }
        $sMessage = $this->xTranslator->trans('errors.class.invalid', ['name' => $sClassName]);
        throw new SetupException($sMessage);
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
        return is_subclass_of($sClassName, CallableClass::class) ? $this->aProtectedMethods : [];
    }

    /**
     * Get callable objects for known classes
     *
     * @return array
     * @throws SetupException
     */
    public function getCallableObjects(): array
    {
        $aCallableObjects = [];
        foreach($this->aClasses as $sClassName => $aOptions)
        {
            if(!$this->di->h($sClassName))
            {
                $this->di->registerCallableClass($sClassName, $aOptions);
            }
            $aCallableObjects[$sClassName] = $this->di->getCallableObject($sClassName);
        }
        return $aCallableObjects;
    }
}
