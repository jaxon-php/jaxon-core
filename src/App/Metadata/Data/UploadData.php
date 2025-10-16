<?php

/**
 * UploadData.php
 *
 * Upload metadata for Jaxon classes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Metadata\Data;

use Jaxon\Exception\SetupException;

use function preg_match;

class UploadData extends AbstractData
{
    /**
     * The id of the upload field
     *
     * @var string
     */
    protected $sField = '';

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'upload';
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        // The field id is surrounded with simple quotes.
        return "'{$this->sField}'";
    }

    /**
     * @param string $sField
     *
     * @return void
     */
    protected function validateField(string $sField): void
    {
        if(preg_match('/^[a-zA-Z][a-zA-Z0-9_\-\.]*$/', $sField) > 0)
        {
            return;
        }
        throw new SetupException("$sField is not a valid \"field\" value for upload");
    }

    /**
     * @param string $sField
     *
     * @return void
     */
    public function setValue(string $sField): void
    {
        $this->validateField($sField);

        $this->sField = $sField;
    }

    /**
     * @inheritDoc
     */
    public function encode(string $sVarName): array
    {
        return ["{$sVarName}->setValue('{$this->sField}');"];
    }
}
