<?php

/**
 * UserFunction.php - Jaxon user function
 *
 * This class stores a reference to a user defined function which can be called from the client via an Jaxon request
 *
 * The function specification passed to the constructor of this class in one of the following formats:
 * - a three element array:
 *     (string) Alternate function name: when a method of a class has the same name as
 *              another function in the system, you can provide an alias to help avoid collisions.
 *     (object or class name) Class: the name of the class or an instance of the object which contains
 *              the function to be called.
 *     (string) Method:  the name of the method that will be called.
 * - a two element array:
 *     (object or class name) Class: the name of the class or an instance of the object which contains
 *              the function to be called.
 *     (string) Method:  the name of the method that will be called.
 * - a string:
 *     the name of the function that is available at global scope (not in a class).
 *
 * Examples:
 *      $myFunction = array('alias', 'myClass', 'myMethod');
 *      $myFunction = array('alias', &$myObject, 'myMethod');
 *      $myFunction = array('myClass', 'myMethod');
 *      $myFunction = array(&$myObject, 'myMethod');
 *      $myFunction = 'myFunction';
 *
 *      $myUserFunction = new UserFunction($myFunction);
 *      $jaxon->register(Jaxon::USER_FUNCTION, $myUserFunction);
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

namespace Jaxon\Request\Support;

use Jaxon\Jaxon;
use Jaxon\Request\Request;

class UserFunction
{
    use \Jaxon\Utils\Traits\Config;
    use \Jaxon\Utils\Traits\Manager;
    use \Jaxon\Utils\Traits\Template;
    use \Jaxon\Utils\Traits\Translator;

    /**
     * An alias to use for this function
     *
     * This is useful when you want to call the same jaxon enabled function with
     * a different set of call options from what was already registered.
     *
     * @var string
     */
    private $sAlias;

    /**
     * A string or an array which defines the function to be registered
     *
     * @var string
     */
    private $sUserFunction;

    /**
     * The path and file name of the include file where the function is defined
     *
     * @var string
     */
    private $sInclude;

    /**
     * An associative array containing call options that will be sent
     * to the browser curing client script generation
     *
     * @var array
     */
    private $aConfiguration;

    public function __construct($sUserFunction)
    {
        $this->aConfiguration = array();
        $this->sAlias = '';
        if(is_array($sUserFunction))
        {
            if(count($sUserFunction) != 2 && count($sUserFunction) != 3)
            {
                throw new \Jaxon\Exception\Error($this->trans('errors.functions.invalid-declaration'));
            }
            if(count($sUserFunction) == 3)
            {
                $this->sAlias = $sUserFunction[0];
                $this->sUserFunction = array_slice($sUserFunction, 1);
            }
            else
            {
                $this->sUserFunction = $sUserFunction;
            }
        }
        elseif(is_string($sUserFunction))
        {
            $this->sUserFunction = $sUserFunction;
        }
        else
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.functions.invalid-declaration'));
        }
    }

    /**
     * Get the name of the function being referenced
     *
     * @return string
     */
    public function getName()
    {
        // Do not use sAlias here!
        if(is_array($this->sUserFunction))
        {
            return $this->sUserFunction[1];
        }
        return $this->sUserFunction;
    }

    /**
     * Set call options for this instance
     *
     * @param string        $sName                The name of the configuration option
     * @param string        $sValue                The value of the configuration option
     *
     * @return void
     */
    public function configure($sName, $sValue)
    {
        switch($sName)
        {
        case 'alias':
            $this->sAlias = $sValue;
            break;
        case 'include':
            $this->sInclude = $sValue;
            break;
        default:
            $this->aConfiguration[$sName] = $sValue;
            break;
        }
    }

    /**
     * Constructs and returns a <Jaxon\Request\Request> object which is capable of generating the javascript call to invoke this jaxon enabled function
     *
     * @return Jaxon\Request\Request
     */
    public function generateRequest()
    {
        $sAlias = (($this->sAlias) ? $this->sAlias : $this->getName());
        return new Request($sAlias, 'function');
    }

    /**
     * Generate the javascript function stub that is sent to the browser on initial page load
     *
     * @return string
     */
    public function getScript()
    {
        $sJaxonPrefix = $this->getOption('core.prefix.function');
        $sFunction = $this->getName();
        $sAlias = (($this->sAlias) ? $this->sAlias : $sFunction);

        return $this->render('jaxon::support/function.js', array(
            'sPrefix' => $sJaxonPrefix,
            'sAlias' => $sAlias,
            'sFunction' => $sFunction,
            'aConfig' => $this->aConfiguration,
        ));
    }

    /**
     * Call the registered user function, including an external file if needed
     * and passing along the specified arguments
     *
     * @param array         $aArgs                The function arguments
     *
     * @return void
     */
    public function call($aArgs = array())
    {
        if(($this->sInclude))
        {
            require_once $this->sInclude;
        }
        $response = call_user_func_array($this->sUserFunction, $aArgs);
        if(($response))
        {
            $this->getResponseManager()->append($response);
        }
    }
}
