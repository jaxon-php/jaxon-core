<?php

/**
 * DataBag.php
 *
 * Jaxon attribute.
 * Specifies a data bag stored in the browser and included in ajax requests to a method.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Attribute;

use Attribute;
use Jaxon\Exception\SetupException;

use function count;
use function is_array;
use function preg_match;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class DataBag extends AbstractAttribute
{
    /**
     * The data bag name
     *
     * @var string
     */
    protected $sName = '';

    /**
     * @param string $name The data bag name
     */
    public function __construct(private string $name)
    {
        $this->sName = $name;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'bags';
    }

    /**
     * @inheritDoc
     */
    public function validateArguments(array $aArguments)
    {
        if(count($aArguments) !== 1)
        {
            throw new SetupException('The DataBag attribute requires only one argument');
        }
    }

    /**
     * @inheritDoc
     */
    protected function validateValues()
    {
        if(preg_match('/^[a-zA-Z][a-zA-Z0-9_\-\.]*$/', $this->sName) > 0)
        {
            return;
        }
        throw new SetupException($this->sName . ' is not a valid "name" value for the Databag attribute');
    }

    /**
     * @inheritDoc
     */
    protected function getValue()
    {
        if(is_array($this->xPrevValue))
        {
            $this->xPrevValue[] = $this->sName; // Append the current value to the array
            return $this->xPrevValue;
        }
        return [$this->sName]; // Return the current value in an array
    }
}
