<?php

/**
 * Callback.php
 *
 * Base class for Before and After attributes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Attribute;

use Jaxon\Exception\SetupException;

use function count;
use function strtolower;

abstract class AbstractCallback extends AbstractAttribute
{
    /**
     * @var string
     */
    protected $sMethodName = '';

    /**
     * @var array
     */
    protected $aMethodParams = [];

    /**
     * @param string $call The method to call
     * @param array $with The call parameters
     */
    public function __construct(private string $call, private array $with = [])
    {
        $this->sMethodName = $call;
        $this->aMethodParams = $with;
    }

    /**
     * @return string
     */
    abstract protected function getType(): string;

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return '__' . strtolower($this->getType());
    }

    /**
     * @inheritDoc
     */
    public function validateArguments(array $aArguments)
    {
        if(count($aArguments) !== 1 && count($aArguments) !== 2)
        {
            throw new SetupException('the Exclude attribute requires a single boolean or no argument');
        }
    }

    /**
     * @inheritDoc
     */
    protected function validateValues()
    {
        if(preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $this->sMethodName) > 0)
        {
            return;
        }
        throw new SetupException($this->sMethodName . ' is not a valid value for the ' .
            $this->getType() . ' attribute');
    }

    /**
     * @inheritDoc
     */
    protected function getValue()
    {
        if(is_array($this->xPrevValue))
        {
            // Add the current value to the array
            $this->xPrevValue[$this->sMethodName] = $this->aMethodParams;
            return $this->xPrevValue;
        }
        // Return the current value in an array
        return [$this->sMethodName => $this->aMethodParams];
    }
}
