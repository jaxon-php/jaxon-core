<?php

/**
 * Exclude.php
 *
 * Jaxon attribute.
 * Specifies if a class or method is excluded from js export.
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

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Exclude extends AbstractAttribute
{
    /**
     * @var bool
     */
    private $bValue;

    /**
     * @param bool $value
     */
    public function __construct(private bool $value = true)
    {
        $this->bValue = $value;
    }

    /**
     * @inheritDoc
     */
    public function validateArguments(array $aArguments)
    {
        if(count($aArguments) !== 0 && count($aArguments) !== 1)
        {
            throw new SetupException('the Exclude attribute requires a single boolean or no argument');
        }
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'protected';
    }

    /**
     * @inheritDoc
     */
    protected function getValue()
    {
        return $this->bValue;
    }
}
