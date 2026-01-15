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

class PageNumberInput
{
    /**
     * The current page number.
     *
     * @var int
     */
    private int $pageNumber = 1;

    /**
     * Set the input page number.
     *
     * @param int $pageNumber
     *
     * @return int
     */
    public function getInputPageNumber(int $pageNumber): int
    {
        return $pageNumber > 0 ? $pageNumber : 1;
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
        $this->pageNumber = $pageNumber;
    }

    /**
     * Get the final page number
     *
     * @return int
     */
    public function getFinalPageNumber(): int
    {
        return $this->pageNumber;
    }
}
