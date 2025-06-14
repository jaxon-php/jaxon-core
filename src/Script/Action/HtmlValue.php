<?php

/**
 * HtmlValue.php
 *
 * Wrapper for values from web pages or forms.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Script\Action;

use JsonSerializable;

class HtmlValue implements JsonSerializable
{
    /**
     * The class contructor
     *
     * @param array $aValue
     */
    public function __construct(protected array $aValue)
    {}

    /**
     * Convert the js value to int
     *
     * @return self
     */
    public function toInt(): self
    {
        $this->aValue['toInt'] = true;
        return $this;
    }

    /**
     * Trim the js value
     *
     * @return self
     */
    public function trim(): self
    {
        $this->aValue['trim'] = true;
        return $this;
    }

    /**
     * Convert to array.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->aValue;
    }
}
