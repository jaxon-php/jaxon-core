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

use Jaxon\Utils\View\Store;
use Jaxon\Request\Factory\Request;

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
     */
    public function setMaxPagesToShow($maxPagesToShow)
    {
        $this->xRenderer->setMaxPagesToShow($maxPagesToShow);
        return $this;
    }

    /**
     * Set the current page number
     *
     * @param int $currentPage The current page number
     *
     * @return Paginator
     */
    protected function setCurrentPage($currentPage)
    {
        $this->xRenderer->setCurrentPage($currentPage);
        return $this;
    }

    /**
     * Set the number of items per page
     *
     * @param int $itemsPerPage The number of items per page
     *
     * @return Paginator
     */
    protected function setItemsPerPage($itemsPerPage)
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
    protected function setTotalItems($totalItems)
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
        $this->setTotalItems($totalItems)->setItemsPerPage($itemsPerPage)->setCurrentPage($currentPage);
        $this->xRenderer->setRequest($xRequest);
        return $this;
    }

    /**
     * Get the pages.
     *
     * @return array
     */
    public function getPages()
    {
        return array_map(function($aPage) {
            return (object)['type' => $aPage[0], 'text' => $aPage[1], 'call' => $aPage[2]];
        }, $this->xRenderer->getPages($this->totalPages));
    }

    /**
     * Render an HTML pagination control.
     *
     * @return null|Store
     */
    public function render()
    {
        return $this->xRenderer->render($this->totalPages);
    }

    /**
     * Render an HTML pagination control.
     *
     * @return string
     */
    public function __toString()
    {
        if($this->totalPages < 2)
        {
            return '';
        }
        return $this->render()->__toString();
    }
}
