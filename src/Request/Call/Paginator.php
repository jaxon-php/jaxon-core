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

namespace Jaxon\Request\Call;

use Jaxon\App\View\PaginationRenderer;

use function array_map;
use function array_walk;
use function ceil;
use function floor;
use function max;

class Paginator
{
    /**
     * The Jaxon request to be paginated
     *
     * @var Call
     */
    protected $xCall = null;

    /**
     * @var PaginationRenderer
     */
    protected $xRenderer;

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
     * The constructor.
     *
     * @param PaginationRenderer $xRenderer
     */
    public function __construct(PaginationRenderer $xRenderer)
    {
        $this->xRenderer = $xRenderer;
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
        $this->nTotalPages = ($this->nItemsPerPage === 0 ? 0 : (int)ceil($this->nTotalItems / $this->nItemsPerPage));
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
     * Get the js call to a given page
     *
     * @param int $pageNum    The page number
     *
     * @return string
     */
    protected function getPageCall(int $pageNum): string
    {
        return $this->xCall->setPageNumber($pageNum)->getScript();
    }

    /**
     * Get the previous page data.
     *
     * @return array
     */
    protected function getPrevLink(): array
    {
        if($this->nCurrentPage <= 1)
        {
            return ['disabled', $this->sPreviousText, ''];
        }
        return ['enabled', $this->sPreviousText, $this->getPageCall($this->nCurrentPage - 1)];
    }

    /**
     * Get the next page data.
     *
     * @return array
     */
    protected function getNextLink(): array
    {
        if($this->nCurrentPage >= $this->nTotalPages)
        {
            return ['disabled', $this->sNextText, ''];
        }
        return ['enabled', $this->sNextText, $this->getPageCall($this->nCurrentPage + 1)];
    }

    /**
     * Get a page data.
     *
     * @param integer $nNumber    The page number
     *
     * @return array
     */
    protected function getPageLink(int $nNumber): array
    {
        if($nNumber < 1)
        {
            return ['disabled', $this->sEllipsysText, ''];
        }
        $sTemplate = ($nNumber === $this->nCurrentPage ? 'current' : 'enabled');
        return [$sTemplate, $nNumber, $this->getPageCall($nNumber)];
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
     * Get the links (pages raw data).
     *
     * @return array
     */
    public function links(): array
    {
        $aPageNumbers = $this->getPageNumbers();
        $aPages = [$this->getPrevLink()];
        array_walk($aPageNumbers, function($nNumber) use(&$aPages) {
            $aPages[] = $this->getPageLink($nNumber);
        });
        $aPages[] = $this->getNextLink();

        return $aPages;
    }

    /**
     * Get the pages.
     *
     * @return array
     */
    public function pages(): array
    {
        if($this->nTotalPages < 2)
        {
            return [];
        }

        return array_map(function($aPage) {
            return (object)['type' => $aPage[0], 'text' => $aPage[1], 'call' => $aPage[2]];
        }, $this->links());
    }

    /**
     * Setup the paginator
     *
     * @param Call $xCall    The call to be paginated
     * @param int $nCurrentPage    The current page number
     * @param int $nItemsPerPage    The number of items per page
     * @param int $nTotalItems    The total number of items
     *
     * @return Paginator
     */
    public function setup(Call $xCall, int $nCurrentPage, int $nItemsPerPage, int $nTotalItems): Paginator
    {
        $this->setCurrentPage($nCurrentPage)->setItemsPerPage($nItemsPerPage)->setTotalItems($nTotalItems);
        $this->xCall = $xCall;
        return $this;
    }

    /**
     * Render an HTML pagination control.
     *
     * @return string
     */
    public function __toString()
    {
        if($this->nTotalPages < 2)
        {
            return '';
        }
        return $this->xRenderer->render($this)->__toString();
    }
}
