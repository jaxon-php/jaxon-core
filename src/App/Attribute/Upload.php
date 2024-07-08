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
    protected function validate(): bool
    {
        if(preg_match('/^[a-zA-Z][a-zA-Z0-9_\-\.]*$/', $this->sFieldId) > 0)
        {
            return true;
        }
        $this->setError($this->sFieldId . ' is not a valid "field" value for the Upload attrubute');
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return "'" . $this->sFieldId . "'" ; // The field id is surrounded with simple quotes.
    }
}
