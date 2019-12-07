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
use Jaxon\Request\Support\CallableRegistry;

class CallableDir extends RequestPlugin
{
    use \Jaxon\Features\Translator;

    /**
     * The callable registrar
     *
     * @var CallableRegistry
     */
    protected $xRegistry;

    /**
     * The class constructor
     *
     * @param CallableRegistry        $xRegistry
     */
    public function __construct(CallableRegistry $xRegistry)
    {
        $this->xRegistry = $xRegistry;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return Jaxon::CALLABLE_DIR;
    }

    /**
     * Check the directory
     *
     * @param string        $sDirectory     The path of teh directory being registered
     *
     * @return string
     * @throws \Jaxon\Exception\Error
     */
    private function checkDirectory($sDirectory)
    {
        if(!is_string($sDirectory))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.objects.invalid-declaration'));
        }
        $sDirectory = rtrim(trim($sDirectory), '/\\');
        if(!is_dir($sDirectory))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.objects.invalid-declaration'));
        }
        return realpath($sDirectory);
    }

    /**
     * Check the options
     *
     * @param array|string  $aOptions       The associated options
     *
     * @return array
     * @throws \Jaxon\Exception\Error
     */
    private function checkOptions($aOptions)
    {
        if(is_string($aOptions))
        {
            $aOptions = ['namespace' => $aOptions];
        }
        if(!is_array($aOptions))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.objects.invalid-declaration'));
        }

        // Change the keys in $aOptions to have "\" as separator
        $_aOptions = [];
        foreach($aOptions as $sName => $aOption)
        {
            $sName = trim(str_replace('.', '\\', $sName), ' \\');
            $_aOptions[$sName] = $aOption;
        }
        return $_aOptions;
    }

    /**
     * Register a callable class
     *
     * @param string        $sType          The type of request handler being registered
     * @param string        $sDirectory     The path of the directory being registered
     * @param array|string  $aOptions       The associated options
     *
     * @return boolean
     */
    public function register($sType, $sDirectory, $aOptions)
    {
        $sType = trim($sType);
        if($sType != $this->getName())
        {
            return false;
        }

        $sDirectory = $this->checkDirectory($sDirectory);

        $aOptions = $this->checkOptions($aOptions);
        $aOptions['directory'] = $sDirectory;

        $sNamespace = key_exists('namespace', $aOptions) ? $aOptions['namespace'] : '';
        if(!($sNamespace = trim($sNamespace, ' \\')))
        {
            $sNamespace = '';
        }

        if(($sNamespace))
        {
            $this->xRegistry->addNamespace($sNamespace, $aOptions);
        }
        else
        {
            $this->xRegistry->addDirectory($sDirectory, $aOptions);
        }

        return true;
    }

    /**
     * @inheritDoc
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
