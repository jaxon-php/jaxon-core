<?php

/**
 * CallableFunction.php - Jaxon user function plugin
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
use Jaxon\Request\Target;
use Jaxon\Request\Handler\Handler as RequestHandler;
use Jaxon\Response\Manager as ResponseManager;
use Jaxon\Utils\DI\Container;

class CallableFunction extends RequestPlugin
{
    use \Jaxon\Features\Validator;
    use \Jaxon\Features\Translator;

    /**
     * The DI container
     *
     * @var Container
     */
    protected $di;

    /**
     * The request handler
     *
     * @var RequestHandler
     */
    protected $xRequestHandler;

    /**
     * The response manager
     *
     * @var ResponseManager
     */
    protected $xResponseManager;

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

    /**
     * The constructor
     *
     * @param Container         $di
     * @param RequestHandler    $xRequestHandler
     * @param ResponseManager   $xResponseManager
     */
    public function __construct(Container $di, RequestHandler $xRequestHandler, ResponseManager $xResponseManager)
    {
        $this->di = $di;
        $this->xRequestHandler = $xRequestHandler;
        $this->xResponseManager = $xResponseManager;

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
     * @inheritDoc
     */
    public function getName()
    {
        return Jaxon::CALLABLE_FUNCTION;
    }

    /**
     * @inheritDoc
     */
    public function getTarget()
    {
        if(!$this->sRequestedFunction)
        {
            return null;
        }
        return Target::makeFunction($this->sRequestedFunction);
    }

    /**
     * Register a user defined function
     *
     * @param string        $sType              The type of request handler being registered
     * @param string        $sCallableFunction  The name of the function being registered
     * @param array|string  $aOptions           The associated options
     *
     * @return boolean
     */
    public function register($sType, $sCallableFunction, $aOptions)
    {
        $sType = trim($sType);
        if($sType != $this->getName())
        {
            return false;
        }

        if(!is_string($sCallableFunction))
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

        $sCallableFunction = trim($sCallableFunction);
        // Check if an alias is defined
        $sFunctionName = $sCallableFunction;
        foreach($aOptions as $sName => $sValue)
        {
            if($sName == 'alias')
            {
                $sFunctionName = $sValue;
                break;
            }
        }

        $this->aFunctions[$sFunctionName] = $aOptions;
        $this->di->registerCallableFunction($sFunctionName, $sCallableFunction, $aOptions);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getHash()
    {
        return md5(implode('', array_keys($this->aFunctions)));
    }

    /**
     * @inheritDoc
     */
    public function getScript()
    {
        $code = '';
        foreach(array_keys($this->aFunctions) as $sName)
        {
            $xFunction = $this->di->get($sName);
            $code .= $xFunction->getScript();
        }
        return $code;
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
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

        $xFunction = $this->di->get($this->sRequestedFunction);
        $aArgs = $this->xRequestHandler->processArguments();
        $xResponse = $xFunction->call($aArgs);
        if(($xResponse))
        {
            $this->xResponseManager->append($xResponse);
        }
        return true;
    }
}
