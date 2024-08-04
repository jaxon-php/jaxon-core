<?php

/**
 * CallableRegistry.php - Jaxon callable class registry
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
use Jaxon\App\AbstractCallable;
use Jaxon\App\Component;
use Jaxon\Di\ClassContainer;
use Jaxon\Utils\Config\Config;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
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

class CallableRegistry
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
     * @param ClassContainer $cls
     */
    public function __construct(protected ClassContainer $cls)
    {
        // Set the composer autoloader
        if(file_exists(($sAutoloadFile = __DIR__ . '/../../../../../../autoload.php')) ||
            file_exists(($sAutoloadFile = __DIR__ . '/../../../../vendor/autoload.php')))
        {
            $this->xAutoloader = require($sAutoloadFile);
        }

        // The methods of the AbstractCallable class must not be exported
        $xAbstractCallable = new ReflectionClass(AbstractCallable::class);
        foreach($xAbstractCallable->getMethods(ReflectionMethod::IS_PUBLIC) as $xMethod)
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
     * @param string $sClassName    The class name
     * @param array $aClassOptions    The default class options
     * @param array $aDirectoryOptions    The directory options
     *
     * @return void
     */
    private function _addClass(string $sClassName, array $aClassOptions, array $aDirectoryOptions = [])
    {
        $aOptions = $this->makeClassOptions($sClassName, $aClassOptions, $aDirectoryOptions);
        $this->sHash .= $sClassName . $aOptions['timestamp'];
        $this->cls->addClass($sClassName, $aOptions);
    }

    /**
     *
     * @param string $sClassName    The class name
     * @param array $aClassOptions    The default class options
     *
     * @return void
     */
    public function addClass(string $sClassName, array $aClassOptions)
    {
        if($this->xCurrentConfig !== null)
        {
            $aClassOptions['config'] = $this->xCurrentConfig;
        }
        $this->_addClass($sClassName, $aClassOptions);
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
        foreach($this->aNamespaceOptions as $sNamespace => $aDirectoryOptions)
        {
            // Check if the namespace matches the class.
            if(strncmp($sClassName, $sNamespace . '\\', strlen($sNamespace) + 1) === 0)
            {
                // Save the class options
                $aClassOptions = ['namespace' => $sNamespace];
                $aOptions = $this->makeClassOptions($sClassName, $aClassOptions, $aDirectoryOptions);
                $this->cls->addClass($sClassName, $aOptions);
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
            (is_subclass_of($sClassName, AbstractCallable::class) ?
                $this->aProtectedMethods : []);
    }

    /**
     *
     * @param string $sDirectory    The directory being registered
     * @param array $aOptions    The associated options
     *
     * @return void
     */
    public function addDirectory(string $sDirectory, array $aOptions)
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
    private function _addNamespace(string $sNamespace, array $aOptions)
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
    public function addNamespace(string $sNamespace, array $aOptions)
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
            $this->cls->useUnderscore();
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
            $itFile = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sDirectory));
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
                // No more custom classmap autoloading. The file will be included when needed.
                if(($aDirectoryOptions['autoload']))
                {
                    $aClassMap[$sClassName] = $xFile->getPathname();
                }
                $this->_addClass($sClassName, $aClassOptions, $aDirectoryOptions);
            }
        }
        // Set classmap autoloading
        if(($aClassMap) && $this->xAutoloader !== null)
        {
            $this->xAutoloader->addClassMap($aClassMap);
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
            $this->_addNamespace($sNamespace, ['separator' => $aDirectoryOptions['separator']]);

            // Iterate on dir content
            $sDirectory = $aDirectoryOptions['directory'];
            $itFile = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sDirectory));
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

                $this->_addNamespace($sClassPath, ['separator' => $aDirectoryOptions['separator']]);

                $sClassName = $sClassPath . '\\' . $xFile->getBasename('.php');
                $aClassOptions = ['namespace' => $sNamespace, 'timestamp' => $xFile->getMTime()];
                $this->_addClass($sClassName, $aClassOptions, $aDirectoryOptions);
            }
        }
    }

    /**
     * Register all the callable classes
     *
     * @return void
     */
    public function parseCallableClasses()
    {
        $this->parseDirectories();
        $this->parseNamespaces();
    }
}
