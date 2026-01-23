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
     * @var string
     */
    public $sType;

    /**
     * @var string
     */
    public $sText;

    /**
     * @var int
     */
    public $nNumber;

    public function __construct(string $sType, string $sText, int $nNumber)
    {
        $this->sType = $sType;
        $this->sText = $sText;
        $this->nNumber = $nNumber;
    }
}
