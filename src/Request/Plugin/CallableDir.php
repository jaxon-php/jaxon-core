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
     * The registered callable objects
     *
     * @var array
     */
    protected $aCallableDirs = [];

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
        $this->aCallableDirs[] = $sDirectory;

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

        $aProtected = key_exists('protected', $aOptions) ? $aOptions['protected'] : [];
        if(!is_array($aProtected))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.objects.invalid-declaration'));
        }

        $sSeparator = key_exists('separator', $aOptions) ? $aOptions['separator'] : '.';
        // Only '.' and '_' are allowed to be used as separator. Any other value is ignored and '.' is used instead.
        if(($sSeparator = trim($sSeparator)) != '_')
        {
            $sSeparator = '.';
        }

        $sNamespace = key_exists('namespace', $aOptions) ? $aOptions['namespace'] : '';
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
                $this->xAutoloader->addClassMap([$xFile->getBasename('.php') => $xFile->getPathname()]);
            }
        }

        $this->aCallableDirs[$sDirectory] = [
            'namespace' => $sNamespace,
            'separator' => $sSeparator,
            'protected' => $aProtected,
        ];

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
    protected function _registerClass($xFile, $sDirectory, $sNamespace = '', $sSeparator = '.',
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

        $this->callableClassPlugin->register(Jaxon::CALLABLE_CLASS, $sClassName, $aOptions);
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
        // Get the callable class plugin
        $this->callableClassPlugin = $this->getPluginManager()->getRequestPlugin(Jaxon::CALLABLE_CLASS);

        $sDS = DIRECTORY_SEPARATOR;
        // Change the keys in $aOptions to have "\" as separator
        $aNewOptions = [];
        foreach($aOptions as $key => $aOption)
        {
            $key = trim(str_replace(['.', '_'], ['\\', '\\'], $key), ' \\');
            $aNewOptions[$key] = $aOption;
        }

        foreach($this->aCallableDirs as $sDirectory => $aDirOptions)
        {
            // Get the namespace
            $sNamespace = $aDirOptions['namespace'];

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

                $this->_registerClass($xFile, $sDirectory, $sNamespace,
                    $aDirOptions['separator'], $aDirOptions['protected'], $aClassOptions);
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
        if(count($this->aCallableDirs) == 0)
        {
            return '';
        }
        $sHash = '';
        foreach($this->aCallableDirs as $sDirectory => $aDirOptions)
        {
            $sHash .= $sDirectory . $aDirOptions['namespace'] . $aDirOptions['separator'];
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
        $code = '';
        return $code;
    }

    /**
     * Check if this plugin can process the incoming Jaxon request
     *
     * @return boolean
     */
    public function canProcessRequest()
    {
        // This plugin never processes any request
        return false;
    }

    /**
     * Process the incoming Jaxon request
     *
     * @return boolean
     */
    public function processRequest()
    {
        return false;
    }
}
