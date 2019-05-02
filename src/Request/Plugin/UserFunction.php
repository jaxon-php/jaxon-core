<?php

/**
 * UserFunction.php - Jaxon user function plugin
 *
 * This class registers user defined functions, generates client side javascript code,
 * and calls them on user request
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

class UserFunction extends RequestPlugin
{
    use \Jaxon\Features\Validator;
    use \Jaxon\Features\Translator;

    /**
     * The registered user functions names
     *
     * @var array
     */
    protected $aFunctions = [];

    /**
     * The name of the function that is being requested (during the request processing phase)
     *
     * @var string
     */
    protected $sRequestedFunction = null;

    public function __construct()
    {
        if(isset($_GET['jxnfun']))
        {
            $this->sRequestedFunction = $_GET['jxnfun'];
        }
        if(isset($_POST['jxnfun']))
        {
            $this->sRequestedFunction = $_POST['jxnfun'];
        }
    }

    /**
     * Return the name of this plugin
     *
     * @return string
     */
    public function getName()
    {
        return Jaxon::USER_FUNCTION;
    }

    /**
     * Return the name of target function
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->sRequestedFunction;
    }

    /**
     * Register a user defined function
     *
     * @param string        $sType          The type of request handler being registered
     * @param string        $sUserFunction  The name of the function being registered
     * @param array|string  $aOptions       The associated options
     *
     * @return \Jaxon\Request\Request
     */
    public function register($sType, $sUserFunction, $aOptions)
    {
        if($sType != $this->getName())
        {
            return false;
        }

        if(!is_string($sUserFunction))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.functions.invalid-declaration'));
        }

        if(is_string($aOptions))
        {
            $aOptions = ['include' => $aOptions];
        }
        if(!is_array($aOptions))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.functions.invalid-declaration'));
        }

        // Check if an alias is defined
        $sFunctionName = $sUserFunction;
        foreach($aOptions as $sName => $sValue)
        {
            if($sName == 'alias')
            {
                $sFunctionName = $sValue;
                break;
            }
        }

        $this->aFunctions[$sFunctionName] = $aOptions;
        jaxon()->di()->set($sFunctionName, function () use ($sFunctionName, $sUserFunction) {
            $xUserFunction = new \Jaxon\Request\Support\UserFunction($sUserFunction);

            $aOptions = $this->aFunctions[$sFunctionName];
            foreach($aOptions as $sName => $sValue)
            {
                $xUserFunction->configure($sName, $sValue);
            }

            return $xUserFunction;
        });

        return true;
    }

    /**
     * Generate a hash for the registered user functions
     *
     * @return string
     */
    public function generateHash()
    {
        return md5(implode('', $this->aFunctions));
    }

    /**
     * Generate client side javascript code for the registered user functions
     *
     * @return string
     */
    public function getScript()
    {
        $di = jaxon()->di();
        $code = '';
        foreach(array_keys($this->aFunctions) as $sName)
        {
            $xFunction = $di->get($sName);
            $code .= $xFunction->getScript();
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
        // Check the validity of the function name
        if(($this->sRequestedFunction) && !$this->validateFunction($this->sRequestedFunction))
        {
            $this->sRequestedFunction = null;
        }
        return ($this->sRequestedFunction != null);
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

        // Security check: make sure the requested function was registered.
        if(!key_exists($this->sRequestedFunction, $this->aFunctions))
        {
            // Unable to find the requested function
            throw new \Jaxon\Exception\Error($this->trans('errors.functions.invalid',
                ['name' => $this->sRequestedFunction]));
        }

        $di = jaxon()->di();
        $xFunction = $di->get($this->sRequestedFunction);
        $aArgs = $di->getRequestHandler()->processArguments();
        $xResponse = $xFunction->call($aArgs);
        if(($xResponse))
        {
            $di->getResponseManager()->append($xResponse);
        }
        return true;
    }
}
