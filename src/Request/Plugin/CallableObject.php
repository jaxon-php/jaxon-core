<?php

/**
 * CallableObject.php - Jaxon callable object plugin
 *
 * This class registers user defined callable objects, generates client side javascript code,
 * and calls their methods on user request
 *
 * @package jaxon-core
 * @author Jared White
 * @author J. Max Wilson
 * @author Joseph Woolley
 * @author Steffen Konerow
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
 * @copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Plugin;

use Jaxon\Jaxon;
use Jaxon\Plugin\Request as RequestPlugin;

class CallableObject extends RequestPlugin
{
    use \Jaxon\Utils\Traits\Config;
    use \Jaxon\Utils\Traits\Manager;
    use \Jaxon\Utils\Traits\Validator;
    use \Jaxon\Utils\Traits\Translator;

    /**
     * The registered callable objects
     *
     * @var array
     */
    protected $aCallableObjects;

    /**
     * The classpaths of the registered callable objects
     *
     * @var array
     */
    protected $aClassPaths;

    /**
     * Directories where Jaxon classes to be registered are found
     *
     * @var array
     */
    private $aClassDirs = [];

    /**
     * True if the Composer autoload is enabled
     *
     * @var boolean
     */
    private $bAutoloadEnabled;

    /**
     * The Composer autoloader
     *
     * @var Autoloader
     */
    private $xAutoloader;

    /**
     * The value of the class parameter of the incoming Jaxon request
     *
     * @var string
     */
    protected $sRequestedClass;

    /**
     * The value of the method parameter of the incoming Jaxon request
     *
     * @var string
     */
    protected $sRequestedMethod;

    public function __construct()
    {
        $this->aCallableObjects = [];
        $this->aClassPaths = [];

        $this->bAutoloadEnabled = true;
        $this->xAutoloader = null;

        $this->sRequestedClass = null;
        $this->sRequestedMethod = null;

        if(!empty($_GET['jxncls']))
        {
            $this->sRequestedClass = $_GET['jxncls'];
        }
        if(!empty($_GET['jxnmthd']))
        {
            $this->sRequestedMethod = $_GET['jxnmthd'];
        }
        if(!empty($_POST['jxncls']))
        {
            $this->sRequestedClass = $_POST['jxncls'];
        }
        if(!empty($_POST['jxnmthd']))
        {
            $this->sRequestedMethod = $_POST['jxnmthd'];
        }
    }

    /**
     * Return the name of this plugin
     *
     * @return string
     */
    public function getName()
    {
        return Jaxon::CALLABLE_OBJECT;
    }

    /**
     * Use the Composer autoloader
     *
     * @return void
     */
    public function useComposerAutoloader()
    {
        $this->bAutoloadEnabled = true;
        $this->xAutoloader = require(__DIR__ . '/../../../../autoload.php');
    }

    /**
     * Disable the autoloader in the library
     *
     * The user shall provide an alternative autoload system.
     *
     * @return void
     */
    public function disableAutoload()
    {
        $this->bAutoloadEnabled = false;
        $this->xAutoloader = null;
    }

    /**
     * Register a user defined callable object
     *
     * @param array         $aArgs                An array containing the callable object specification
     *
     * @return array
     */
    public function register($aArgs)
    {
        if(count($aArgs) < 2)
        {
            return false;
        }

        $sType = $aArgs[0];
        if($sType != Jaxon::CALLABLE_OBJECT)
        {
            return false;
        }

        $sCallableObject = $aArgs[1];
        if(!is_string($sCallableObject) || !class_exists($sCallableObject))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.objects.invalid-declaration'));
        }
        $sCallableObject = trim($sCallableObject, '\\');
        $this->aCallableObjects[] = $sCallableObject;

        $aOptions = count($aArgs) > 2 ? $aArgs[2] : [];
        if(is_string($aOptions))
        {
            $aOptions = ['namespace' => $aOptions];
        }
        if(!is_array($aOptions))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.objects.invalid-declaration'));
        }

        // Save the classpath and the separator in this class
        if(key_exists('*', $aOptions) && is_array($aOptions['*']))
        {
            $_aOptions = $aOptions['*'];
            $sSeparator = '.';
            if(key_exists('separator', $_aOptions))
            {
                $sSeparator = trim($_aOptions['separator']);
            }
            if(!in_array($sSeparator, ['.', '_']))
            {
                $sSeparator = '.';
            }
            $_aOptions['separator'] = $sSeparator;

            if(array_key_exists('classpath', $_aOptions))
            {
                $_aOptions['classpath'] = trim($_aOptions['classpath'], ' \\._');
                // Save classpath with "\" in the parameters
                $_aOptions['classpath'] = str_replace(['.', '_'], ['\\', '\\'], $_aOptions['classpath']);
                // Save classpath with separator locally
                $this->aClassPaths[] = str_replace('\\', $sSeparator, $_aOptions['classpath']);
            }
        }

        // Register the callable object
        jaxon()->di()->set($sCallableObject, function () use ($sCallableObject, $aOptions) {
            $xCallableObject = new \Jaxon\Request\Support\CallableObject($sCallableObject);

            foreach($aOptions as $sMethod => $aValue)
            {
                foreach($aValue as $sName => $sValue)
                {
                    $xCallableObject->configure($sMethod, $sName, $sValue);
                }
            }

            return $xCallableObject;
        });

        // Register the request factory for this callable object
        jaxon()->di()->set($sCallableObject . '\Factory\Rq', function ($di) use ($sCallableObject) {
            $xCallableObject = $di->get($sCallableObject);
            return new \Jaxon\Sentry\Factory\Request($xCallableObject);
        });

        // Register the paginator factory for this callable object
        jaxon()->di()->set($sCallableObject . '\Factory\Pg', function ($di) use ($sCallableObject) {
            $xCallableObject = $di->get($sCallableObject);
            return new \Jaxon\Sentry\Factory\Paginator($xCallableObject);
        });

        return true;
    }

    /**
     * Add a path to the class directories
     *
     * @param string            $sDirectory             The path to the directory
     * @param string|null       $sNamespace             The associated namespace
     * @param string            $sSeparator             The character to use as separator in javascript class names
     * @param array             $aProtected             The functions that are not to be exported
     *
     * @return boolean
     */
    public function addClassDir($sDirectory, $sNamespace = '', $sSeparator = '.', array $aProtected = [])
    {
        if(!is_dir(($sDirectory = trim($sDirectory))))
        {
            return false;
        }
        // Only '.' and '_' are allowed to be used as separator. Any other value is ignored and '.' is used instead.
        if(($sSeparator = trim($sSeparator)) != '_')
        {
            $sSeparator = '.';
        }
        if(!($sNamespace = trim($sNamespace, ' \\')))
        {
            $sNamespace = '';
        }
        if(($sNamespace))
        {
            // If there is an autoloader, register the dir with PSR4 autoloading
            if(($this->xAutoloader))
            {
                $this->xAutoloader->setPsr4($sNamespace . '\\', $sDirectory);
            }
        }
        elseif(($this->xAutoloader))
        {
            // If there is an autoloader, register the dir with classmap autoloading
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
                $this->xAutoloader->addClassMap(array($xFile->getBasename('.php') => $xFile->getPathname()));
            }
        }
        $this->aClassDirs[] = array(
            'directory' => rtrim($sDirectory, DIRECTORY_SEPARATOR),
            'namespace' => $sNamespace,
            'separator' => $sSeparator,
            'protected' => $aProtected
        );
        return true;
    }

    /**
     * Register an instance of a given class from a file
     *
     * @param object            $xFile                  The PHP file containing the class
     * @param string            $sDirectory             The path to the directory
     * @param string|''         $sNamespace             The associated namespace
     * @param string            $sSeparator             The character to use as separator in javascript class names
     * @param array             $aProtected             The functions that are not to be exported
     * @param array             $aOptions               The options to register the class with
     *
     * @return void
     */
    protected function registerClassFromFile($xFile, $sDirectory, $sNamespace = '', $sSeparator = '.',
        array $aProtected = [], array $aOptions = [])
    {
        $sDS = DIRECTORY_SEPARATOR;
        // Get the corresponding class path and name
        $sClassPath = substr($xFile->getPath(), strlen($sDirectory));
        $sClassPath = str_replace($sDS, '\\', trim($sClassPath, $sDS));
        $sClassName = $xFile->getBasename('.php');
        if(($sNamespace))
        {
            $sClassPath = ($sClassPath) ? $sNamespace . '\\' . $sClassPath : $sNamespace;
            $sClassName = '\\' . $sClassPath . '\\' . $sClassName;
        }
        // Require the file only if autoload is enabled but there is no autoloader
        if(($this->bAutoloadEnabled) && !($this->xAutoloader))
        {
            require_once($xFile->getPathname());
        }
        // Create and register an instance of the class
        if(!array_key_exists('*', $aOptions) || !is_array($aOptions['*']))
        {
            $aOptions['*'] = [];
        }
        $aOptions['*']['separator'] = $sSeparator;
        if(($sNamespace))
        {
            $aOptions['*']['namespace'] = $sNamespace;
        }
        if(($sClassPath))
        {
            $aOptions['*']['classpath'] = $sClassPath;
        }
        // Filter excluded methods
        $aProtected = array_filter($aProtected, function ($sName) {return is_string($sName);});
        if(count($aProtected) > 0)
        {
            $aOptions['*']['protected'] = $aProtected;
        }
        $this->register(array(Jaxon::CALLABLE_OBJECT, $sClassName, $aOptions));
    }

    /**
     * Register callable objects from all class directories
     *
     * @param array             $aOptions               The options to register the classes with
     *
     * @return void
     */
    public function registerClasses(array $aOptions = [])
    {
        $sDS = DIRECTORY_SEPARATOR;
        // Change the keys in $aOptions to have "\" as separator
        $aNewOptions = [];
        foreach($aOptions as $key => $aOption)
        {
            $key = trim(str_replace(['.', '_'], ['\\', '\\'], $key), ' \\');
            $aNewOptions[$key] = $aOption;
        }

        foreach($this->aClassDirs as $aClassDir)
        {
            // Get the directory
            $sDirectory = $aClassDir['directory'];
            // Get the namespace
            $sNamespace = $aClassDir['namespace'];

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

                // Get the class name
                $sClassPath = substr($xFile->getPath(), strlen($sDirectory));
                $sClassPath = trim(str_replace($sDS, '\\', $sClassPath), '\\');
                $sClassName = $xFile->getBasename('.php');
                if(($sClassPath))
                {
                    $sClassName = $sClassPath . '\\' . $sClassName;
                }
                if(($sNamespace))
                {
                    $sClassName = $sNamespace . '\\' . $sClassName;
                }
                // Get the class options
                $aClassOptions = [];
                if(array_key_exists($sClassName, $aNewOptions))
                {
                    $aClassOptions = $aNewOptions[$sClassName];
                }

                $this->registerClassFromFile($xFile, $sDirectory, $sNamespace,
                    $aClassDir['separator'], $aClassDir['protected'], $aClassOptions);
            }
        }
    }

    /**
     * Register an instance of a given class
     *
     * @param string            $sClassName             The name of the class to be registered
     * @param array             $aOptions               The options to register the class with
     *
     * @return bool
     */
    public function registerClass($sClassName, array $aOptions = [])
    {
        if(!($sClassName = trim($sClassName, ' \\._')))
        {
            return false;
        }
        $sDS = DIRECTORY_SEPARATOR;

        // Replace "." and "_" with antislashes, and set the class path.
        $sClassName = str_replace(['.', '_'], ['\\', '\\'], $sClassName);
        $sClassPath = '';
        if(($nLastSlashPosition = strrpos($sClassName, '\\')) !== false)
        {
            $sClassPath = substr($sClassName, 0, $nLastSlashPosition);
            $sClassName = substr($sClassName, $nLastSlashPosition + 1);
        }
        // Path to the file, relative to a registered directory.
        $sPartPath = str_replace('\\', $sDS, $sClassPath) . $sDS . $sClassName . '.php';

        // Search for the class file in all directories.
        foreach($this->aClassDirs as $aClassDir)
        {
            // Get the separator
            $sSeparator = $aClassDir['separator'];
            // Get the namespace
            $sNamespace = $aClassDir['namespace'];
            $nLen = strlen($sNamespace);
            $sFullPath = '';
            // Check if the class belongs to the namespace
            if(($sNamespace) && substr($sClassPath, 0, $nLen) == $sNamespace)
            {
                $sFullPath = $aClassDir['directory'] . $sDS . substr($sPartPath, $nLen + 1);
            }
            elseif(!($sNamespace))
            {
                $sFullPath = $aClassDir['directory'] . $sDS . $sPartPath;
            }
            if(($sFullPath) && is_file($sFullPath))
            {
                // Found the file in this directory
                $xFileInfo = new \SplFileInfo($sFullPath);
                $sDirectory = $aClassDir['directory'];
                $aProtected = $aClassDir['protected'];
                $this->registerClassFromFile($xFileInfo, $sDirectory, $sNamespace, $sSeparator, $aProtected, $aOptions);
                return true;
            }
        }
        return false;
    }

    /**
     * Generate a hash for the registered callable objects
     *
     * @return string
     */
    public function generateHash()
    {
        $di = jaxon()->di();
        $sHash = '';
        foreach($this->aCallableObjects as $sName)
        {
            $xCallableObject = $di->get($sName);
            $sHash .= $sName . implode('|', $xCallableObject->getMethods());
        }
        return md5($sHash);
    }

    /**
     * Generate client side javascript code for the registered callable objects
     *
     * @return string
     */
    public function getScript()
    {
        $sJaxonPrefix = $this->getOption('core.prefix.class');
        // Generate code for javascript objects declaration
        $code = '';
        $classes = [];
        foreach($this->aClassPaths as $sClassPath)
        {
            $offset = 0;
            $sClassPath .= '.Null'; // This is a sentinel. The last token is not processed in the while loop.
            while(($dotPosition = strpos($sClassPath, '.', $offset)) !== false)
            {
                $class = substr($sClassPath, 0, $dotPosition);
                // Generate code for this object
                if(!array_key_exists($class, $classes))
                {
                    $code .= "$sJaxonPrefix$class = {};\n";
                    $classes[$class] = $class;
                }
                $offset = $dotPosition + 1;
            }
        }
        // Generate code for javascript methods
        $di = jaxon()->di();
        foreach($this->aCallableObjects as $sName)
        {
            $xCallableObject = $di->get($sName);
            $code .= $xCallableObject->getScript();
        }
        return $code;
    }

    /**
     * Check if this plugin can process the incoming Jaxon request
     *
     * @return boolean
     */
    public function canProcessRequest()
    {
        // Check the validity of the class name
        if(($this->sRequestedClass) && !$this->validateClass($this->sRequestedClass))
        {
            $this->sRequestedClass = null;
            $this->sRequestedMethod = null;
        }
        // Check the validity of the method name
        if(($this->sRequestedMethod) && !$this->validateMethod($this->sRequestedMethod))
        {
            $this->sRequestedClass = null;
            $this->sRequestedMethod = null;
        }
        return ($this->sRequestedClass != null && $this->sRequestedMethod != null);
    }

    /**
     * Process the incoming Jaxon request
     *
     * @return boolean
     */
    public function processRequest()
    {
        if(!$this->canProcessRequest())
        {
            return false;
        }

        $aArgs = $this->getRequestManager()->process();

        // Find the requested method
        $xCallableObject = $this->getCallableObject($this->sRequestedClass);
        if(!$xCallableObject || !$xCallableObject->hasMethod($this->sRequestedMethod))
        {
            // Unable to find the requested object or method
            throw new \Jaxon\Exception\Error($this->trans('errors.objects.invalid',
                ['class' => $this->sRequestedClass, 'method' => $this->sRequestedMethod]));
        }

        // Call the requested method
        $xCallableObject->call($this->sRequestedMethod, $aArgs);
        return true;
    }

    /**
     * Find a callable object by class name
     *
     * @param string        $sClassName            The class name of the callable object
     *
     * @return object
     */
    public function getCallableObject($sClassName)
    {
        // Replace all separators ('.' and '_') with antislashes, and remove the antislashes
        // at the beginning and the end of the class name.
        $sClassName = trim(str_replace(['.', '_'], ['\\', '\\'], (string)$sClassName), '\\');
        // Register an instance of the requested class, if it isn't yet
        if(!key_exists($sClassName, $this->aCallableObjects))
        {
            $this->getPluginManager()->registerClass($sClassName);
        }
        return key_exists($sClassName, $this->aCallableObjects) ? jaxon()->di()->get($sClassName) : null;
    }

    /**
     * Find a user registered callable object by class name
     *
     * @param string        $sClassName            The class name of the callable object
     *
     * @return object
     */
    public function getRegisteredObject($sClassName)
    {
        // Get the corresponding callable object
        $xCallableObject = $this->getCallableObject($sClassName);
        return ($xCallableObject) ? $xCallableObject->getRegisteredObject() : null;
    }
}
