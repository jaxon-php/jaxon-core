<?php

/**
 * Parameter.php - A parameter of a Jaxon request
 *
 * This class is used to create client side requests to the Jaxon functions and callable objects.
 *
 * @package jaxon-core
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request;

use Jaxon\Jaxon;

class Parameter implements Interfaces\Parameter
{
    /**
     * The parameter type
     *
     * @var string
     */
    protected $sType;

    /**
     * The parameter value
     *
     * @var mixed
     */
    protected $xValue;

    /**
     * The constructor.
     * 
     * @param string        $sType            The parameter type
     * @param string        $xValue           The parameter value
     */
    public function __construct($sType, $xValue)
    {
        $this->sType = $sType;
        $this->xValue = $xValue;
    }

    /**
     * Get the parameter type
     * 
     * @return string
     */
    public function getType()
    {
        return $this->sType;
    }

    /**
     * Get the parameter value
     * 
     * @return mixed
     */
    public function getValue()
    {
        return $this->xValue;
    }
}
