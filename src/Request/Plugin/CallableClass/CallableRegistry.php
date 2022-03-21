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

namespace Jaxon\Request\Plugin\CallableClass;

use Jaxon\Di\Container;
use Jaxon\Request\Factory\RequestFactory;
use Jaxon\Utils\Translation\Translator;
use Jaxon\Exception\SetupException;

use Composer\Autoload\ClassLoader;

use function file_exists;
use function in_array;
use function str_replace;
use function strlen;
use function strncmp;
use function trim;

class CallableRegistry
{
    /**
     * The DI container
     *
     * @var Container
     */
    protected $di;

    /**
     * The callable repository
     *
     * @var CallableRepository
     */
    protected $xRepository;

    /**
     * @var Translator
     */
    protected $xTranslator;

    /**
     * The registered directories
     *
     * These are directories registered without a namespace.
     *
     * @var array
     */
    protected $aDirectories = [];

    /**
     * The registered namespaces
     *
     * These are the namespaces specified when registering directories.
     *
     * @var array
     */
    protected $aNamespaces = [];

    /**
     * If the underscore is used as separator in js class names.
     *
     * @var bool
     */
    protected $bUsingUnderscore = false;

    /**
     * The Composer autoloader
     *
     * @var ClassLoader
     */
    private $xAutoloader = null;

    /**
     * The class constructor
     *
     * @param Container $di
     * @param CallableRepository $xRepository
     * @param Translator $xTranslator
     */
    public function __construct(Container $di, CallableRepository $xRepository, Translator $xTranslator)
    {
        $this->di = $di;
        $this->xTranslator = $xTranslator;
        $this->xRepository = $xRepository;

        // Set the composer autoloader
        $sAutoloadFile = __DIR__ . '/../../../../../../autoload.php';
        if(file_exists($sAutoloadFile))
        {
            $this->xAutoloader = require($sAutoloadFile);
        }
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

        $this->aDirectories[$sDirectory] = $aOptions;
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
     * Find options for a class which is registered with namespace
     *
     * @param string $sClassName    The class name
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
     * @param string $sClassName The class name
     *
     * @return array
     * @throws SetupException
     */
    protected function getClassOptions(string $sClassName): array
    {
        // Find options for a class registered with namespace.
        $aOptions = $this->getClassOptionsFromNamespaces($sClassName);
        if($aOptions !== null)
        {
            return $aOptions;
        }
        // Without a namespace, we need to parse all classes to be able to find one.
        $this->xRepository->parseDirectories($this->aDirectories);
        // Find options for a class registered without namespace.
        $aOptions = $this->xRepository->getClassOptions($sClassName);
        if($aOptions !== null)
        {
            return $aOptions;
        }
        $sMessage = $this->xTranslator->trans('errors.class.invalid', ['name' => $sClassName]);
        throw new SetupException($sMessage);
    }

    /**
     * Check if a callable object is already in the DI, and register if not
     *
     * @param string $sClassName The class name of the callable object
     *
     * @return string
     * @throws SetupException
     */
    private function checkCallableObject(string $sClassName): string
    {
        // Replace all separators ('.' and '_') with antislashes, and remove the antislashes
        // at the beginning and the end of the class name.
        $sSeparator = $this->bUsingUnderscore ? '_' : '.';
        $sClassName = trim(str_replace($sSeparator, '\\', $sClassName), '\\');

        // Check if the callable object was already created.
        if(!$this->di->h($sClassName))
        {
            $aOptions = $this->getClassOptions($sClassName);
            $this->xRepository->registerCallableClass($sClassName, $aOptions);
        }
        return $sClassName;
    }

    /**
     * Get the callable object for a given class
     *
     * @param string $sClassName The class name of the callable object
     *
     * @return CallableObject|null
     * @throws SetupException
     */
    public function getCallableObject(string $sClassName): ?CallableObject
    {
        return $this->di->getCallableObject($this->checkCallableObject($sClassName));
    }

    /**
     * Get the request factory for a given class
     *
     * @param string $sClassName The class name of the callable object
     *
     * @return RequestFactory|null
     * @throws SetupException
     */
    public function getRequestFactory(string $sClassName): ?RequestFactory
    {
        return $this->di->getRequestFactory($this->checkCallableObject($sClassName));
    }

    /**
     * Register all the callable classes
     *
     * @return void
     */
    public function parseCallableClasses()
    {
        $this->xRepository->parseDirectories($this->aDirectories);
        $this->xRepository->parseNamespaces($this->aNamespaces);
    }

    /**
     * Create callable objects for all registered classes
     *
     * @return void
     * @throws SetupException
     */
    public function registerCallableClasses()
    {
        $this->parseCallableClasses();

        $aClasses = $this->xRepository->getClasses();
        foreach($aClasses as $sClassName => $aClassOptions)
        {
            $this->xRepository->registerCallableClass($sClassName, $aClassOptions);
        }
    }
}
