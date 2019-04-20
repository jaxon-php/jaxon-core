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
use Jaxon\Request\Manager as RequestManager;

class UserFunction extends RequestPlugin
{
    use \Jaxon\Utils\Traits\Manager;
    use \Jaxon\Utils\Traits\Validator;
    use \Jaxon\Utils\Traits\Translator;

    /**
     * The registered user functions names
     *
     * @var array
     */
    protected $aFunctions;

    /**
     * The name of the function that is being requested (during the request processing phase)
     *
     * @var string
     */
    protected $sRequestedFunction;

    public function __construct()
    {
        $this->aFunctions = [];

        $this->sRequestedFunction = null;

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
     * Register a user defined function
     *
     * @param array         $aArgs                An array containing the function specification
     *
     * @return \Jaxon\Request\Request
     */
    public function register($aArgs)
    {
        if(count($aArgs) < 2)
        {
            return false;
        }

        $sType = $aArgs[0];
        if($sType != Jaxon::USER_FUNCTION)
        {
            return false;
        }

        $sUserFunction = $aArgs[1];
        if(!is_string($sUserFunction))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.functions.invalid-declaration'));
        }

        $this->aFunctions[] = $sUserFunction;
        $xOptions = count($aArgs) > 2 ? $aArgs[2] : [];

        jaxon()->di()->set($sUserFunction, function() use($sUserFunction, $xOptions) {
            $xUserFunction = new \Jaxon\Request\Support\UserFunction($sUserFunction);

            if(is_array($xOptions))
            {
                foreach($xOptions as $sName => $sValue)
                {
                    $xUserFunction->configure($sName, $sValue);
                }
            }
            else
            {
                $xUserFunction->configure('include', $xOptions);
            }

            return $xUserFunction->generateRequest();
        });
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
        $code = '';
        foreach($this->aFunctions as $sName)
        {
            $xFunction = jaxon()->di()->get($sName);
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

        if(!key_exists($this->sRequestedFunction, $this->aFunctions))
        {
            // Unable to find the requested function
            throw new \Jaxon\Exception\Error($this->trans('errors.functions.invalid',
                ['name' => $this->sRequestedFunction]));
        }

        $xFunction = jaxon()->di()->get($this->sRequestedFunction);
        $aArgs = $this->getRequestManager()->process();
        $xFunction->call($aArgs);
        return true;
    }
}
