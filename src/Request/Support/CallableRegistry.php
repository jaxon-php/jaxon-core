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

class CallableRegistry
{
    /**
     * The callable repository
     *
     * @var CallableRepository
     */
    public $xRepository;

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
     * @var bool
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
     * @var bool
     */
    protected $bParsedNamespaces = false;

    /**
     * If the underscore is used as separator in js class names.
     *
     * @var bool
     */
    protected $bUsingUnderscore = false;

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
     * @param string        $sDirectory     The directory being registered
     * @param array         $aOptions       The associated options
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

        $this->aDirectories[$sDirectory] = $aOptions;
    }

    /**
     *
     * @param string        $sNamespace     The namespace of the directory being registered
     * @param array         $aOptions       The associated options
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
        if($aOptions['separator'] == '_')
        {
            $this->bUsingUnderscore = true;
        }
        // Set the autoload option default value
        if(!isset($aOptions['autoload']))
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
     * Read classes from directories registered without namespaces
     *
     * @return void
     */
    protected function parseDirectories()
    {
        // Browse directories without namespaces and read all the files.
        // This is to be done only once.
        if($this->bParsedDirectories)
        {
            return;
        }
        $this->bParsedDirectories = true;

        $this->xRepository->parseDirectories($this->aDirectories);
    }

    /**
     * Read classes from directories registered with namespaces
     *
     * @return void
     */
    protected function parseNamespaces()
    {
        // This is to be done only once.
        if($this->bParsedNamespaces)
        {
            return;
        }
        $this->bParsedNamespaces = true;

        $this->xRepository->parseNamespaces($this->aNamespaces);
    }

    /**
     * Find options for a class which is registered with namespace
     *
     * @param string        $sClassName            The class name
     *
     * @return array|null
     */
    protected function getClassOptionsFromNamespaces(string $sClassName): ?array
    {
        // Find the corresponding namespace
        $sNamespace = null;
        $aOptions = null;
        foreach($this->aNamespaces as $_sNamespace => $_aOptions)
        {
            // Check if the namespace matches the class.
            if(strncmp($sClassName, $_sNamespace . '\\', strlen($_sNamespace) + 1) === 0)
            {
                $sNamespace = $_sNamespace;
                $aOptions = $_aOptions;
                break;
            }
        }
        if($sNamespace === null)
        {
            return null; // Class not registered
        }

        // Get the class options
        $aClassOptions = ['namespace' => $sNamespace];
        return $this->xRepository->makeClassOptions($sClassName, $aClassOptions, $aOptions);
    }

    /**
     * Find the options associated with a registered class name
     *
     * @param string        $sClassName            The class name
     *
     * @return array|null
     */
    protected function getClassOptions(string $sClassName): ?array
    {
        // Find options for a class registered with namespace.
        $aOptions = $this->getClassOptionsFromNamespaces($sClassName);
        if($aOptions !== null)
        {
            return $aOptions;
        }

        // Without a namespace, we need to parse all classes to be able to find one.
        $this->parseDirectories();

        // Find options for a class registered without namespace.
        return $this->xRepository->getClassOptions($sClassName);
    }

    /**
     * Find a callable object by class name
     *
     * @param string        $sClassName            The class name of the callable object
     *
     * @return CallableObject|null
     */
    public function getCallableObject(string $sClassName): ?CallableObject
    {
        // Replace all separators ('.' and '_') with antislashes, and remove the antislashes
        // at the beginning and the end of the class name.
        $sClassName = (string)$sClassName;
        $sClassName = trim(str_replace('.', '\\', $sClassName), '\\');
        if($this->bUsingUnderscore)
        {
            $sClassName = trim(str_replace('_', '\\', $sClassName), '\\');
        }

        // Check if the callable object was already created.
        if(!$this->xRepository->hasCallableObject($sClassName))
        {
            $aOptions = $this->getClassOptions($sClassName);
            if($aOptions === null)
            {
                return null;
            }
            $this->xRepository->registerCallableObject($sClassName, $aOptions);
        }

        return $this->xRepository->getCallableObject($sClassName);
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

    /**
     * Create callable objects for all registered classes
     *
     * @return void
     */
    public function registerCallableObjects()
    {
        $this->parseCallableClasses();

        $aClasses = $this->xRepository->getClasses();
        foreach($aClasses as $sClassName => $aClassOptions)
        {
            // Make sure we create each callable object only once.
            if(!$this->xRepository->hasCallableObject($sClassName))
            {
                $this->xRepository->registerCallableObject($sClassName, $aClassOptions);
            }
        }
    }
}
