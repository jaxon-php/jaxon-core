<?php

/**
 * ComponentRegistry.php - Jaxon component registry
 *
 * This class is the entry point for class, directory and namespace registration.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2019 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin\Request\CallableClass;

use Composer\Autoload\ClassLoader;
use Jaxon\App\AbstractComponent;
use Jaxon\App\Component;
use Jaxon\Config\Config;
use Jaxon\Di\ComponentContainer;
use ReflectionClass;
use ReflectionMethod;

use function array_merge;
use function file_exists;
use function in_array;
use function is_string;
use function is_subclass_of;
use function str_replace;
use function strlen;
use function strncmp;
use function substr;
use function trim;

class ComponentRegistry
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
     * The package providing the class or directory being registered.
     *
     * @var Config|null
     */
    protected $xCurrentConfig = null;

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
     * @var bool
     */
    protected $bDirectoriesParsed = false;

    /**
     * @var bool
     */
    protected $bNamespacesParsed = false;

    /**
     * The Composer autoloader
     *
     * @var ClassLoader
     */
    private $xAutoloader = null;

    /**
     * The class constructor
     *
     * @param ComponentContainer $cdi
     */
    public function __construct(protected ComponentContainer $cdi)
    {
        // Set the composer autoloader
        if(file_exists(($sAutoloadFile = __DIR__ . '/../../../../../../autoload.php')) ||
            file_exists(($sAutoloadFile = __DIR__ . '/../../../../../vendor/autoload.php')) ||
            file_exists(($sAutoloadFile = __DIR__ . '/../../../../vendor/autoload.php')))
        {
            $this->xAutoloader = require($sAutoloadFile);
        }

        // The methods of the AbstractComponent class must not be exported
        $xAbstractComponent = new ReflectionClass(AbstractComponent::class);
        foreach($xAbstractComponent->getMethods(ReflectionMethod::IS_PUBLIC) as $xMethod)
        {
            $this->aProtectedMethods[] = $xMethod->getName();
        }
    }

    /**
     * @param Config|null $xConfig
     *
     * @return void
     */
    public function setCurrentConfig(Config $xConfig = null)
    {
        $this->xCurrentConfig = $xConfig;
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
    private function makeClassOptions(string $sClassName, array $aClassOptions, array $aDirectoryOptions): array
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
        if(isset($aDirectoryOptions['config']) && !isset($aClassOptions['config']))
        {
            $aClassOptions['config'] = $aDirectoryOptions['config'];
        }

        return $aClassOptions;
    }

    /**
     *
     * @param string $sClassName        The class name
     * @param array $aClassOptions      The default class options
     * @param array $aDirectoryOptions  The directory options
     * @param bool $bAddToHash          Add the class name to the hash value
     *
     * @return void
     */
    private function _registerComponent(string $sClassName, array $aClassOptions,
        array $aDirectoryOptions = [], bool $bAddToHash = true)
    {
        $aOptions = $this->makeClassOptions($sClassName, $aClassOptions, $aDirectoryOptions);
        $this->cdi->registerComponent($sClassName, $aOptions);
        if($bAddToHash)
        {
            $this->sHash .= $sClassName . $aOptions['timestamp'];
        }
    }

    /**
     *
     * @param string $sClassName    The class name
     * @param array $aClassOptions    The default class options
     *
     * @return void
     */
    public function registerComponent(string $sClassName, array $aClassOptions)
    {
        if($this->xCurrentConfig !== null)
        {
            $aClassOptions['config'] = $this->xCurrentConfig;
        }
        $this->_registerComponent($sClassName, $aClassOptions);
    }

    /**
     * Find options for a class which is registered with namespace
     *
     * @param string $sClassName    The class name
     *
     * @return void
     */
    public function registerClassFromNamespace(string $sClassName)
    {
        // Find the corresponding namespace
        foreach($this->aNamespaceOptions as $sNamespace => $aDirectoryOptions)
        {
            // Check if the namespace matches the class.
            if(strncmp($sClassName, $sNamespace . '\\', strlen($sNamespace) + 1) === 0)
            {
                // Save the class options
                $aClassOptions = ['namespace' => $sNamespace];
                $this->_registerComponent($sClassName, $aClassOptions, $aDirectoryOptions, false);
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
        // Don't export the item() and html() public methods for Component objects.
        return is_subclass_of($sClassName, Component::class) ?
            [...$this->aProtectedMethods, 'item', 'html'] :
            (is_subclass_of($sClassName, AbstractComponent::class) ?
                $this->aProtectedMethods : []);
    }

    /**
     *
     * @param string $sDirectory    The directory being registered
     * @param array $aOptions    The associated options
     *
     * @return void
     */
    public function registerDirectory(string $sDirectory, array $aOptions)
    {
        // Set the autoload option default value
        if(!isset($aOptions['autoload']))
        {
            $aOptions['autoload'] = true;
        }
        if($this->xCurrentConfig !== null)
        {
            $aOptions['config'] = $this->xCurrentConfig;
        }
        $this->aDirectoryOptions[$sDirectory] = $aOptions;
    }

    /**
     *
     * @param string $sNamespace    The namespace
     * @param array $aOptions    The associated options
     *
     * @return void
     */
    private function addNamespace(string $sNamespace, array $aOptions)
    {
        $this->aNamespaces[] = $sNamespace;
        $this->sHash .= $sNamespace . $aOptions['separator'];
    }

    /**
     *
     * @param string $sNamespace    The namespace of the directory being registered
     * @param array $aOptions    The associated options
     *
     * @return void
     */
    public function registerNamespace(string $sNamespace, array $aOptions)
    {
        // Separator default value
        if(!isset($aOptions['separator']))
        {
            $aOptions['separator'] = '.';
        }
        $aOptions['separator'] = trim($aOptions['separator']);
        if(!in_array($aOptions['separator'], ['.', '_']))
        {
            $aOptions['separator'] = '.';
        }
        if($aOptions['separator'] === '_')
        {
            $this->cdi->useUnderscore();
        }
        // Set the autoload option default value
        if(!isset($aOptions['autoload']))
        {
            $aOptions['autoload'] = true;
        }
        if($this->xCurrentConfig !== null)
        {
            $aOptions['config'] = $this->xCurrentConfig;
        }
        // Register the dir with PSR4 autoloading
        if(($aOptions['autoload']) && $this->xAutoloader != null)
        {
            $this->xAutoloader->setPsr4($sNamespace . '\\', $aOptions['directory']);
        }

        $this->aNamespaceOptions[$sNamespace] = $aOptions;
    }

    /**
     * Read classes from directories registered without namespaces
     *
     * @return void
     */
    public function parseDirectories()
    {
        // This is to be done only once.
        if($this->bDirectoriesParsed)
        {
            return;
        }
        $this->bDirectoriesParsed = true;

        // Browse directories without namespaces and read all the files.
        $aClassMap = [];
        foreach($this->aDirectoryOptions as $sDirectory => $aDirectoryOptions)
        {
            $itFile = new SortedFileIterator($sDirectory);
            // Iterate on dir content
            foreach($itFile as $xFile)
            {
                // Skip everything except PHP files
                if(!$xFile->isFile() || $xFile->getExtension() !== 'php')
                {
                    continue;
                }

                $sClassName = $xFile->getBasename('.php');
                $aClassOptions = ['timestamp' => $xFile->getMTime()];
                if(($aDirectoryOptions['autoload']) && $this->xAutoloader !== null)
                {
                    // Set classmap autoloading. Must be done before registering the class.
                    $this->xAutoloader->addClassMap([$sClassName => $xFile->getPathname()]);
                }
                $this->_registerComponent($sClassName, $aClassOptions, $aDirectoryOptions);
            }
        }
    }

    /**
     * Read classes from directories registered with namespaces
     *
     * @return void
     */
    public function parseNamespaces()
    {
        // This is to be done only once.
        if($this->bNamespacesParsed)
        {
            return;
        }
        $this->bNamespacesParsed = true;

        // Browse directories with namespaces and read all the files.
        $sDS = DIRECTORY_SEPARATOR;
        foreach($this->aNamespaceOptions as $sNamespace => $aDirectoryOptions)
        {
            $this->addNamespace($sNamespace, ['separator' => $aDirectoryOptions['separator']]);

            // Iterate on dir content
            $sDirectory = $aDirectoryOptions['directory'];
            $itFile = new SortedFileIterator($sDirectory);
            foreach($itFile as $xFile)
            {
                // skip everything except PHP files
                if(!$xFile->isFile() || $xFile->getExtension() !== 'php')
                {
                    continue;
                }

                // Find the class path (the same as the class namespace)
                $sClassPath = $sNamespace;
                $sRelativePath = substr($xFile->getPath(), strlen($sDirectory));
                $sRelativePath = trim(str_replace($sDS, '\\', $sRelativePath), '\\');
                if($sRelativePath !== '')
                {
                    $sClassPath .= '\\' . $sRelativePath;
                }

                $this->addNamespace($sClassPath, ['separator' => $aDirectoryOptions['separator']]);

                $sClassName = $sClassPath . '\\' . $xFile->getBasename('.php');
                $aClassOptions = ['namespace' => $sNamespace, 'timestamp' => $xFile->getMTime()];
                $this->_registerComponent($sClassName, $aClassOptions, $aDirectoryOptions);
            }
        }
    }

    /**
     * Register all the components
     *
     * @return void
     */
    public function parseComponents()
    {
        $this->parseDirectories();
        $this->parseNamespaces();
    }
}
