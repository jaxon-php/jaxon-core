<?php

/**
 * Callback.php
 *
 * Jaxon attribute.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Attribute;

use Jaxon\Exception\SetupException;

abstract class AbstractAttribute
{
    /**
     * @var mixed
     */
    protected $xTarget;

    /**
     * @var string
     */
    protected string $sError;

    /**
     * @var string
     */
    protected string $sNamespace;

    /**
     * @var mixed
     */
    protected $xPrevValue = null;

    /**
     * @param mixed $xTarget
     *
     * @return void
     */
    public function setTarget($xTarget): void
    {
        $this->xTarget = $xTarget;
    }

    /**
     * Set the attribute previous value
     *
     * @param mixed $xPrevValue The previous value of the attribute
     *
     * @return void
     */
    public function setPrevValue($xPrevValue)
    {
        $this->xPrevValue = $xPrevValue;
    }

    /**
     * @param string $sError
     *
     * @return void
     */
    protected function setError(string $sError)
    {
        $this->sError = $sError;
    }

    /**
     * Get the annotation name
     * This is the corresponding option name in the Jaxon config.
     *
     * @return string
     */
    abstract public function getName(): string;

    /**
     * Validate the attribute parameters
     *
     * @return bool
     */
    abstract protected function validate(): bool;

    /**
     * Get the annotation value
     *
     * @return mixed
     */
    abstract protected function getValue();

    /**
     * @param string $sNamespace
     *
     * @return void
     */
    public function setNamespace(string $sNamespace)
    {
        $this->sNamespace = $sNamespace;
    }

    /**
     * Get the annotation value
     *
     * @return mixed
     */
    public function value()
    {
        if(!$this->validate())
        {
            throw new SetupException($this->sError);
        }

        return $this->getValue();
    }
}
