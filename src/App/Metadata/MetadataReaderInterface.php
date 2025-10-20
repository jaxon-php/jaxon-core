<?php

/**
 * MetadataReaderInterface.php
 *
 * Read component metadata.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Metadata;

interface MetadataReaderInterface
{
    /**
     * Get the component metadata
     *
     * @param InputData $xInput
     *
     * @return Metadata
     */
    public function getAttributes(InputData $xInput): Metadata;
}
