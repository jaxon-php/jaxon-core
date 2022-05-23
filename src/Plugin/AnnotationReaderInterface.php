<?php

/**
 * AnnotationReaderInterface.php
 *
 * Read Jaxon class annotations.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin;

interface AnnotationReaderInterface
{
    /**
     * Get the class attributes from its annotations
     *
     * @param string $sClass
     * @param array $aMethods
     * @param array $aProperties
     *
     * @return array
     */
    public function getAttributes(string $sClass, array $aMethods = [], array $aProperties = []): array;
}
