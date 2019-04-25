<?php

/**
 * CallableDir.php - Jaxon callable dir plugin
 *
 * This class registers directories containing user defined callable classes,
 * and generates client side javascript code.
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

class CallableDir extends RequestPlugin
{
    use \Jaxon\Utils\Traits\Config;
    use \Jaxon\Utils\Traits\Manager;
    use \Jaxon\Utils\Traits\Validator;
    use \Jaxon\Utils\Traits\Translator;

    /**
     * The registered namespaces with their directories
     *
     * @var array
     */
    protected $aNamespaces = [];

    /**
     * The classes of the registered callable objects
     *
     * @var array
     */
    protected $aClassNames = [];

    /**
     * The registered callable objects
     *
     * @var array
     */
    protected $aCallableObjects = [];

    /**
     * True if the Composer autoload is enabled
     *
     * @var boolean
     */
    private $bAutoloadEnabled = true;

    /**
     * The Composer autoloader
     *
     * @var Autoloader
     */
    private $xAutoloader = null;

    /**
     * The value of the class parameter of the incoming Jaxon request
     *
     * @var string
     */
    protected $sRequestedClass = null;

    /**
     * The value of the method parameter of the incoming Jaxon request
     *
     * @var string
     */
    protected $sRequestedMethod = null;

    public function __construct()
    {
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
        return Jaxon::CALLABLE_DIR;
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
     * Register a callable class
     *
     * @param string        $sType          The type of request handler being registered
     * @param string        $sDirectory     The name of the class being registered
     * @param array|string  $aOptions       The associated options
     *
     * @return boolean
     */
    public function register($sType, $sDirectory, $aOptions)
    {
        if($sType != $this->getName())
        {
            return false;
        }

        if(!is_string($sDirectory) || !is_dir($sDirectory))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.objects.invalid-declaration'));
        }
        $sDirectory = trim($sDirectory, DIRECTORY_SEPARATOR);

        if(is_string($aOptions))
        {
            $aOptions = ['namespace' => $aOptions];
        }
        if(!is_array($aOptions))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.objects.invalid-declaration'));
        }

        if(!is_dir(($sDirectory = trim($sDirectory))))
        {
            return false;
        }
        $aOptions['directory'] = $sDirectory;

        $aProtected = key_exists('protected', $aOptions) ? $aOptions['protected'] : [];
        if(!is_array($aProtected))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.objects.invalid-declaration'));
        }
        $aOptions['protected'] = $aProtected;

        $sSeparator = key_exists('separator', $aOptions) ? $aOptions['separator'] : '.';
        // Only '.' and '_' are allowed to be used as separator. Any other value is ignored and '.' is used instead.
        if(($sSeparator = trim($sSeparator)) != '_')
        {
            $sSeparator = '.';
        }
        $aOptions['separator'] = $sSeparator;

        $sNamespace = key_exists('namespace', $aOptions) ? $aOptions['namespace'] : '';
        if(!($sNamespace = trim($sNamespace, ' \\')))
        {
            $sNamespace = '';
        }
        $aOptions['namespace'] = $sNamespace;

        // Todo: Change the keys in $aOptions['classes'] to have "\" as separator
        // $aNewOptions = [];
        // foreach($aOptions['classes'] as $sClass => $aOption)
        // {
        //     $sClass = trim(str_replace(['.', '_'], ['\\', '\\'], $sClass), ' \\');
        //     $aNewOptions[$sClass] = $aOption;
        // }
        // $aOptions['classes'] = $aNewOptions;

        if(($sNamespace))
        {
            // Register the dir with PSR4 autoloading
            if(($this->xAutoloader))
            {
                $this->xAutoloader->setPsr4($sNamespace . '\\', $sDirectory);
            }

            $this->aNamespaces[$sNamespace] = $aOptions;
        }
        else
        {
            // Get the callable class plugin
            $callableClassPlugin = $this->getPluginManager()->getRequestPlugin(Jaxon::CALLABLE_CLASS);

            // Register the dir with classmap autoloading
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
                if(($this->xAutoloader))
                {
                    $this->xAutoloader->addClassMap([$sClassName => $xFile->getPathname()]);
                }
                elseif(!class_exists($sClassName))
                {
                    $aOptions['include'] = $xFile->getPathname();
                }

                $callableClassPlugin->register(Jaxon::CALLABLE_CLASS, $sClassName, $aOptions);
            }
        }

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

        // Make sure the registered class exists
        if(!class_exists('\\' . $sClassName))
        {
            return null;
        }

        if(key_exists($sClassName, $this->aCallableObjects))
        {
            return $this->aCallableObjects[$sClassName];
        }

        // Find the corresponding namespace
        $sNamespace = null;
        foreach(array_keys($this->aNamespaces) as $_sNamespace)
        {
            if(substr($sClassName, 0, strlen($_sNamespace)) == $_sNamespace)
            {
                $sNamespace = $_sNamespace;
                break;
            }
        }
        if($sNamespace == null)
        {
            return null; // Class not registered
        }

        // Create the callable object
        $xCallableObject = new \Jaxon\Request\Support\CallableObject($sClassName);
        $aOptions = $this->aNamespaces[$sNamespace];
        foreach($aOptions as $sClass => $aClassOptions)
        {
            if($sClass == '*' || trim(str_replace(['.', '_'], ['\\', '\\'], $sClass)) == $sClassName)
            {
                foreach($aClassOptions as $sMethod => $aValue)
                {
                    foreach($aValue as $sName => $sValue)
                    {
                        $xCallableObject->configure($sMethod, $sName, $sValue);
                    }
                }
            }
        }

        $this->aCallableObjects[$sClassName] = $xCallableObject;
        // jaxon()->di()->set($sClassName, $xCallableObject);
        // Register the request factory for this callable object
        jaxon()->di()->set($sClassName . '_Factory_Rq', function ($di) use ($sClassName) {
            $xCallableObject = $this->aCallableObjects[$sClassName];
            return new \Jaxon\Factory\Request\Portable($xCallableObject);
        });
        // Register the paginator factory for this callable object
        jaxon()->di()->set($sClassName . '_Factory_Pg', function ($di) use ($sClassName) {
            $xCallableObject = $this->aCallableObjects[$sClassName];
            return new \Jaxon\Factory\Request\Paginator($xCallableObject);
        });

        return $xCallableObject;
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

    /**
     * Create callable objects for all registered namespaces
     *
     * @return void
     */
    private function createCallableObjects()
    {
        $sDS = DIRECTORY_SEPARATOR;

        foreach($this->aNamespaces as $sNamespace => $aOptions)
        {
            if(key_exists($sNamespace, $this->aClassNames))
            {
                continue;
            }

            $this->aClassNames[$sNamespace] = [];

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
                $sRelativePath = trim(str_replace($sDS, '\\', $sClassPath), '\\');
                if(($sRelativePath))
                {
                    $sClassPath .= '\\' . $sRelativePath;
                }
                if(!key_exists($sClassPath, $this->aClassNames))
                {
                    $this->aClassNames[$sClassPath] = [];
                }

                $sClassName = $xFile->getBasename('.php');
                $this->aClassNames[$sClassPath][] = $sClassName;
                $this->getCallableObject($sNamespace . '\\' . $sClass);
            }
        }
    }

    /**
     * Generate a hash for the registered callable objects
     *
     * @return string
     */
    public function generateHash()
    {
        if(count($this->aNamespaces) == 0)
        {
            return '';
        }

        $this->createCallableObjects();

        $sHash = '';
        foreach($this->aNamespaces as $sNamespace => $aOptions)
        {
            $sHash .= $sNamespace . $aOptions['directory'] . $aOptions['separator'];
        }
        foreach($this->aCallableObjects as $sClassName => $xCallableObject)
        {
            $sHash .= $sClassName . implode('|', $xCallableObject->getMethods());
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
        $this->createCallableObjects();

        // Generate code for javascript objects declaration
        $sJaxonPrefix = $this->getOption('core.prefix.class');
        $aJsClasses = [];
        $sCode = '';
        foreach(array_keys($this->aClassNames) as $sNamespace)
        {
            // if(key_exists('separator', $aOptions) && $aOptions['separator'] != '.')
            // {
            //     continue;
            // }
            $offset = 0;
            $sJsClasses = str_replace('\\', '.', $sNamespace);
            $sJsClasses .= '.Null'; // This is a sentinel. The last token is not processed in the while loop.
            while(($dotPosition = strpos($sJsClasses, '.', $offset)) !== false)
            {
                $sJsClass = substr($sJsClasses, 0, $dotPosition);
                // Generate code for this object
                if(!key_exists($sJsClass, $aJsClasses))
                {
                    $sCode .= "$sJaxonPrefix$sJsClass = {};\n";
                    $aJsClasses[$sJsClass] = $sJsClass;
                }
                $offset = $dotPosition + 1;
            }
        }
        foreach($this->aCallableObjects as $xCallableObject)
        {
            $sCode .= $xCallableObject->getScript();
        }

        return $sCode;
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
}
