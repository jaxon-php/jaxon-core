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
use Jaxon\Response\ComponentResponse;
use Jaxon\Response\Response;
use Jaxon\Plugin\Response\Pagination\PaginatorPlugin;
use Jaxon\Script\JsExpr;
use Closure;

use function array_pop;
use function array_shift;
use function array_walk;
use function ceil;
use function count;
use function floor;
use function is_a;
use function max;
use function trim;

class Paginator
{
    /**
     * @var integer
     */
    protected $nItemsCount = 0;

    /**
     * @var integer
     */
    protected $nPagesCount = 0;

    /**
     * @var integer
     */
    protected $nItemsPerPage = 0;

    /**
     * @var integer
     */
    protected $nPageNumber = 0;

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
     * @param int $nPageNumber     The current page number
     * @param int $nItemsPerPage    The number of items per page
     * @param int $nItemsCount      The total number of items
     */
    public function __construct(PaginatorPlugin $xPlugin, int $nPageNumber, int $nItemsPerPage, int $nItemsCount)
    {
        $this->xPlugin = $xPlugin;
        $this->nItemsPerPage = $nItemsPerPage > 0 ? $nItemsPerPage : 0;
        $this->nItemsCount = $nItemsCount > 0 ? $nItemsCount : 0;
        $this->nPageNumber = $nPageNumber < 1 ? 1 : $nPageNumber;
        $this->updatePagesCount();
    }

    /**
     * Update the number of pages
     *
     * @return Paginator
     */
    private function updatePagesCount(): Paginator
    {
        $this->nPagesCount = ($this->nItemsPerPage === 0 ? 0 :
            (int)ceil($this->nItemsCount / $this->nItemsPerPage));
        if($this->nPageNumber > $this->nPagesCount)
        {
            $this->nPageNumber = $this->nPagesCount;
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
        $this->nMaxPages = max($nMaxPages, 4);
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
        return $this->nPageNumber >= $this->nPagesCount ?
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
     * Example: [1, 0, 4, 5, 6, 0, 10]
     *
     * @return array
     */
    protected function getPageNumbers(): array
    {
        $aPageNumbers = [];

        if($this->nPagesCount <= $this->nMaxPages)
        {
            for($i = 0; $i < $this->nPagesCount; $i++)
            {
                $aPageNumbers[] = $i + 1;
            }

            return $aPageNumbers;
        }

        // Determine the sliding range, centered around the current page.
        $nNumAdjacents = (int)floor(($this->nMaxPages - 4) / 2);

        $nSlidingStart = 1;
        $nSlidingEndOffset = $nNumAdjacents + 3 - $this->nPageNumber;
        if($nSlidingEndOffset < 0)
        {
            $nSlidingStart = $this->nPageNumber - $nNumAdjacents;
            $nSlidingEndOffset = 0;
        }

        $nSlidingEnd = $this->nPagesCount;
        $nSlidingStartOffset = $this->nPageNumber + $nNumAdjacents + 2 - $this->nPagesCount;
        if($nSlidingStartOffset < 0)
        {
            $nSlidingEnd = $this->nPageNumber + $nNumAdjacents;
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
        if($nSlidingEnd < $this->nPagesCount)
        {
            $aPageNumbers[] = 0; // Ellipsys;
            $aPageNumbers[] = $this->nPagesCount;
        }

        return $aPageNumbers;
    }

    /**
     * Get the links (pages raw data).
     *
     * @return array<Page>
     */
    public function pages(): array
    {
        if($this->nPagesCount < 2)
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
     * Show the pagination links
     *
     * @return string|null
     */
    private function renderLinks(): ?string
    {
        $aPages = $this->pages();
        if(count($aPages) === 0)
        {
            return null;
        }

        $xPrevPage = array_shift($aPages); // The first entry in the array
        $xNextPage = array_pop($aPages); // The last entry in the array
        return $this->xPlugin->renderer()->render($aPages, $xPrevPage, $xNextPage);
    }

    /**
     * Show the pagination links
     *
     * @param string $sWrapperId
     *
     * @return array|null
     */
    private function showLinks(string $sWrapperId): ?array
    {
        $sHtml = $this->renderLinks();
        if(!$sHtml)
        {
            return null;
        }

        if(is_a($this->xPlugin->response(), Response::class))
        {
            /** @var Response */
            $xResponse = $this->xPlugin->response();
            $xResponse->html($sWrapperId, $sHtml);
            return ['id' => $sWrapperId];
        }

        // The wrapper id is not needed for the ComponentResponse
        /** @var ComponentResponse */
        $xResponse = $this->xPlugin->response();
        $xResponse->html($sHtml);
        return [];
    }

    /**
     * @param JsExpr $xCall
     * @param string $sWrapperId
     *
     * @return void
     */
    public function render(JsExpr $xCall, string $sWrapperId = '')
    {
        if(($xFunc = $xCall->func()) === null)
        {
            return;
        }

        // The HTML code must always be displayed, even if it is empty.
        $aParams = $this->showLinks(trim($sWrapperId));
        if($aParams !== null)
        {
            // Set click handlers on the pagination links
            $aParams['func'] = $xFunc->withPage()->jsonSerialize();
            $this->xPlugin->addCommand('pg.paginate', $aParams);
        }
    }
}
