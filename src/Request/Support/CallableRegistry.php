<?php

/**
 * CallableRegistry.php - Jaxon callable object registrar
 *
 * This class is the entry point for class, directory and namespace registration.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2019 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Support;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class CallableRegistry
{
    /**
     * The callable repository
     *
     * @var CallableRepository
     */
    public $xRepository;

    /**
     * The registered classes
     *
     * These are registered classes, and classes in directories registered without a namespace.
     *
     * @var array
     */
    protected $aClasses = [];

    /**
     * The registered directories
     *
     * These are directories registered without a namespace.
     *
     * @var array
     */
    protected $aDirectories = [];

    /**
     * Indicate if the registered directories are already parsed
     *
     * @var array
     */
    protected $bParsedDirectories = false;

    /**
     * The registered namespaces
     *
     * These are the namespaces specified when registering directories.
     *
     * @var array
     */
    protected $aNamespaces = [];

    /**
     * Indicate if the registered namespaces are already parsed
     *
     * @var array
     */
    protected $bParsedNamespaces = false;

    /**
     * If the underscore is used as separator in js class names.
     *
     * @var boolean
     */
    public $bUsingUnderscore = false;

    /**
     * The Composer autoloader
     *
     * @var Autoloader
     */
    private $xAutoloader = null;

    /**
     * The class constructor
     *
     * @param CallableRepository        $xRepository
     */
    public function __construct(CallableRepository $xRepository)
    {
        $this->xRepository = $xRepository;

        // Set the composer autoloader
        $sAutoloadFile = __DIR__ . '/../../../../../autoload.php';
        if(file_exists($sAutoloadFile))
        {
            $this->xAutoloader = require($sAutoloadFile);
        }
    }

    /**
     *
     * @param string        $sClassName     The name of the class being registered
     * @param array|string  $aOptions       The associated options
     *
     * @return void
     */
    public function addClass($sClassName, $aOptions)
    {
        $sClassName = trim($sClassName, '\\ ');
        $this->aClasses[$sClassName] = $aOptions;
    }

    /**
     *
     * @param string        $sDirectory     The directory being registered
     * @param array         $aOptions       The associated options
     *
     * @return void
     */
    public function addDirectory($sDirectory, array $aOptions)
    {
        // Set the autoload option default value
        if(!key_exists('autoload', $aOptions))
        {
            $aOptions['autoload'] = true;
        }

        $this->aDirectories[$sDirectory] = $aOptions;
    }

    /**
     *
     * @param string        $sNamespace     The namespace of the directory being registered
     * @param array         $aOptions       The associated options
     *
     * @return void
     */
    public function addNamespace($sNamespace, array $aOptions)
    {
        // Separator default value
        if(!key_exists('separator', $aOptions))
        {
            $aOptions['separator'] = '.';
        }
        $aOptions['separator'] = trim($aOptions['separator']);
        if(!in_array($aOptions['separator'], ['.', '_']))
        {
            $aOptions['separator'] = '.';
        }
        if($aOptions['separator'] == '_')
        {
            $this->bUsingUnderscore = true;
        }
        // Set the autoload option default value
        if(!key_exists('autoload', $aOptions))
        {
            $aOptions['autoload'] = true;
        }
        // Register the dir with PSR4 autoloading
        if(($aOptions['autoload']) && $this->xAutoloader != null)
        {
            $this->xAutoloader->setPsr4($sNamespace . '\\', $aOptions['directory']);
        }

        $this->aNamespaces[$sNamespace] = $aOptions;
    }

    /**
     * Get a given class options from specified directory options
     *
     * @param string        $sClassName         The name of the class
     * @param array         $aDirectoryOptions  The directory options
     * @param array         $aDefaultOptions    The default options
     *
     * @return array
     */
    private function _makeClassOptions($sClassName, array $aDirectoryOptions, array $aDefaultOptions = [])
    {
        $aOptions = $aDefaultOptions;
        if(key_exists('separator', $aDirectoryOptions))
        {
            $aOptions['separator'] = $aDirectoryOptions['separator'];
        }
        if(key_exists('protected', $aDirectoryOptions))
        {
            $aOptions['protected'] = $aDirectoryOptions['protected'];
        }
        if(key_exists('*', $aDirectoryOptions))
        {
            $aOptions = array_merge($aOptions, $aDirectoryOptions['*']);
        }
        if(key_exists($sClassName, $aDirectoryOptions))
        {
            $aOptions = array_merge($aOptions, $aDirectoryOptions[$sClassName]);
        }

        return $aOptions;
    }

    /**
     * Read classes from registered directories (without namespaces)
     *
     * @return void
     */
    public function parseDirectories()
    {
        // Browse directories without namespaces and read all the files.
        // This is to be done only once.
        if($this->bParsedDirectories)
        {
            return;
        }
        $this->bParsedDirectories = true;

        foreach($this->aClasses as $sClassName => $aClassOptions)
        {
            $this->xRepository->addClass($sClassName, $aClassOptions);
        }

        foreach($this->aDirectories as $sDirectory => $aOptions)
        {
            $itDir = new RecursiveDirectoryIterator($sDirectory);
            $itFile = new RecursiveIteratorIterator($itDir);
            // Iterate on dir content
            foreach($itFile as $xFile)
            {
                // skip everything except PHP files
                if(!$xFile->isFile() || $xFile->getExtension() != 'php')
                {
                    continue;
                }

                $sClassName = $xFile->getBasename('.php');
                $aClassOptions = [];
                // No more classmap autoloading. The file will be included when needed.
                if(($aOptions['autoload']))
                {
                    $aClassOptions['include'] = $xFile->getPathname();
                }
                $aClassOptions = $this->_makeClassOptions($sClassName, $aOptions, $aClassOptions);
                $this->xRepository->addClass($sClassName, $aClassOptions);
            }
        }
    }

    /**
     * Read classes from registered directories (with namespaces)
     *
     * @return void
     */
    public function parseNamespaces()
    {
        // Browse directories with namespaces and read all the files.
        // This is to be done only once.
        if($this->bParsedNamespaces)
        {
            return;
        }
        $this->bParsedNamespaces = true;

        $sDS = DIRECTORY_SEPARATOR;
        foreach($this->aNamespaces as $sNamespace => $aOptions)
        {
            $this->xRepository->addNamespace($sNamespace, ['separator' => $aOptions['separator']]);

            // Iterate on dir content
            $sDirectory = $aOptions['directory'];
            $itDir = new RecursiveDirectoryIterator($sDirectory);
            $itFile = new RecursiveIteratorIterator($itDir);
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
                if($sRelativePath != '')
                {
                    $sClassPath .= '\\' . $sRelativePath;
                }

                $this->xRepository->addNamespace($sClassPath, ['separator' => $aOptions['separator']]);

                $sClassName = $sClassPath . '\\' . $xFile->getBasename('.php');
                $aClassOptions = ['namespace' => $sNamespace];
                $aClassOptions = $this->_makeClassOptions($sClassName, $aOptions, $aClassOptions);
                $this->xRepository->addClass($sClassName, $aClassOptions);
            }
        }
    }

    /**
     * Find options for a class which is registered with namespace
     *
     * @param string        $sClassName            The class name
     *
     * @return array|null
     */
    public function getClassOptions($sClassName)
    {
        // Find the corresponding namespace
        $sNamespace = null;
        foreach(array_keys($this->aNamespaces) as $_sNamespace)
        {
            if(substr($sClassName, 0, strlen($_sNamespace) + 1) == $_sNamespace . '\\')
            {
                $sNamespace = $_sNamespace;
                break;
            }
        }
        if($sNamespace === null)
        {
            return null; // Class not registered
        }

        // Get the class options
        $aOptions = $this->aNamespaces[$sNamespace];
        $aDefaultOptions = ['namespace' => $sNamespace];
        return $this->_makeClassOptions($sClassName, $aOptions, $aDefaultOptions);
    }
}
