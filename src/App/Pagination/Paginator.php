<?php

/*
The MIT License (MIT)

Copyright (c) 2014 Jason Grimes

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

/**
 * Paginator.php - Jaxon Paginator
 *
 * Create pagination links from a Jaxon request and a data array.
 *
 * @package jaxon-core
 * @author Jason Grimes
 * @author Thierry Feuzeu
 * @copyright 2014 Jason Grimes
 * @copyright 2016 Thierry Feuzeu
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Pagination;

use Jaxon\App\Pagination\Page;
use Jaxon\Script\JsExpr;
use Closure;

use function array_map;
use function ceil;
use function count;
use function max;
use function range;
use function trim;

abstract class Paginator
{
    /**
     * @var integer
     */
    protected $nPagesCount = 0;

    /**
     * @var integer
     */
    protected $nMaxPages = 9;

    /**
     * @var string
     */
    protected $sPreviousText = '&laquo;';

    /**
     * @var string
     */
    protected $sNextText = '&raquo;';

    /**
     * @var string
     */
    protected $sEllipsysText = '...';

    /**
     * The constructor.
     *
     * @param int $nPageNumber     The current page number
     * @param int $nItemsPerPage    The number of items per page
     * @param int $nItemsCount      The total number of items
     * @param PaginationRenderer $xRenderer
     */
    public function __construct(protected int $nPageNumber, protected int $nItemsPerPage,
        protected int $nItemsCount, protected PaginationRenderer $xRenderer)
    {
        $this->updatePagesCount();
    }

    /**
     * Update the number of pages
     *
     * @return Paginator
     */
    private function updatePagesCount(): Paginator
    {
        $this->nItemsPerPage = $this->nItemsPerPage > 0 ? $this->nItemsPerPage : 0;
        // $this->nItemsCount = $this->nItemsCount > 0 ? $this->nItemsCount : 0;
        $this->nPageNumber = $this->nPageNumber < 1 ? 1 : $this->nPageNumber;

        if($this->nItemsCount >= 0)
        {
            $this->nPagesCount = ($this->nItemsPerPage === 0 ? 0 :
                (int)ceil($this->nItemsCount / $this->nItemsPerPage));
            if($this->nPageNumber > $this->nPagesCount)
            {
                $this->nPageNumber = $this->nPagesCount;
            }
        }
        return $this;
    }

    /**
     * Set the text for the previous page link
     *
     * @param string $sText    The text for the previous page link
     *
     * @return Paginator
     */
    public function setPreviousText(string $sText): Paginator
    {
        $this->sPreviousText = $sText;
        return $this;
    }

    /**
     * Set the text for the next page link
     *
     * @param string $sText    The text for the previous page link
     *
     * @return Paginator
     */
    public function setNextText(string $sText): Paginator
    {
        $this->sNextText = $sText;
        return $this;
    }

    /**
     * Set the max number of pages to show
     *
     * @param int $nMaxPages    The max number of pages to show
     *
     * @return Paginator
     */
    public function setMaxPages(int $nMaxPages): Paginator
    {
        // Make sure the max number of pages is odd and greater than 5.
        $this->nMaxPages = max((int)(($nMaxPages - 1) / 2) * 2 + 1, 5);
        return $this;
    }

    /**
     * Get the previous page data.
     *
     * @return Page
     */
    protected function getPrevPage(): Page
    {
        return $this->nPageNumber <= 1 ?
            new Page('disabled', $this->sPreviousText, 0) :
            new Page('enabled', $this->sPreviousText, $this->nPageNumber - 1);
    }

    /**
     * Get the next page data.
     *
     * @return Page
     */
    protected function getNextPage(): Page
    {
        // The next page link is always active when the total number of items is not privided.
        return $this->nItemsCount >= 0 && $this->nPageNumber >= $this->nPagesCount ?
            new Page('disabled', $this->sNextText, 0) :
            new Page('enabled', $this->sNextText, $this->nPageNumber + 1);
    }

    /**
     * Get a page data.
     *
     * @param integer $nNumber    The page number
     *
     * @return Page
     */
    protected function getPage(int $nNumber): Page
    {
        if($nNumber < 1)
        {
            return new Page('disabled', $this->sEllipsysText, 0);
        }
        $sType = ($nNumber === $this->nPageNumber ? 'current' : 'enabled');
        return new Page($sType, "$nNumber", $nNumber);
    }

    /**
     * Get the array of page numbers to be printed.
     *
     * Example: [1, 2, 3, 4, 5, 6, 7]
     *
     * @return array
     */
    protected function getAllPageNumbers(): array
    {
        return range(1, $this->nPagesCount);
    }

    /**
     * Get the array of page numbers to be printed, when the total number of items is not provided.
     *
     * Example: [1, 0, 4, 5, 6, 0, 10]
     *
     * @return array
     */
    protected function getPageNumbersWithoutTotal(): array
    {
        $aPageNumbers = [];

        // Determine the sliding range, centered around the current page.
        $nNumAdjacents = ($this->nMaxPages - 1) / 2;

        $nSlidingStart = 1;
        $nSlidingStartThreshold = $nNumAdjacents;
        $nSlidingEnd = $this->nPageNumber + $nNumAdjacents - 1;

        if($this->nPageNumber > $nNumAdjacents + 1)
        {
            $nSlidingStart = $this->nPageNumber - $nNumAdjacents + 2;
        }
        if($this->nPageNumber <= $nSlidingStartThreshold)
        {
            $nSlidingEnd += $nSlidingStartThreshold - $this->nPageNumber + 1;
        }

        // Build the list of page numbers. Pages with 0 as number are ellipsys.
        $aStartPages = $nSlidingStart > 1 ? [1, 0] : [];
        $aPageNumbers = range($nSlidingStart, $nSlidingEnd);
        // Ellipsys are always added at the end of the list.
        return [...$aStartPages, ...$aPageNumbers, 0];
    }

    /**
     * Get the array of page numbers to be printed, when the total number of items is provided.
     *
     * Example: [1, 0, 4, 5, 6, 0, 10]
     *
     * @return array
     */
    protected function getPageNumbersWithTotal(): array
    {
        $aPageNumbers = [];

        // Determine the sliding range, centered around the current page.
        $nNumAdjacents = ($this->nMaxPages - 1) / 2;

        $nSlidingStart = 1;
        $nSlidingStartThreshold = $nNumAdjacents;
        $nSlidingEnd = $this->nPagesCount;
        $nSlidingEndThreshold = $this->nPagesCount - $nNumAdjacents;

        if($this->nPageNumber > $nNumAdjacents + 1)
        {
            $nSlidingStart = $this->nPageNumber - $nNumAdjacents + 2;
        }
        if($this->nPageNumber > $nSlidingEndThreshold)
        {
            $nSlidingStart -= $this->nPageNumber - $nSlidingEndThreshold;
        }

        if($this->nPageNumber < $this->nPagesCount - $nNumAdjacents)
        {
            $nSlidingEnd = $this->nPageNumber + $nNumAdjacents - 2;
        }
        if($this->nPageNumber <= $nSlidingStartThreshold)
        {
            $nSlidingEnd += $nSlidingStartThreshold - $this->nPageNumber + 1;
        }

        // Build the list of page numbers. Pages with 0 as number are ellipsys.
        $aStartPages = $nSlidingStart > 1 ? [1, 0] : [];
        $aEndPages = $nSlidingEnd < $this->nPagesCount ? [0, $this->nPagesCount] : [];
        $aPageNumbers = range($nSlidingStart, $nSlidingEnd);
        return [...$aStartPages, ...$aPageNumbers, ...$aEndPages];
    }

    /**
     * Get the current page number.
     *
     * @return int
     */
    public function currentPage(): int
    {
        return $this->nPageNumber;
    }

    /**
     * Get the links (pages raw data).
     *
     * @return array<null|Page|array<Page>>
     */
    public function pages(): array
    {
        $aPageNumbers = match(true) {
            $this->nItemsCount < 0 => $this->getPageNumbersWithoutTotal(),
            $this->nPagesCount < 2 => [],
            $this->nPagesCount <= $this->nMaxPages => $this->getAllPageNumbers(),
            default => $this->getPageNumbersWithTotal(),
        };
        if(count($aPageNumbers) === 0)
        {
            return [null, [], null];
        }

        $aPages = array_map($this->getPage(...), $aPageNumbers);
        return [$this->getPrevPage(), $aPages, $this->getNextPage()];
    }

    /**
     * Call a closure that will receive the page number as parameter.
     *
     * @param Closure $fPageCallback
     *
     * @return Paginator
     */
    public function page(Closure $fPageCallback): Paginator
    {
        $fPageCallback($this->nPageNumber);

        return $this;
    }

    /**
     * Call a closure that will receive the pagination offset as parameter.
     *
     * @param Closure $fOffsetCallback
     *
     * @return Paginator
     */
    public function offset(Closure $fOffsetCallback): Paginator
    {
        $fOffsetCallback(($this->nPageNumber - 1) * $this->nItemsPerPage);

        return $this;
    }

    /**
     * @param string $sHtml
     * @param array $aParams
     *
     * @return void
     */
    abstract protected function showHtml(string $sHtml, array $aParams): void;

    /**
     * Render the pagination links with a given javascript call.
     *
     * @param JsExpr $xCall
     * @param array $aParams
     *
     * @return void
     */
    protected function paginate(JsExpr $xCall, ...$aParams): void
    {
        if(($xFunc = $xCall->func()) !== null)
        {
            $this->showHtml(trim((string)$this->xRenderer->getHtml($this)),
                [$xFunc->withPage()->jsonSerialize(), ...$aParams]);
        }
    }
}
