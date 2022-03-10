<?php

/**
 * CallableFunction.php - Jaxon user function
 *
 * This class stores a reference to a user defined function which can be called from the client via an Jaxon request
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

class CallableFunction
{
    /**
     * The name of the corresponding javascript function
     *
     * @var string
     */
    private $sJsFunction;

    /**
     * A string or an array which defines the function to be registered
     *
     * @var string|array
     */
    private $xCallableFunction;

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

    public function __construct(string $sCallableFunction)
    {
        $this->aConfiguration = [];
        $this->sJsFunction = $sCallableFunction;
        $this->xCallableFunction = $sCallableFunction;
    }

    /**
     * Get the name of the function being referenced
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->sJsFunction;
    }

    /**
     * Get the config options of the function being referenced
     *
     * @return array
     */
    public function getConfigOptions(): array
    {
        return $this->aConfiguration;
    }

    /**
     * Set call options for this instance
     *
     * @param string $sName    The name of the configuration option
     * @param string $sValue    The value of the configuration option
     *
     * @return void
     */
    public function configure(string $sName, string $sValue)
    {
        switch($sName)
        {
        case 'class': // The user function is a method in the given class
            $this->xCallableFunction = [$sValue, $this->xCallableFunction];
            break;
        case 'alias':
            $this->sJsFunction = $sValue;
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
     * Call the registered user function, including an external file if needed
     * and passing along the specified arguments
     *
     * @param array $aArgs    The function arguments
     *
     * @return void
     */
    public function call(array $aArgs = [])
    {
        if(($this->sInclude))
        {
            require_once $this->sInclude;
        }

        // If the function is an alias for a class method, then instanciate the class
        if(is_array($this->xCallableFunction) && is_string($this->xCallableFunction[0]))
        {
            $sClassName = $this->xCallableFunction[0];
            $this->xCallableFunction[0] = new $sClassName;
        }

        return call_user_func_array($this->xCallableFunction, $aArgs);
    }
}
