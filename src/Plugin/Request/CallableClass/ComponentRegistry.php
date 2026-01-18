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
use Jaxon\Config\Config;
use Jaxon\Di\ComponentContainer;

use function array_merge;
use function dirname;
use function file_exists;
use function is_string;
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
     * A config from the package providing the class or directory being registered.
     *
     * @var Config|null
     */
    protected $xPackageConfig = null;

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
     * @var bool
     */
    private $bUpdateHash = true;

    /**
     * The class constructor
     *
     * @param ComponentContainer $cdi
     */
    public function __construct(protected ComponentContainer $cdi)
    {
        // Set the composer autoloader
        if(file_exists(($sAutoloadFile = dirname(__DIR__, 6) . '/autoload.php')) ||
            file_exists(($sAutoloadFile = dirname(__DIR__, 5) . '/vendor/autoload.php')) ||
            file_exists(($sAutoloadFile = dirname(__DIR__, 4) . '/vendor/autoload.php')))
        {
            $this->xAutoloader = require $sAutoloadFile;
        }
    }

    /**
     * @param Config $xConfig
     *
     * @return void
     */
    public function setPackageConfig(Config $xConfig): void
    {
        $this->xPackageConfig = $xConfig;
    }

    /**
     * @return void
     */
    public function unsetPackageConfig(): void
    {
        $this->xPackageConfig = null;
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
     * Enable or disable hash calculation
     *
     * @param bool $bUpdateHash
     *
     * @return void
     */
    public function updateHash(bool $bUpdateHash): void
    {
        $this->bUpdateHash = $bUpdateHash;
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
            if(isset($aOptionGroup['export']))
            {
                $aClassOptions['export'] = (array)$aOptionGroup['export'];
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
     * Register a component
     *
     * @param string $sClassName        The class name
     * @param array $aClassOptions      The default class options
     * @param array $aDirectoryOptions  The directory options
     *
     * @return void
     */
    private function _registerComponent(string $sClassName, array $aClassOptions,
        array $aDirectoryOptions = []): void
    {
        $aOptions = $this->makeClassOptions($sClassName, $aClassOptions, $aDirectoryOptions);
        $this->cdi->saveComponent($sClassName, $aOptions);
        if($this->bUpdateHash)
        {
            $this->sHash .= $sClassName . $aOptions['timestamp'];
        }
    }

    /**
     * Register a component
     *
     * @param string $sClassName    The class name
     * @param array $aClassOptions    The default class options
     *
     * @return void
     */
    public function registerComponent(string $sClassName, array $aClassOptions): void
    {
        // For classes, the underscore is used as separator.
        $aClassOptions['separator'] = '_';
        if($this->xPackageConfig !== null)
        {
            $aClassOptions['config'] = $this->xPackageConfig;
        }
        $this->_registerComponent($sClassName, $aClassOptions);
    }

    /**
     * Get the options of a component in a registered namespace
     *
     * @param string $sClassName    The class name
     *
     * @return array|null
     */
    public function getNamespaceComponentOptions(string $sClassName): ?array
    {
        // Find the corresponding namespace
        foreach($this->aNamespaceOptions as $sNamespace => $aDirectoryOptions)
        {
            // Check if the namespace matches the class.
            if(strncmp($sClassName, $sNamespace . '\\', strlen($sNamespace) + 1) === 0)
            {
                // Save the class options
                $aClassOptions = ['namespace' => $sNamespace];
                return $this->makeClassOptions($sClassName, $aClassOptions, $aDirectoryOptions);
            }
        }
        return null;
    }

    /**
     * Register a directory
     *
     * @param string $sDirectory    The directory being registered
     * @param array $aOptions    The associated options
     *
     * @return void
     */
    public function registerDirectory(string $sDirectory, array $aOptions): void
    {
        // For directories without namespace, the underscore is used as separator.
        $aOptions['separator'] = '_';
        // Set the autoload option default value
        if(!isset($aOptions['autoload']))
        {
            $aOptions['autoload'] = true;
        }
        if($this->xPackageConfig !== null)
        {
            $aOptions['config'] = $this->xPackageConfig;
        }
        $this->aDirectoryOptions[$sDirectory] = $aOptions;
    }

    /**
     * Add a namespace
     *
     * @param string $sNamespace    The namespace
     * @param array $aOptions    The associated options
     *
     * @return void
     */
    private function addNamespace(string $sNamespace, array $aOptions): void
    {
        $this->aNamespaces[] = $sNamespace;
        $this->sHash .= $sNamespace . $aOptions['separator'];
    }

    /**
     * Register a namespace
     *
     * @param string $sNamespace    The namespace of the directory being registered
     * @param array $aOptions    The associated options
     *
     * @return void
     */
    public function registerNamespace(string $sNamespace, array $aOptions): void
    {
        // For namespaces, the dot is used as separator.
        $aOptions['separator'] = '.';
        // Set the autoload option default value
        if(!isset($aOptions['autoload']))
        {
            $aOptions['autoload'] = true;
        }
        if($this->xPackageConfig !== null)
        {
            $aOptions['config'] = $this->xPackageConfig;
        }
        // Register the dir with PSR4 autoloading
        if(($aOptions['autoload']) && $this->xAutoloader != null)
        {
            $this->xAutoloader->setPsr4($sNamespace . '\\', $aOptions['directory']);
        }

        $this->aNamespaceOptions[$sNamespace] = $aOptions;
    }

    /**
     * Read classes from directories registered with namespaces
     *
     * @return void
     */
    public function registerComponentsInNamespaces(): void
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
            $this->addNamespace($sNamespace, ['separator' => '.']);

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

                $this->addNamespace($sClassPath, ['separator' => '.']);

                $sClassName = $sClassPath . '\\' . $xFile->getBasename('.php');
                $aClassOptions = [
                    'separator' => '.',
                    'namespace' => $sNamespace,
                    'timestamp' => $xFile->getMTime(),
                ];
                $this->_registerComponent($sClassName, $aClassOptions, $aDirectoryOptions);
            }
        }
    }

    /**
     * Read classes from directories registered without namespaces
     *
     * @return void
     */
    public function registerComponentsInDirectories(): void
    {
        // This is to be done only once.
        if($this->bDirectoriesParsed)
        {
            return;
        }
        $this->bDirectoriesParsed = true;

        // Browse directories without namespaces and read all the files.
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
                $aClassOptions = [
                    'separator' => '.',
                    'timestamp' => $xFile->getMTime(),
                ];
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
     * Register all the components
     *
     * @return void
     */
    public function registerAllComponents(): void
    {
        $this->registerComponentsInNamespaces();
        $this->registerComponentsInDirectories();
    }
}
