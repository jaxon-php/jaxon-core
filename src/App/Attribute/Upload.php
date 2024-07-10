<?php

/**
 * Upload.php
 *
 * Jaxon attribute.
 * Specifies an upload form field id.
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
use function preg_match;

#[Attribute(Attribute::TARGET_METHOD)]
class Upload extends AbstractAttribute
{
    /**
     * @var string
     */
    private string $sFieldId;

    /**
     * @param string $field The name of the upload field
     */
    public function __construct(string $field)
    {
        $this->sFieldId = $field;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'upload';
    }

    /**
     * @inheritDoc
     */
    public function validateArguments(array $aArguments)
    {
        if(count($aArguments) !== 1)
        {
            throw new SetupException('The Upload attribute requires only one argument');
        }
    }

    /**
     * @inheritDoc
     */
    protected function validateValues()
    {
        if(preg_match('/^[a-zA-Z][a-zA-Z0-9_\-\.]*$/', $this->sFieldId) > 0)
        {
            return;
        }
        throw new SetupException($this->sFieldId . ' is not a valid "field" value for the Upload attribute');
    }

    /**
     * @inheritDoc
     */
    protected function getValue()
    {
        return "'" . $this->sFieldId . "'" ; // The field id is surrounded with simple quotes.
    }
}
