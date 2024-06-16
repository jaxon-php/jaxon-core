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

namespace Jaxon\Plugin\Response\Pagination;

use Jaxon\Script\JsExpr;

use function array_walk;
use function ceil;
use function floor;
use function max;

class Paginator
{
    /**
     * @var integer
     */
    protected $nTotalItems = 0;

    /**
     * @var integer
     */
    protected $nTotalPages = 0;

    /**
     * @var integer
     */
    protected $nItemsPerPage = 0;

    /**
     * @var integer
     */
    protected $nCurrentPage = 0;

    /**
     * @var integer
     */
    protected $nMaxPages = 10;

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
     * @var PaginatorPlugin
     */
    private $xPlugin;

    /**
     * The constructor.
     *
     * @param PaginatorPlugin $xPlugin
     * @param int $nCurrentPage     The current page number
     * @param int $nItemsPerPage    The number of items per page
     * @param int $nTotalItems      The total number of items
     */
    public function __construct(PaginatorPlugin $xPlugin, int $nCurrentPage, int $nItemsPerPage, int $nTotalItems)
    {
        $this->xPlugin = $xPlugin;
        $this->setCurrentPage($nCurrentPage)
            ->setItemsPerPage($nItemsPerPage)
            ->setTotalItems($nTotalItems);
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
     * Update the number of pages
     *
     * @return Paginator
     */
    protected function updateTotalPages(): Paginator
    {
        $this->nTotalPages = ($this->nItemsPerPage === 0 ? 0 :
            (int)ceil($this->nTotalItems / $this->nItemsPerPage));
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
        $this->nMaxPages = max($nMaxPages, 4);
        return $this;
    }

    /**
     * Set the current page number
     *
     * @param int $nCurrentPage    The current page number
     *
     * @return Paginator
     */
    protected function setCurrentPage(int $nCurrentPage): Paginator
    {
        $this->nCurrentPage = $nCurrentPage;
        return $this;
    }

    /**
     * Set the number of items per page
     *
     * @param int $nItemsPerPage    The number of items per page
     *
     * @return Paginator
     */
    protected function setItemsPerPage(int $nItemsPerPage): Paginator
    {
        $this->nItemsPerPage = $nItemsPerPage;
        return $this->updateTotalPages();
    }

    /**
     * Set the total number of items
     *
     * @param int $nTotalItems    The total number of items
     *
     * @return Paginator
     */
    protected function setTotalItems(int $nTotalItems): Paginator
    {
        $this->nTotalItems = $nTotalItems;
        return $this->updateTotalPages();
    }

    /**
     * Get the previous page data.
     *
     * @return Page
     */
    protected function getPrevPage(): Page
    {
        return $this->nCurrentPage <= 1 ?
            new Page('disabled', $this->sPreviousText, 0) :
            new Page('enabled', $this->sPreviousText, $this->nCurrentPage - 1);
    }

    /**
     * Get the next page data.
     *
     * @return Page
     */
    protected function getNextPage(): Page
    {
        return $this->nCurrentPage >= $this->nTotalPages ?
            new Page('disabled', $this->sNextText, 0) :
            new Page('enabled', $this->sNextText, $this->nCurrentPage + 1);
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
        $sType = ($nNumber === $this->nCurrentPage ? 'current' : 'enabled');
        return new Page($sType, "$nNumber", $nNumber);
    }

    /**
     * Get the array of page numbers to be printed.
     *
     * Example: [1, 0, 4, 5, 6, 0, 10]
     *
     * @return array
     */
    protected function getPageNumbers(): array
    {
        $aPageNumbers = [];

        if($this->nTotalPages <= $this->nMaxPages)
        {
            for($i = 0; $i < $this->nTotalPages; $i++)
            {
                $aPageNumbers[] = $i + 1;
            }

            return $aPageNumbers;
        }

        // Determine the sliding range, centered around the current page.
        $nNumAdjacents = (int)floor(($this->nMaxPages - 4) / 2);

        $nSlidingStart = 1;
        $nSlidingEndOffset = $nNumAdjacents + 3 - $this->nCurrentPage;
        if($nSlidingEndOffset < 0)
        {
            $nSlidingStart = $this->nCurrentPage - $nNumAdjacents;
            $nSlidingEndOffset = 0;
        }

        $nSlidingEnd = $this->nTotalPages;
        $nSlidingStartOffset = $this->nCurrentPage + $nNumAdjacents + 2 - $this->nTotalPages;
        if($nSlidingStartOffset < 0)
        {
            $nSlidingEnd = $this->nCurrentPage + $nNumAdjacents;
            $nSlidingStartOffset = 0;
        }

        // Build the list of page numbers.
        if($nSlidingStart > 1)
        {
            $aPageNumbers[] = 1;
            $aPageNumbers[] = 0; // Ellipsys;
        }
        for($i = $nSlidingStart - $nSlidingStartOffset; $i <= $nSlidingEnd + $nSlidingEndOffset; $i++)
        {
            $aPageNumbers[] = $i;
        }
        if($nSlidingEnd < $this->nTotalPages)
        {
            $aPageNumbers[] = 0; // Ellipsys;
            $aPageNumbers[] = $this->nTotalPages;
        }

        return $aPageNumbers;
    }

    /**
     * Get the number of pages.
     *
     * @return int
     */
    public function getTotalPages(): int
    {
        return $this->nTotalPages;
    }

    /**
     * Get the links (pages raw data).
     *
     * @return array<Page>
     */
    public function pages(): array
    {
        if($this->nTotalPages < 2)
        {
            return [];
        }

        $aPageNumbers = $this->getPageNumbers();
        $aPages = [$this->getPrevPage()];
        array_walk($aPageNumbers, function($nNumber) use(&$aPages) {
            $aPages[] = $this->getPage($nNumber);
        });
        $aPages[] = $this->getNextPage();

        return $aPages;
    }

    /**
     * Render the paginator
     *
     * @param JsExpr $xCall
     * @param string $sWrapperId
     *
     * @return string
     */
    public function paginate(JsExpr $xCall, string $sWrapperId = '')
    {
        $this->xPlugin->render($this->pages(), $xCall, $sWrapperId);
    }
}
