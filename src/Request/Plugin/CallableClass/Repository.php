<?php

/**
 * Repository.php - Jaxon callable object repository
 *
 * This class stores all the callable object already created.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2019 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Plugin\CallableClass;

use Jaxon\Container\Container;
use Jaxon\Exception\SetupException;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use function array_merge;
use function str_replace;
use function strlen;
use function strncmp;
use function substr;
use function trim;

class Repository
{
    /**
     * The DI container
     *
     * @var Container
     */
    protected $di;

    /**
     * The classes
     *
     * These are all the registered classes.
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
     * Indicate if the registered directories are already parsed
     *
     * @var bool
     */
    protected $bParsedDirectories = false;

    /**
     * Indicate if the registered namespaces are already parsed
     *
     * @var bool
     */
    protected $bParsedNamespaces = false;

    /**
     * The constructor
     *
     * @param Container  $di
     */
    public function __construct(Container $di)
    {
        $this->di = $di;
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
        if(!isset($aClassOptions['functions']))
        {
            $aClassOptions['functions'] = [];
        }
        foreach(['separator', 'protected'] as $sName)
        {
            if(isset($aDirectoryOptions[$sName]))
            {
                $aClassOptions[$sName] = $aDirectoryOptions[$sName];
            }
        }

        $aFunctionOptions = $aDirectoryOptions['classes'] ?? [];
        foreach($aFunctionOptions as $sName => $xValue)
        {
            if($sName === '*' || strncmp($sClassName, $sName, strlen($sName)) === 0)
            {
                $aClassOptions['functions'] = array_merge($aClassOptions['functions'], $xValue);
            }
        }

        // This value will be used to compute hash
        if(!isset($aClassOptions['timestamp']))
        {
            $aClassOptions['timestamp'] = 0;
        }

        return $aClassOptions;
    }

    /**
     * Get all registered classes
     *
     * @return array
     */
    public function getClasses(): array
    {
        return $this->aClasses;
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
     * Get the names of all registered classess
     *
     * @return array
     */
    public function getClassNames(): array
    {
        return array_keys($this->aClasses);
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
    }

    /**
     *
     * @param string $sNamespace    The namespace
     * @param array|string $aOptions    The associated options
     *
     * @return void
     */
    public function addNamespace(string $sNamespace, $aOptions)
    {
        $this->aNamespaces[$sNamespace] = $aOptions;
    }

    /**
     * Find the options associated with a registered class name
     *
     * @param string $sClassName    The class name
     *
     * @return array|null
     */
    public function getClassOptions(string $sClassName): ?array
    {
        if(!isset($this->aClasses[$sClassName]))
        {
            // Class not found
            return null;
        }
        return $this->aClasses[$sClassName];
    }

    /**
     * Read classes from directories registered without namespaces
     *
     * @param array $aDirectories    The directories
     *
     * @return void
     */
    public function parseDirectories(array $aDirectories)
    {
        // Browse directories without namespaces and read all the files.
        // This is to be done only once.
        if($this->bParsedDirectories)
        {
            return;
        }
        $this->bParsedDirectories = true;

        // Browse directories without namespaces and read all the files.
        foreach($aDirectories as $sDirectory => $aOptions)
        {
            $itFile = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sDirectory));
            // Iterate on dir content
            foreach($itFile as $xFile)
            {
                // skip everything except PHP files
                if(!$xFile->isFile() || $xFile->getExtension() != 'php')
                {
                    continue;
                }

                $sClassName = $xFile->getBasename('.php');
                $aClassOptions = ['timestamp' => $xFile->getMTime()];
                // No more classmap autoloading. The file will be included when needed.
                if(($aOptions['autoload']))
                {
                    $aClassOptions['include'] = $xFile->getPathname();
                }
                $this->addClass($sClassName, $aClassOptions, $aOptions);
            }
        }
    }

    /**
     * Read classes from directories registered with namespaces
     *
     * @param array $aNamespaces    The namespaces
     *
     * @return void
     */
    public function parseNamespaces(array $aNamespaces)
    {
        // This is to be done only once.
        if($this->bParsedNamespaces)
        {
            return;
        }
        $this->bParsedNamespaces = true;

        // Browse directories with namespaces and read all the files.
        $sDS = DIRECTORY_SEPARATOR;
        foreach($aNamespaces as $sNamespace => $aOptions)
        {
            $this->addNamespace($sNamespace, ['separator' => $aOptions['separator']]);

            // Iterate on dir content
            $sDirectory = $aOptions['directory'];
            $itFile = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sDirectory));
            foreach($itFile as $xFile)
            {
                // skip everything except PHP files
                if(!$xFile->isFile() || $xFile->getExtension() != 'php')
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

                $this->addNamespace($sClassPath, ['separator' => $aOptions['separator']]);

                $sClassName = $sClassPath . '\\' . $xFile->getBasename('.php');
                $aClassOptions = ['namespace' => $sNamespace, 'timestamp' => $xFile->getMTime()];
                $this->addClass($sClassName, $aClassOptions, $aOptions);
            }
        }
    }

    /**
     * Register a callable class
     *
     * @param string $sClassName The class name of the callable object
     * @param array $aOptions The callable object options
     *
     * @return void
     * @throws SetupException
     */
    public function registerCallableClass(string $sClassName, array $aOptions)
    {
        // Make sure the registered class exists
        if(isset($aOptions['include']))
        {
            require_once($aOptions['include']);
        }
        // Register the callable object
        $this->di->registerCallableClass($sClassName, $aOptions);
    }
}
