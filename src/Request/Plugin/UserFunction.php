<?php

/**
 * UserFunction.php - Xajax user function plugin
 *
 * This class registers user defined functions, generates client side javascript code,
 * and calls them on user request
 *
 * @package xajax-core
 * @author Jared White
 * @author J. Max Wilson
 * @author Joseph Woolley
 * @author Steffen Konerow
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
 * @copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/lagdo/xajax-core
 */

namespace Xajax\Request\Plugin;

use Xajax\Xajax;
use Xajax\Plugin\Request as RequestPlugin;
use Xajax\Request\Manager as RequestManager;

class UserFunction extends RequestPlugin
{
    use \Xajax\Utils\ContainerTrait;

    /**
     * The registered user functions
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
        $this->aFunctions = array();

        $this->sRequestedFunction = null;
        
        if(isset($_GET['xjxfun']))
        {
            $this->sRequestedFunction = $_GET['xjxfun'];
        }
        if(isset($_POST['xjxfun']))
        {
            $this->sRequestedFunction = $_POST['xjxfun'];
        }
    }

    /**
     * Return the name of this plugin
     *
     * @return string
     */
    public function getName()
    {
        return 'UserFunction';
    }

    /**
     * Register a user defined function
     *
     * @param array         $aArgs                An array containing the function specification
     *
     * @return \Xajax\Request\Request
     */
    public function register($aArgs)
    {
        if(count($aArgs) > 1)
        {
            $sType = $aArgs[0];

            if($sType == Xajax::USER_FUNCTION)
            {
                $xUserFunction = $aArgs[1];

                if(!($xUserFunction instanceof \Xajax\Request\Support\UserFunction))
                    $xUserFunction = new \Xajax\Request\Support\UserFunction($xUserFunction);

                if(count($aArgs) > 2)
                {
                    if(is_array($aArgs[2]))
                    {
                        foreach($aArgs[2] as $sName => $sValue)
                        {
                            $xUserFunction->configure($sName, $sValue);
                        }
                    }
                    else
                    {
                        $xUserFunction->configure('include', $aArgs[2]);
                    }
                }
                $this->aFunctions[$xUserFunction->getName()] = $xUserFunction;

                return $xUserFunction->generateRequest();
            }
        }

        return false;
    }

    /**
     * Generate a hash for the registered user functions
     *
     * @return string
     */
    public function generateHash()
    {
        $sHash = '';
        foreach($this->aFunctions as $xFunction)
        {
            $sHash .= $xFunction->getName();
        }
        return md5($sHash);
    }

    /**
     * Generate client side javascript code for the registered user functions
     *
     * @return string
     */
    public function getScript()
    {
        $code = '';
        foreach($this->aFunctions as $xFunction)
        {
            $code .= $xFunction->getScript();
        }
        return $code;
    }

    /**
     * Check if this plugin can process the incoming Xajax request
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
     * Process the incoming Xajax request
     *
     * @return boolean
     */
    public function processRequest()
    {
        if(!$this->canProcessRequest())
            return false;

        $aArgs = RequestManager::getInstance()->process();

        if(array_key_exists($this->sRequestedFunction, $this->aFunctions))
        {
            $this->aFunctions[$this->sRequestedFunction]->call($aArgs);
            return true;
        }
        // Unable to find the requested function
        throw new \Xajax\Exception\Error('errors.functions.invalid', array('name' => $this->sRequestedFunction));
    }
}
