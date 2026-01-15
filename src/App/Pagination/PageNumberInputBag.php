<?php

/**
 * PageNumberInterface.php
 *
 * Current page number for the paginator.
 *
 * @package jaxon-core
 * @copyright 2026 Thierry Feuzeu
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Pagination;

use Closure;

class PageNumberInputBag extends PageNumberInput
{
    /**
     * @param Closure $bagGetter
     * @param Closure $bagSetter
     */
    public function __construct(private Closure $bagGetter, private Closure $bagSetter)
    {}

    /**
     * Set the input page number.
     *
     * @param int $pageNumber
     *
     * @return int
     */
    public function getInputPageNumber(int $pageNumber): int
    {
        // If no page number is provided, then get the value from the databag.
        return $pageNumber > 0 ? $pageNumber : ($this->bagGetter)();
    }

    /**
     * Set the final page number.
     *
     * @param int $pageNumber
     *
     * @return void
     */
    public function setFinalPageNumber(int $pageNumber): void
    {
        ($this->bagSetter)($pageNumber);
        parent::setFinalPageNumber($pageNumber);
    }
}
