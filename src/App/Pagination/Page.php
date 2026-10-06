<?php

/**
 * Page.php
 *
 * A page link for the Jaxon Paginator
 *
 * @package jaxon-core
 * @copyright 2024 Thierry Feuzeu
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Pagination;

class Page
{
    /**
     * @param string $sType
     * @param string $sText
     * @param int $nNumber
     */
    public function __construct(public string $sType, public string $sText, public int $nNumber)
    {}
}
