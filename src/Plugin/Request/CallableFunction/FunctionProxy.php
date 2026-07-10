<?php

/**
 * FunctionProxy.php
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

use Jaxon\Di\ComponentContainer;
use ReflectionClass;
use ReflectionFunction;
use ReflectionParameter;

use function call_user_func_array;
use function is_array;
use function is_string;

class FunctionProxy
{
    /**
     * A string or an array which defines the registered PHP function
     *
     * @var string|array
     */
    private string|array $xPhpFunction;

    /**
     * The path and file name of the include file where the function is defined
     *
     * @var string
     */
    private string $sInclude = '';

    /**
     * An associative array containing call options that will be sent
     * to the browser with the client script.
     *
     * @var array
     */
    private array $aOptions = [];

    /**
     * @param ComponentContainer $cdi
     * @param string $sFunction
     * @param string $sJsFunction
     * @param string $sPhpFunction
     */
    public function __construct(private ComponentContainer $cdi,
        private string $sFunction, private string $sJsFunction, string $sPhpFunction)
    {
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
     * Get the name of the corresponding javascript function
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
    public function configure(string $sName, string $sValue): void
    {
        switch($sName)
        {
        case 'class': // The user function is a method in the given class
            $this->xPhpFunction = is_string($this->xPhpFunction) ?
                [$sValue, $this->xPhpFunction] : [$sValue, $this->xPhpFunction[1]];
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
     * Get the function parameters
     *
     * @return array<ReflectionParameter>
     */
    private function getArgTypes(): array
    {
        return is_string($this->xPhpFunction) ?
            (new ReflectionFunction($this->xPhpFunction))->getParameters() :
            (new ReflectionClass($this->xPhpFunction[0]))
                ->getMethod($this->xPhpFunction[1])->getParameters();
    }

    /**
     * Call the registered user function, including an external file if needed
     * and passing along the specified arguments
     *
     * @param CallableFunction $xAction
     *
     * @return void
     */
    public function call(CallableFunction $xAction): void
    {
        if($this->sInclude !== '')
        {
            require_once $this->sInclude;
        }
        // If the function is an alias for a class method, then instantiate the class
        if(is_array($this->xPhpFunction) && is_string($this->xPhpFunction[0]))
        {
            $this->xPhpFunction[0] = $this->cdi->getClassInstance($this->xPhpFunction[0]);
        }

        $aArgs = $this->cdi->convertArguments($xAction->args(), $this->getArgTypes());
        call_user_func_array($this->xPhpFunction, $aArgs);
    }
}
