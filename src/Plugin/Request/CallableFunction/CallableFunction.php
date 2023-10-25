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

namespace Jaxon\Plugin\Request\CallableFunction;

use Jaxon\Di\Container;

class CallableFunction
{
    /**
     * The DI container
     *
     * @var Container
     */
    protected $di;

    /**
     * The name of the function in the ajax call
     *
     * @var string
     */
    private $sFunction;

    /**
     * The name of the generated javascript function
     *
     * @var string
     */
    private $sJsFunction;

    /**
     * A string or an array which defines the registered PHP function
     *
     * @var string|array
     */
    private $xPhpFunction;

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
    private $aOptions = [];

    /**
     * The constructor
     *
     * @param Container $di
     * @param string $sFunction
     * @param string $sJsFunction
     * @param string $sPhpFunction
     */
    public function __construct(Container $di, string $sFunction, string $sJsFunction, string $sPhpFunction)
    {
        $this->di = $di;
        $this->sFunction = $sFunction;
        $this->sJsFunction = $sJsFunction;
        $this->xPhpFunction = $sPhpFunction;
    }

    /**
     * Get the name of the function being referenced
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->sFunction;
    }

    /**
     * Get name of the corresponding javascript function
     *
     * @return string
     */
    public function getJsName(): string
    {
        return $this->sJsFunction;
    }

    /**
     * Get the config options of the function being referenced
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->aOptions;
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
            $this->xPhpFunction = [$sValue, $this->xPhpFunction];
            break;
        case 'include':
            $this->sInclude = $sValue;
            break;
        default:
            $this->aOptions[$sName] = $sValue;
            break;
        }
    }

    /**
     * Call the registered user function, including an external file if needed
     * and passing along the specified arguments
     *
     * @param array $aArgs    The function arguments
     *
     * @return mixed
     */
    public function call(array $aArgs = [])
    {
        if(($this->sInclude))
        {
            require_once $this->sInclude;
        }
        // If the function is an alias for a class method, then instantiate the class
        if(is_array($this->xPhpFunction) && is_string($this->xPhpFunction[0]))
        {
            $sClassName = $this->xPhpFunction[0];
            $this->xPhpFunction[0] = $this->di->h($sClassName) ?
                $this->di->g($sClassName) : $this->di->make($sClassName);
        }
        return call_user_func_array($this->xPhpFunction, $aArgs);
    }
}
