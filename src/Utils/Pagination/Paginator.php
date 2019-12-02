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
 * Create pagination links from  an Jaxon request and a data array.
 *
 * @package jaxon-core
 * @author Jason Grimes
 * @author Thierry Feuzeu
 * @copyright 2014 Jason Grimes
 * @copyright 2016 Thierry Feuzeu
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils\Pagination;

use Jaxon\Request\Factory\Request;
use Jaxon\Request\Factory\Parameter;

class Paginator
{
    /**
     * @var integer
     */
    protected $totalItems = 0;

    /**
     * @var integer
     */
    protected $totalPages = 0;

    /**
     * @var integer
     */
    protected $itemsPerPage = 0;

    /**
     * @var integer
     */
    protected $currentPage = 0;

    /**
     * @var integer
     */
    protected $maxPagesToShow = 10;

    /**
     * The pagination renderer
     *
     * @var Renderer
     */
    protected $xRenderer = null;

    /**
     * The constructor
     *
     * @param Renderer $xRenderer
     */
    public function __construct(Renderer $xRenderer)
    {
        $this->xRenderer = $xRenderer;
    }

    /**
     * Set the text for the previous page link
     *
     * @param string $text The text for the previous page link
     *
     * @return Paginator
     */
    public function setPreviousText($text)
    {
        $this->xRenderer->setPreviousText($text);
        return $this;
    }

    /**
     * Set the text for the next page link
     *
     * @param string $text The text for the previous page link
     *
     * @return Paginator
     */
    public function setNextText($text)
    {
        $this->xRenderer->setNextText($text);
        return $this;
    }

    /**
     * Update the number of pages
     *
     * @return Paginator
     */
    protected function updateTotalPages()
    {
        $this->totalPages = ($this->itemsPerPage == 0 ? 0 : (int)ceil($this->totalItems / $this->itemsPerPage));
        return $this;
    }

    /**
     * Set the max number of pages to show
     *
     * @param int $maxPagesToShow The max number of pages to show
     *
     * @return Paginator
     * @throws \InvalidArgumentException if $maxPagesToShow is less than 3.
     */
    public function setMaxPagesToShow($maxPagesToShow)
    {
        $this->maxPagesToShow = intval($maxPagesToShow);
        if($this->maxPagesToShow < 4)
        {
            $this->maxPagesToShow = 10;
            throw new \InvalidArgumentException('maxPagesToShow cannot be less than 4.');
        }
        return $this;
    }

    /**
     * Set the current page number
     *
     * @param int $currentPage The current page number
     *
     * @return Paginator
     */
    public function setCurrentPage($currentPage)
    {
        $this->currentPage = intval($currentPage);
        return $this;
    }

    /**
     * Set the number of items per page
     *
     * @param int $itemsPerPage The number of items per page
     *
     * @return Paginator
     */
    public function setItemsPerPage($itemsPerPage)
    {
        $this->itemsPerPage = intval($itemsPerPage);
        return $this->updateTotalPages();
    }

    /**
     * Set the total number of items
     *
     * @param int $totalItems The total number of items
     *
     * @return Paginator
     */
    public function setTotalItems($totalItems)
    {
        $this->totalItems = intval($totalItems);
        return $this->updateTotalPages();
    }

    /**
     * Setup the paginator
     *
     * @param int $totalItems The total number of items
     * @param int $itemsPerPage The number of items per page
     * @param int $currentPage The current page number
     * @param Request $xRequest The request to be paginated
     *
     * @return Paginator
     */
    public function setup($totalItems, $itemsPerPage, $currentPage, $xRequest)
    {
        $this->setTotalItems($totalItems)
            ->setItemsPerPage($itemsPerPage)
            ->setCurrentPage($currentPage);
        $this->xRenderer->setRequest($xRequest);
        return $this;
    }

    /**
     * Get an array of paginated page data.
     *
     * Example: [1, 0, 4, 5, 6, 0, 10]
     *
     * @return array
     */
    protected function getPageNumbers()
    {
        $pageNumbers = [];

        if($this->totalPages <= $this->maxPagesToShow)
        {
            for($i = 0; $i < $this->totalPages; $i++)
            {
                $pageNumbers[] = $i + 1;
            }
        }
        else
        {
            // Determine the sliding range, centered around the current page.
            $numAdjacents = (int)floor(($this->maxPagesToShow - 4) / 2);

            $slidingStart = 1;
            $slidingEndOffset = $numAdjacents + 3 - $this->currentPage;
            if($slidingEndOffset < 0)
            {
                $slidingStart = $this->currentPage - $numAdjacents;
                $slidingEndOffset = 0;
            }

            $slidingEnd = $this->totalPages;
            $slidingStartOffset = $this->currentPage + $numAdjacents + 2 - $this->totalPages;
            if($slidingStartOffset < 0)
            {
                $slidingEnd = $this->currentPage + $numAdjacents;
                $slidingStartOffset = 0;
            }

            // Build the list of page numbers.
            if($slidingStart > 1)
            {
                $pageNumbers[] = 1;
                $pageNumbers[] = 0; // Ellipsys;
            }
            for($i = $slidingStart - $slidingStartOffset; $i <= $slidingEnd + $slidingEndOffset; $i++)
            {
                $pageNumbers[] = $i;
            }
            if($slidingEnd < $this->totalPages)
            {
                $pageNumbers[] = 0; // Ellipsys;
                $pageNumbers[] = $this->totalPages;
            }
        }

        return $pageNumbers;
    }

    /**
     * Render an HTML pagination control.
     *
     * @return string
     */
    public function render()
    {
        if($this->totalPages > 1)
        {
            return $this->xRenderer->render($this->getPageNumbers(), $this->currentPage, $this->totalPages);
        }
        return '';
    }

    /**
     * Render an HTML pagination control.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}
