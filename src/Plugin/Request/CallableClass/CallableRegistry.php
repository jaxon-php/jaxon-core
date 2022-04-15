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

use Jaxon\App\I18n\Translator;
use Jaxon\Di\Container;
use Jaxon\Exception\SetupException;
use Jaxon\Request\Factory\RequestFactory;

use Composer\Autoload\ClassLoader;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use function file_exists;
use function in_array;
use function str_replace;
use function strlen;
use function substr;
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
     * @var bool
     */
    protected $bDirectoriesParsed = false;

    /**
     * @var bool
     */
    protected $bNamespacesParsed = false;

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
        if(file_exists(($sAutoloadFile = __DIR__ . '/../../../../../../autoload.php')) ||
            file_exists(($sAutoloadFile = __DIR__ . '/../../../../vendor/autoload.php')))
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
        $this->xRepository->setDirectoryOptions($sDirectory, $aOptions);
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
        $this->xRepository->setNamespaceOptions($sNamespace, $aOptions);
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
        foreach($this->xRepository->getDirectoryOptions() as $sDirectory => $aOptions)
        {
            $itFile = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sDirectory));
            // Iterate on dir content
            foreach($itFile as $xFile)
            {
                // Skip everything except PHP files
                if(!$xFile->isFile() || $xFile->getExtension() != 'php')
                {
                    continue;
                }

                $sClassName = $xFile->getBasename('.php');
                $aClassOptions = ['timestamp' => $xFile->getMTime()];
                // No more classmap autoloading. The file will be included when needed.
                if(($aOptions['autoload']))
                {
                    $aClassMap[$sClassName] = $xFile->getPathname();
                }
                $this->xRepository->addClass($sClassName, $aClassOptions, $aOptions);
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
        foreach($this->xRepository->getNamespaceOptions() as $sNamespace => $aOptions)
        {
            $this->xRepository->addNamespace($sNamespace, ['separator' => $aOptions['separator']]);

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

                $this->xRepository->addNamespace($sClassPath, ['separator' => $aOptions['separator']]);

                $sClassName = $sClassPath . '\\' . $xFile->getBasename('.php');
                $aClassOptions = ['namespace' => $sNamespace, 'timestamp' => $xFile->getMTime()];
                $this->xRepository->addClass($sClassName, $aClassOptions, $aOptions);
            }
        }
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
        $sClassName = trim(str_replace('.', '\\', $sClassName), '\\');
        if($this->bUsingUnderscore)
        {
            $sClassName = trim(str_replace('_', '\\', $sClassName), '\\');
        }
        // Register the class, if it wasn't already.
        if(!$this->di->h($sClassName))
        {
            $this->di->registerCallableClass($sClassName, $this->xRepository->getClassOptions($sClassName));
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
        $this->parseDirectories();
        $this->parseNamespaces();
    }
}
