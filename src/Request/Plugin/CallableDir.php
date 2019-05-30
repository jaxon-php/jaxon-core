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
use Jaxon\Request\Support\CallableRepository;

class CallableDir extends RequestPlugin
{
    use \Jaxon\Features\Translator;

    /**
     * The callable repository
     *
     * @var CallableRepository
     */
    protected $xRepository = null;

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
     * Return the name of this plugin
     *
     * @return string
     */
    public function getName()
    {
        return Jaxon::CALLABLE_DIR;
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
        if(is_string($aOptions))
        {
            $aOptions = ['namespace' => $aOptions];
        }
        if(!is_array($aOptions))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.objects.invalid-declaration'));
        }

        $sDirectory = rtrim(trim($sDirectory), DIRECTORY_SEPARATOR);
        if(!is_dir($sDirectory))
        {
            return false;
        }
        $aOptions['directory'] = realpath($sDirectory);

        $sNamespace = key_exists('namespace', $aOptions) ? $aOptions['namespace'] : '';
        if(!($sNamespace = trim($sNamespace, ' \\')))
        {
            $sNamespace = '';
        }

        // $sSeparator = key_exists('separator', $aOptions) ? $aOptions['separator'] : '.';
        // // Only '.' and '_' are allowed to be used as separator. Any other value is ignored and '.' is used instead.
        // if(($sSeparator = trim($sSeparator)) != '_')
        // {
        //     $sSeparator = '.';
        // }

        // Set the autoload option default value
        if(!key_exists('autoload', $aOptions))
        {
            $aOptions['autoload'] = false;
        }

        // Change the keys in $aOptions to have "\" as separator
        $_aOptions = [];
        foreach($aOptions as $sName => $aOption)
        {
            $sName = trim(str_replace('.', '\\', $sName), ' \\');
            $_aOptions[$sName] = $aOption;
        }
        $aOptions = $_aOptions;

        if(($sNamespace))
        {
            // Register the dir with PSR4 autoloading
            if(($aOptions['autoload']))
            {
                $this->xAutoloader->setPsr4($sNamespace . '\\', $sDirectory);
            }

            $this->xRepository->addNamespace($sNamespace, $aOptions);
        }
        else
        {
             // Use underscore as separator, so there's no need to deal with namespace
            // when generating javascript code.
            $aOptions['separator'] = '_';

            $this->xRepository->addDirectory($sDirectory, $aOptions);
        }

        return true;
    }

    /**
     * Generate a hash for the registered callable objects
     *
     * @return string
     */
    public function generateHash()
    {
        return '';
    }

    /**
     * Generate client side javascript code for the registered callable objects
     *
     * @return string
     */
    public function getScript()
    {
        return '';
    }

    /**
     * Check if this plugin can process the incoming Jaxon request
     *
     * @return boolean
     */
    public function canProcessRequest()
    {
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
