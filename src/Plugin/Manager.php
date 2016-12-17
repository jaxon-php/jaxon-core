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
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
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
    use \Jaxon\Utils\ContainerTrait;

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
     * Confirmation question for Jaxon requests
     *
     * @var Request\Interfaces\Confirm
     */
    private $xConfirm;

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

        // Set response type to JSON
        $this->sResponseType = 'JSON';

        // Confirmation question for Jaxon requests
        $this->xConfirm = new \Jaxon\Request\Support\Confirm();
    }

    /**
     * Use the Composer autoloader
     *
     * @return void
     */
    public function useComposerAutoloader()
    {
        $this->bAutoloadEnabled = true;
        $this->xAutoloader = require (__DIR__ . '/../../../../autoload.php');
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
     * Set the Jaxon request Confirmation
     *
     * @param \Jaxon\Request\Interfaces\Confirm        $xConfirm     The Jaxon request Confirmation
     *
     * @return void
     */
    public function setConfirm(\Jaxon\Request\Interfaces\Confirm $xConfirm)
    {
        $this->xConfirm = $xConfirm;
    }

    /**
     * Get the Jaxon request Confirmation
     *
     * @return \Jaxon\Request\Interfaces\Confirm
     */
    public function getConfirm()
    {
        return $this->xConfirm;
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
        $bIsConfirm = ($xPlugin instanceof \Jaxon\Request\Interfaces\Confirm);
        if($xPlugin instanceof Request)
        {
            // The name of a request plugin is used as key in the plugin table
            $this->aRequestPlugins[$xPlugin->getName()] = $xPlugin;
        }
        else if( $xPlugin instanceof Response)
        {
            // The name of a response plugin is used as key in the plugin table
            $this->aResponsePlugins[$xPlugin->getName()] = $xPlugin;
        }
        else if(!$bIsConfirm)
        {
            throw new \Jaxon\Exception\Error('errors.register.invalid', array('name' => get_class($xPlugin)));
        }
        // This plugin implements the Confirmation interface
        if($bIsConfirm)
        {
            $this->setConfirm($xPlugin);
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
        $sHash = '';
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
            if($xPlugin->canProcessRequest())
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
        foreach($this->aRequestPlugins as $xPlugin)
        {
            if($xPlugin->canProcessRequest())
            {
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
        throw new \Jaxon\Exception\Error('errors.register.method', array('args' => print_r($aArgs, true)));
    }

    /**
     * Add a path to the class directories
     *
     * @param string            $sDirectory             The path to the directory
     * @param string|null       $sNamespace             The associated namespace
     * @param array             $aExcluded              The functions that are not to be exported
     * @param string            $sSeparator             The character to use as separator in javascript class names
     *
     * @return boolean
     */
    public function addClassDir($sDirectory, $sNamespace = null, array $aExcluded = array(), $sSeparator = '.')
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
        if(!($sNamespace = trim($sNamespace)))
        {
            $sNamespace = null;
        }
        if(($sNamespace))
        {
            $sNamespace = trim($sNamespace, '\\');
            // If there is an autoloader, register the dir with PSR4 autoloading
            if(($this->xAutoloader))
            {
                $this->xAutoloader->setPsr4($sNamespace . '\\', $sDirectory);
            }
        }
        else if(($this->xAutoloader))
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
        $this->aClassDirs[] = array('path' => $sDirectory, 'namespace' => $sNamespace,
            'excluded' => $aExcluded, 'separator' => $sSeparator);
        return true;
    }

    /**
     * Register an instance of a given class from a file
     *
     * @param object            $xFile                  The PHP file containing the class
     * @param string            $sDir                   The path to the directory
     * @param string|null       $sNamespace             The associated namespace
     * @param array             $aExcluded              The functions that are not to be exported
     * @param string            $sSeparator             The character to use as separator in javascript class names
     *
     * @return void
     */
    protected function registerClassFromFile($xFile, $sDir, $sNamespace = null, array $aExcluded = array(), $sSeparator = '.')
    {
        // Get the corresponding class path and name
        $sClassPath = trim(substr($xFile->getPath(), strlen($sDir)), DIRECTORY_SEPARATOR);
        $sClassPath = str_replace(array(DIRECTORY_SEPARATOR), array($sSeparator), $sClassPath);
        $sClassName = $xFile->getBasename('.php');
        if(($sNamespace))
        {
            $sNamespace = trim($sNamespace, '\\');
            $sClassPath = str_replace(array('\\'), array($sSeparator), $sNamespace) . $sSeparator . $sClassPath;
            $sClassPath = rtrim($sClassPath, $sSeparator);
            $sClassName = '\\' . str_replace(array($sSeparator), array('\\'), $sClassPath) . '\\' . $sClassName;
        }
        // Require the file only if autoload is enabled but there is no autoloader
        if(($this->bAutoloadEnabled) && !($this->xAutoloader))
        {
            require_once($xFile->getPathname());
        }
        // Create and register an instance of the class
        $xCallableObject = new $sClassName;
        $aOptions = array('*' => array('separator' => $sSeparator));
        if(($sNamespace))
        {
            $aOptions['*']['namespace'] = $sNamespace;
        }
        if(($sClassPath))
        {
            $aOptions['*']['classpath'] = $sClassPath;
        }
        // Filter excluded methods
        $aExcluded = array_filter($aExcluded, function($sName){return is_string($sName);});
        if(count($aExcluded) > 0)
        {
            $aOptions['*']['excluded'] = $aExcluded;
        }
        $this->register(array(Jaxon::CALLABLE_OBJECT, $xCallableObject, $aOptions));
    }

    /**
     * Register callable objects from all class directories
     *
     * @return void
     */
    public function registerClasses()
    {
        foreach($this->aClassDirs as $sClassDir)
        {
            $itDir = new RecursiveDirectoryIterator($sClassDir['path']);
            $itFile = new RecursiveIteratorIterator($itDir);
            // Iterate on dir content
            foreach($itFile as $xFile)
            {
                // skip everything except PHP files
                if(!$xFile->isFile() || $xFile->getExtension() != 'php')
                {
                    continue;
                }
                $this->registerClassFromFile($xFile, $sClassDir['path'],
                    $sClassDir['namespace'], $sClassDir['excluded'], $sClassDir['separator']);
            }
        }
    }

    /**
     * Register an instance of a given class
     *
     * @param string            $sClassName             The name of the class to be registered
     * @param array             $aExcluded              The functions that are not to be exported
     *
     * @return bool
     */
    public function registerClass($sClassName, array $aExcluded = array())
    {
        if(!($sInitialClassName = trim($sClassName)))
        {
            return false;
        }
        foreach($this->aClassDirs as $aClassDir)
        {
            // Get the separator
            $sSeparator = $aClassDir['separator'];
            // Replace / and \ with dots, and trim the string
            $sClassName = trim(str_replace(array('\\', '/'), array($sSeparator, $sSeparator), $sInitialClassName), $sSeparator);
            $sClassPath = '';
            if(($nLastDotPosition = strrpos($sClassName, $sSeparator)) !== false)
            {
                $sClassPath = substr($sClassName, 0, $nLastDotPosition);
                $sClassName = substr($sClassName, $nLastDotPosition + 1);
            }
            $sClassFile = str_replace(array($sSeparator), array(DIRECTORY_SEPARATOR), $sClassPath) .
                DIRECTORY_SEPARATOR . $sClassName . '.php';
            // Get the namespace
            $sNamespace = $aClassDir['namespace'];
            $nLen = strlen($sNamespace);
            $bRegister = false;
            // Check if the class belongs to the namespace
            if(($sNamespace) && substr($sClassPath, 0, $nLen) == str_replace(array('\\'), array($sSeparator), $sNamespace))
            {
                $sClassFile = $aClassDir['path'] . DIRECTORY_SEPARATOR . substr($sClassFile, $nLen);
                $bRegister = true;
            }
            else if(!($sNamespace))
            {
                $sClassFile = $aClassDir['path'] . DIRECTORY_SEPARATOR . $sClassFile;
                $bRegister = true;
            }
            if($bRegister && is_file($sClassFile))
            {
                $this->registerClassFromFile(new \SplFileInfo($sClassFile), $aClassDir['path'], $sNamespace, $aExcluded, $sSeparator);
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
    public function getJsLibUri()
    {
        if(!$this->hasOption('js.lib.uri'))
        {
            return 'https://cdn.jsdelivr.net/jaxon/1.0.0/';
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
        $jsDelivrUri = 'https://cdn.jsdelivr.net';
        $nLen = strlen($jsDelivrUri);
        // The jsDelivr CDN only hosts minified files
        if(($this->getOption('js.app.minify')) || substr($this->getJsLibUri(), 0, $nLen) == $jsDelivrUri)
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
        $sJsReadyUrl = $sJsLibUri . 'jaxon.ready' . $sJsLibExt;
        $sJsDebugUrl = $sJsLibUri . 'jaxon.debug' . $sJsLibExt;
        $sJsVerboseUrl = $sJsLibUri . 'jaxon.verbose' . $sJsLibExt;
        $sJsLanguageUrl = $sJsLibUri . 'lang/jaxon.' . $this->getOption('core.language') . $sJsLibExt;

        // Add component files to the javascript file array;
        $aJsFiles = array($sJsCoreUrl, $sJsReadyUrl);
        if($this->getOption('core.debug.on'))
        {
            $aJsFiles[] = $sJsDebugUrl;
            $aJsFiles[] = $sJsLanguageUrl;
            if($this->getOption('core.debug.verbose'))
            {
                $aJsFiles[] = $sJsVerboseUrl;
            }
        }

        // Set the template engine cache dir
        $this->setTemplateCacheDir();
        $sCode = $this->render('plugins/includes.js.tpl', array(
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
            'sResponseType'             => $this->sResponseType,
            'sVersion'                  => $this->getOption('core.version'),
            'sLanguage'                 => $this->getOption('core.language'),
            'bLanguage'                 => $this->hasOption('core.language') ? true : false,
            'sRequestURI'               => $this->getOption('core.request.uri'),
            'sDefaultMode'              => $this->getOption('core.request.mode'),
            'sDefaultMethod'            => $this->getOption('core.request.method'),
            'sCsrfMetaName'             => $this->getOption('core.request.csrf_meta'),
            'bDebug'                    => $this->getOption('core.debug.on'),
            'bVerboseDebug'             => $this->getOption('core.debug.verbose'),
            'sDebugOutputID'            => $this->getOption('js.lib.output_id'),
            'nResponseQueueSize'        => $this->getOption('js.lib.queue_size'),
            'sStatusMessages'           => $this->getOption('js.lib.show_status') ? 'true' : 'false',
            'sWaitCursor'               => $this->getOption('js.lib.show_cursor') ? 'true' : 'false',
            'nScriptLoadTimeout'        => $this->getOption('js.lib.load_timeout'),
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
        return $this->render('plugins/config.js.tpl', $aVars);
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
        $sJsLibUri = $this->getJsLibUri();
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
        ));

        $sPluginScript = '';
        foreach($this->aResponsePlugins as $xPlugin)
        {
            $sPluginScript .= "\n" . trim($xPlugin->getScript(), " \n");
        }

        $aVars = $this->getOptionVars();
        $aVars['sPluginScript'] = $sPluginScript;
        $aVars['sJsCoreError'] = $sJsCoreError;
        $aVars['sJsDebugError'] = $sJsDebugError;
        $aVars['sJsVerboseError'] = $sJsVerboseError;
        $aVars['sJsLanguageError'] = $sJsLanguageError;

        return $this->render('plugins/ready.js.tpl', $aVars);
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

        // Get the config and plugins scripts
        $sScript = $this->getConfigScript() . "\n" . $this->getReadyScript() . "\n";
        foreach($this->aRequestPlugins as $xPlugin)
        {
            $sScript .= "\n" . trim($xPlugin->getScript(), " \n");
        }
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
                file_put_contents($sJsAppDir . $sOutFile, $sScript);
            }
            if(($this->getOption('js.app.minify')))
            {
                if(($this->minify($sJsAppDir . $sOutFile, $sJsAppDir . $sMinFile)))
                {
                    $sOutFile = $sMinFile;
                }
            }

            // The returned code loads the generated javascript file
            $sScript = $this->render('plugins/include.js.tpl', array(
                'sJsOptions' => $this->getOption('js.app.options'),
                'sUrl' => $sJsAppURI . $sOutFile,
            ));
        }
        else
        {
            // The plugins scripts are wrapped with javascript tags
            $sScript = $this->render('plugins/wrapper.js.tpl', array(
                'sJsOptions' => $this->getOption('js.app.options'),
                'sScript' => $sScript,
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
