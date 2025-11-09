<?php

/**
 * ReadyScriptGenerator.php
 *
 * Generate the first ready script for Jaxon.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin\Code;

use Jaxon\Plugin\AbstractCodeGenerator;

class ReadyScriptGenerator extends AbstractCodeGenerator
{
    /**
     * @inheritDoc
     */
    public function getScript(): string
    {
        return "jaxon.dom.ready(() => jaxon.processCustomAttrs());";
    }
}
