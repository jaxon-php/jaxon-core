<?php

/**
 * Manager.php - Jaxon plugin manager
 *
 * Register Jaxon plugins, generate corresponding code, handle request
 * and redirect them to the right plugin.
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

namespace Jaxon\Plugin;

use Jaxon\Jaxon;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use RecursiveRegexIterator;

class Manager
{
    use \Jaxon\Utils\Traits\Manager;
    use \Jaxon\Utils\Traits\Config;
    use \Jaxon\Utils\Traits\Cache;
    use \Jaxon\Utils\Traits\Event;
    use \Jaxon\Utils\Traits\Minifier;
    use \Jaxon\Utils\Traits\Template;
    use \Jaxon\Utils\Traits\Translator;

    /**
     * The response type.
     *
     * @var string
     */
    const RESPONSE_TYPE = 'JSON';

    /**
     * All plugins, indexed by priority
     *
     * @var array
     */
    private $aPlugins;

    /**
     * Request plugins, indexed by name
     *
     * @var array
     */
    private $aRequestPlugins;

    /**
     * Response plugins, indexed by name
     *
     * @var array
     */
    private $aResponsePlugins;

    /**
     * Directories where Jaxon classes to be registered are found
     *
     * @var array
     */
    private $aClassDirs;

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
     * Javascript confirm function
     *
     * @var \Jaxon\Request\Interfaces\Confirm
     */
    private $xConfirm;

    /**
     * Default javascript confirm function
     *
     * @var \Jaxon\Request\Support\Confirm
     */
    private $xDefaultConfirm;

    /**
     * Javascript alert function
     *
     * @var \Jaxon\Request\Interfaces\Alert
     */
    private $xAlert;

    /**
     * Default javascript alert function
     *
     * @var \Jaxon\Request\Support\Alert
     */
    private $xDefaultAlert;

    /**
     * Initialize the Jaxon Plugin Manager
     */
    public function __construct()
    {
        $this->aRequestPlugins = array();
        $this->aResponsePlugins = array();
        $this->aPlugins = array();
        $this->aClassDirs = array();

        $this->bAutoloadEnabled = true;
        $this->xAutoloader = null;

        // Javascript confirm function
        $this->xConfirm = null;
        $this->xDefaultConfirm = new \Jaxon\Request\Support\Confirm();

        // Javascript alert function
        $this->xAlert = null;
        $this->xDefaultAlert = new \Jaxon\Request\Support\Alert();
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
     * Set the javascript confirm function
     *
     * @param \Jaxon\Request\Interfaces\Confirm         $xConfirm     The javascript confirm function
     *
     * @return void
     */
    public function setConfirm(\Jaxon\Request\Interfaces\Confirm $xConfirm)
    {
        $this->xConfirm = $xConfirm;
    }

    /**
     * Get the javascript confirm function
     *
     * @return \Jaxon\Request\Interfaces\Confirm
     */
    public function getConfirm()
    {
        return (($this->xConfirm) ? $this->xConfirm : $this->xDefaultConfirm);
    }

    /**
     * Get the default javascript confirm function
     *
     * @return \Jaxon\Request\Support\Confirm
     */
    public function getDefaultConfirm()
    {
        return $this->xDefaultConfirm;
    }

    /**
     * Set the javascript alert function
     *
     * @param \Jaxon\Request\Interfaces\Alert           $xAlert       The javascript alert function
     *
     * @return void
     */
    public function setAlert(\Jaxon\Request\Interfaces\Alert $xAlert)
    {
        $this->xAlert = $xAlert;
    }

    /**
     * Get the javascript alert function
     *
     * @return \Jaxon\Request\Interfaces\Alert
     */
    public function getAlert()
    {
        return (($this->xAlert) ? $this->xAlert : $this->xDefaultAlert);
    }

    /**
     * Get the default javascript alert function
     *
     * @return \Jaxon\Request\Support\Alert
     */
    public function getDefaultAlert()
    {
        return $this->xDefaultAlert;
    }

    /**
     * Inserts an entry into an array given the specified priority number
     *
     * If a plugin already exists with the given priority, the priority is automatically incremented until a free spot is found.
     * The plugin is then inserted into the empty spot in the array.
     *
     * @param Plugin         $xPlugin               An instance of a plugin
     * @param integer        $nPriority             The desired priority, used to order the plugins
     *
     * @return void
     */
    private function setPluginPriority(Plugin $xPlugin, $nPriority)
    {
        while (isset($this->aPlugins[$nPriority]))
        {
            $nPriority++;
        }
        $this->aPlugins[$nPriority] = $xPlugin;
        // Sort the array by ascending keys
        ksort($this->aPlugins);
    }

    /**
     * Register a plugin
     *
     * Below is a table for priorities and their description:
     * - 0 thru 999: Plugins that are part of or extensions to the jaxon core
     * - 1000 thru 8999: User created plugins, typically, these plugins don't care about order
     * - 9000 thru 9999: Plugins that generally need to be last or near the end of the plugin list
     *
     * @param Plugin         $xPlugin               An instance of a plugin
     * @param integer        $nPriority             The plugin priority, used to order the plugins
     *
     * @return void
     */
    public function registerPlugin(Plugin $xPlugin, $nPriority = 1000)
    {
        $bIsAlert = ($xPlugin instanceof \Jaxon\Request\Interfaces\Alert);
        $bIsConfirm = ($xPlugin instanceof \Jaxon\Request\Interfaces\Confirm);
        if($xPlugin instanceof Request)
        {
            // The name of a request plugin is used as key in the plugin table
            $this->aRequestPlugins[$xPlugin->getName()] = $xPlugin;
        }
        elseif($xPlugin instanceof Response)
        {
            // The name of a response plugin is used as key in the plugin table
            $this->aResponsePlugins[$xPlugin->getName()] = $xPlugin;
        }
        elseif(!$bIsConfirm && !$bIsAlert)
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.register.invalid', array('name' => get_class($xPlugin))));
        }
        // This plugin implements the Alert interface
        if($bIsAlert)
        {
            $this->setAlert($xPlugin);
        }
        // This plugin implements the Confirm interface
        if($bIsConfirm)
        {
            $this->setConfirm($xPlugin);
        }
        // Register the plugin as an event listener
        if($xPlugin instanceof \Jaxon\Utils\Interfaces\EventListener)
        {
            $this->addEventListener($xPlugin);
        }

        $this->setPluginPriority($xPlugin, $nPriority);
    }

    /**
     * Generate a hash for all the javascript code generated by the library
     *
     * @return string
     */
    private function generateHash()
    {
        $sHash = $this->getVersion();
        foreach($this->aPlugins as $xPlugin)
        {
            $sHash .= $xPlugin->generateHash();
        }
        return md5($sHash);
    }

    /**
     * Check if the current request can be processed
     *
     * Calls each of the request plugins and determines if the current request can be processed by one of them.
     * If no processor identifies the current request, then the request must be for the initial page load.
     *
     * @return boolean
     */
    public function canProcessRequest()
    {
        foreach($this->aRequestPlugins as $xPlugin)
        {
            if($xPlugin->getName() != Jaxon::FILE_UPLOAD && $xPlugin->canProcessRequest())
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Process the current request
     *
     * Calls each of the request plugins to request that they process the current request.
     * If any plugin processes the request, it will return true.
     *
     * @return boolean
     */
    public function processRequest()
    {
        $xUploadPlugin = $this->getRequestPlugin(Jaxon::FILE_UPLOAD);
        foreach($this->aRequestPlugins as $xPlugin)
        {
            if($xPlugin->getName() != Jaxon::FILE_UPLOAD && $xPlugin->canProcessRequest())
            {
                // Process uploaded files
                if($xUploadPlugin != null)
                {
                    $xUploadPlugin->processRequest();
                }
                // Process the request
                return $xPlugin->processRequest();
            }
        }
        // Todo: throw an exception
        return false;
    }
    
    /**
     * Register a function, event or callable object
     *
     * Call each of the request plugins and give them the opportunity to handle the
     * registration of the specified function, event or callable object.
     *
     * @param array         $aArgs                The registration data
     *
     * @return mixed
     */
    public function register($aArgs)
    {
        foreach($this->aRequestPlugins as $xPlugin)
        {
            $mResult = $xPlugin->register($aArgs);
            if($mResult instanceof \Jaxon\Request\Request || is_array($mResult) || $mResult === true)
            {
                return $mResult;
            }
        }
        throw new \Jaxon\Exception\Error($this->trans('errors.register.method', array('args' => print_r($aArgs, true))));
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
    public function addClassDir($sDirectory, $sNamespace = '', $sSeparator = '.', array $aProtected = array())
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
        array $aProtected = array(), array $aOptions = array())
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
            $aOptions['*'] = array();
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
    public function registerClasses(array $aOptions = array())
    {
        $sDS = DIRECTORY_SEPARATOR;
        // Change the keys in $aOptions to have "\" as separator
        $aNewOptions = array();
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
    public function registerClass($sClassName, array $aOptions = array())
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
     * Find a user registered callable object by class name
     *
     * @param string        $sClassName            The class name of the callable object
     *
     * @return object
     */
    public function getRegisteredObject($sClassName)
    {
        $xObject = null; // The user registered object
        $xPlugin = $this->getRequestPlugin('CallableObject'); // The CallableObject plugin
        if(($xPlugin))
        {
            $xObject = $xPlugin->getRegisteredObject($sClassName);
        }
        return $xObject;
    }

    /**
     * Get the base URI of the Jaxon library javascript files
     *
     * @return string
     */
    private function getJsLibUri()
    {
        if(!$this->hasOption('js.lib.uri'))
        {
            // return 'https://cdn.jsdelivr.net/jaxon/1.2.0/';
            return 'https://cdn.jsdelivr.net/gh/jaxon-php/jaxon-js@2.0/dist/';
        }
        // Todo: check the validity of the URI
        return rtrim($this->getOption('js.lib.uri'), '/') . '/';
    }
    
    /**
     * Get the extension of the Jaxon library javascript files
     *
     * The returned string is '.min.js' if the files are minified.
     *
     * @return string
     */
    private function getJsLibExt()
    {
        // $jsDelivrUri = 'https://cdn.jsdelivr.net';
        // $nLen = strlen($jsDelivrUri);
        // The jsDelivr CDN only hosts minified files
        // if(($this->getOption('js.app.minify')) || substr($this->getJsLibUri(), 0, $nLen) == $jsDelivrUri)
        // Starting from version 2.0.0 of the js lib, the jsDelivr CDN also hosts non minified files.
        if(($this->getOption('js.app.minify')))
        {
            return '.min.js';
        }
        return '.js';
    }

    /**
     * Check if the javascript code generated by Jaxon can be exported to an external file
     *
     * @return boolean
     */
    public function canExportJavascript()
    {
        // Check config options
        // - The js.app.extern option must be set to true
        // - The js.app.uri and js.app.dir options must be set to non null values
        if(!$this->getOption('js.app.extern') ||
            !$this->getOption('js.app.uri') ||
            !$this->getOption('js.app.dir'))
        {
            return false;
        }
        // Check dir access
        // - The js.app.dir must be writable
        $sJsAppDir = $this->getOption('js.app.dir');
        if(!is_dir($sJsAppDir) || !is_writable($sJsAppDir))
        {
            return false;
        }
        return true;
    }

    /**
     * Set the cache directory for the template engine
     *
     * @return void
     */
    private function setTemplateCacheDir()
    {
        if($this->hasOption('core.template.cache_dir'))
        {
            $this->setCacheDir($this->getOption('core.template.cache_dir'));
        }
    }

    /**
     * Get the HTML tags to include Jaxon javascript files into the page
     *
     * @return string
     */
    public function getJs()
    {
        $sJsLibUri = $this->getJsLibUri();
        $sJsLibExt = $this->getJsLibExt();
        $sJsCoreUrl = $sJsLibUri . 'jaxon.core' . $sJsLibExt;
        $sJsDebugUrl = $sJsLibUri . 'jaxon.debug' . $sJsLibExt;
        // $sJsVerboseUrl = $sJsLibUri . 'jaxon.verbose' . $sJsLibExt;
        $sJsLanguageUrl = $sJsLibUri . 'lang/jaxon.' . $this->getOption('core.language') . $sJsLibExt;

        // Add component files to the javascript file array;
        $aJsFiles = array($sJsCoreUrl);
        if($this->getOption('core.debug.on'))
        {
            $aJsFiles[] = $sJsDebugUrl;
            $aJsFiles[] = $sJsLanguageUrl;
            /*if($this->getOption('core.debug.verbose'))
            {
                $aJsFiles[] = $sJsVerboseUrl;
            }*/
        }

        // Set the template engine cache dir
        $this->setTemplateCacheDir();
        $sCode = $this->render('jaxon::plugins/includes.js', array(
            'sJsOptions' => $this->getOption('js.app.options'),
            'aUrls' => $aJsFiles,
        ));
        foreach($this->aResponsePlugins as $xPlugin)
        {
            $sCode .= rtrim($xPlugin->getJs(), " \n") . "\n";
        }
        return $sCode;
    }

    /**
     * Get the HTML tags to include Jaxon CSS code and files into the page
     *
     * @return string
     */
    public function getCss()
    {
        // Set the template engine cache dir
        $this->setTemplateCacheDir();

        $sCode = '';
        foreach($this->aResponsePlugins as $xPlugin)
        {
            $sCode .= rtrim($xPlugin->getCss(), " \n") . "\n";
        }
        return $sCode;
    }

    /**
     * Get the correspondances between previous and current config options
     *
     * They are used to keep the deprecated config options working.
     * They will be removed when the deprecated options will lot be supported anymore.
     *
     * @return array
     */
    private function getOptionVars()
    {
        return array(
            'sResponseType'             => self::RESPONSE_TYPE,
            'sVersion'                  => $this->getOption('core.version'),
            'sLanguage'                 => $this->getOption('core.language'),
            'bLanguage'                 => $this->hasOption('core.language') ? true : false,
            'sRequestURI'               => $this->getOption('core.request.uri'),
            'sDefaultMode'              => $this->getOption('core.request.mode'),
            'sDefaultMethod'            => $this->getOption('core.request.method'),
            'sCsrfMetaName'             => $this->getOption('core.request.csrf_meta'),
            'bDebug'                    => $this->getOption('core.debug.on'),
            'bVerboseDebug'             => $this->getOption('core.debug.verbose'),
            'sDebugOutputID'            => $this->getOption('core.debug.output_id'),
            'nResponseQueueSize'        => $this->getOption('js.lib.queue_size'),
            'sStatusMessages'           => $this->getOption('js.lib.show_status') ? 'true' : 'false',
            'sWaitCursor'               => $this->getOption('js.lib.show_cursor') ? 'true' : 'false',
            'sDefer'                    => $this->getOption('js.app.options'),
        );
    }

    /**
     * Get the javascript code for Jaxon client side configuration
     *
     * @return string
     */
    private function getConfigScript()
    {
        $aVars = $this->getOptionVars();
        $sYesScript = 'jaxon.ajax.response.process(command.response)';
        $sNoScript = 'jaxon.confirm.skip(command);jaxon.ajax.response.process(command.response)';
        $sConfirmScript = $this->getConfirm()->confirm('msg', $sYesScript, $sNoScript);
        $aVars['sConfirmScript'] = $this->render('jaxon::plugins/confirm.js', array('sConfirmScript' => $sConfirmScript));

        return $this->render('jaxon::plugins/config.js', $aVars);
    }

    /**
     * Get the javascript code to be run after page load
     *
     * Also call each of the response plugins giving them the opportunity
     * to output some javascript to the page being generated.
     *
     * @return string
     */
    private function getReadyScript()
    {
        // Print Jaxon config vars
        /*$sJsLibUri = $this->getJsLibUri();
        $sJsLibExt = $this->getJsLibExt();
        $sJsCoreUrl = $sJsLibUri . 'jaxon.core' . $sJsLibExt;
        $sJsDebugUrl = $sJsLibUri . 'jaxon.debug' . $sJsLibExt;
        $sJsVerboseUrl = $sJsLibUri . 'jaxon.verbose' . $sJsLibExt;
        $sJsLanguageUrl = $sJsLibUri . 'lang/jaxon.' . $this->getOption('core.language') . $sJsLibExt;

        $sJsCoreError = $this->trans('errors.component.load', array(
            'name' => 'jaxon',
            'url' => $sJsCoreUrl,
        ));
        $sJsDebugError = $this->trans('errors.component.load', array(
            'name' => 'jaxon.debug',
            'url' => $sJsDebugUrl,
        ));
        $sJsVerboseError = $this->trans('errors.component.load', array(
            'name' => 'jaxon.debug.verbose',
            'url' => $sJsVerboseUrl,
        ));
        $sJsLanguageError = $this->trans('errors.component.load', array(
            'name' => 'jaxon.debug.lang',
            'url' => $sJsLanguageUrl,
        ));*/

        $sPluginScript = '';
        foreach($this->aResponsePlugins as $xPlugin)
        {
            $sPluginScript .= "\n" . trim($xPlugin->getScript(), " \n");
        }

        /*$aVars = $this->getOptionVars();
        $aVars['sPluginScript'] = $sPluginScript;
        $aVars['sJsCoreError'] = $sJsCoreError;
        $aVars['sJsDebugError'] = $sJsDebugError;
        $aVars['sJsVerboseError'] = $sJsVerboseError;
        $aVars['sJsLanguageError'] = $sJsLanguageError;

        return $this->render('jaxon::plugins/ready.js', $aVars);*/
        return $this->render('jaxon::plugins/ready.js', ['sPluginScript' => $sPluginScript]);
    }

    /**
     * Get the javascript code to be sent to the browser
     *
     * Also call each of the request plugins giving them the opportunity
     * to output some javascript to the page being generated.
     * This is called only when the page is being loaded initially.
     * This is not called when processing a request.
     *
     * @return string
     */
    private function getAllScripts()
    {
        // Get the config and plugins scripts
        $sScript = $this->getConfigScript() . "\n" . $this->getReadyScript() . "\n";
        foreach($this->aRequestPlugins as $xPlugin)
        {
            $sScript .= "\n" . trim($xPlugin->getScript(), " \n");
        }
        return $sScript;
    }

    /**
     * Get the javascript code to be sent to the browser
     *
     * Also call each of the request plugins giving them the opportunity
     * to output some javascript to the page being generated.
     * This is called only when the page is being loaded initially.
     * This is not called when processing a request.
     *
     * @return string
     */
    public function getScript()
    {
        // Set the template engine cache dir
        $this->setTemplateCacheDir();

        if($this->canExportJavascript())
        {
            $sJsAppURI = rtrim($this->getOption('js.app.uri'), '/') . '/';
            $sJsAppDir = rtrim($this->getOption('js.app.dir'), '/') . '/';

            // The plugins scripts are written into the javascript app dir
            $sHash = $this->generateHash();
            $sOutFile = $sHash . '.js';
            $sMinFile = $sHash . '.min.js';
            if(!is_file($sJsAppDir . $sOutFile))
            {
                file_put_contents($sJsAppDir . $sOutFile, $this->getAllScripts());
            }
            if(($this->getOption('js.app.minify')) && !is_file($sJsAppDir . $sMinFile))
            {
                if(($this->minify($sJsAppDir . $sOutFile, $sJsAppDir . $sMinFile)))
                {
                    $sOutFile = $sMinFile;
                }
            }

            // The returned code loads the generated javascript file
            $sScript = $this->render('jaxon::plugins/include.js', array(
                'sJsOptions' => $this->getOption('js.app.options'),
                'sUrl' => $sJsAppURI . $sOutFile,
            ));
        }
        else
        {
            // The plugins scripts are wrapped with javascript tags
            $sScript = $this->render('jaxon::plugins/wrapper.js', array(
                'sJsOptions' => $this->getOption('js.app.options'),
                'sScript' => $this->getAllScripts(),
            ));
        }
        
        return $sScript;
    }

    /**
     * Find the specified response plugin by name and return a reference to it if one exists
     *
     * @param string        $sName                The name of the plugin
     *
     * @return \Jaxon\Plugin\Response
     */
    public function getResponsePlugin($sName)
    {
        if(array_key_exists($sName, $this->aResponsePlugins))
        {
            return $this->aResponsePlugins[$sName];
        }
        return null;
    }

    /**
     * Find the specified request plugin by name and return a reference to it if one exists
     *
     * @param string        $sName                The name of the plugin
     *
     * @return \Jaxon\Plugin\Request
     */
    public function getRequestPlugin($sName)
    {
        if(array_key_exists($sName, $this->aRequestPlugins))
        {
            return $this->aRequestPlugins[$sName];
        }
        return null;
    }
}
