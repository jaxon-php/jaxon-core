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

use Jaxon\Jaxon;
use Jaxon\Request\Request;

class Paginator
{
    protected $totalItems = 0;
    protected $numPages = 0;
    protected $itemsPerPage = 0;
    protected $currentPage = 0;
    protected $maxPagesToShow = 10;
    protected $previousText = '';
    protected $nextText = '';
    protected $request = null;
    protected $renderer = null;

    /**
     * @param object $renderer
     */
    public function __construct($renderer)
    {
        $this->renderer = $renderer;
    }

    protected function updateNumPages()
    {
        $this->numPages = ($this->itemsPerPage == 0 ? 0 : (int) ceil($this->totalItems/$this->itemsPerPage));
    }

    /**
     * @param int $maxPagesToShow
     * @throws \InvalidArgumentException if $maxPagesToShow is less than 3.
     */
    public function setMaxPagesToShow($maxPagesToShow)
    {
        if($maxPagesToShow < 3)
        {
            throw new \InvalidArgumentException('maxPagesToShow cannot be less than 3.');
        }
        $this->maxPagesToShow = $maxPagesToShow;
    }

    /**
     * @return int
     */
    public function getMaxPagesToShow()
    {
        return $this->maxPagesToShow;
    }

    /**
     * @param int $currentPage
     */
    public function setCurrentPage($currentPage)
    {
        $this->currentPage = $currentPage;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * @param int $itemsPerPage
     */
    public function setItemsPerPage($itemsPerPage)
    {
        $this->itemsPerPage = $itemsPerPage;
        $this->updateNumPages();
    }

    /**
     * @return int
     */
    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }

    /**
     * @param int $totalItems
     */
    public function setTotalItems($totalItems)
    {
        $this->totalItems = $totalItems;
        $this->updateNumPages();
    }

    /**
     * @return int
     */
    public function getTotalItems()
    {
        return $this->totalItems;
    }

    /**
     * @return int
     */
    public function getNumPages()
    {
        return $this->numPages;
    }

    /**
     * @param \Jaxon\Request\Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
        // Append the page number to the parameter list, if not yet given.
        if(($this->request) && !$this->request->hasPageNumber())
        {
            $this->request->addParameter(Jaxon::PAGE_NUMBER, 0);
        }
    }

    /**
     * @return string
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param int $totalItems
     * @param int $itemsPerPage
     * @param int $currentPage
     * @param \Jaxon\Request\Request $request
     */
    public function setup($totalItems, $itemsPerPage, $currentPage, $request)
    {
        $this->setTotalItems($totalItems);
        $this->setItemsPerPage($itemsPerPage);
        $this->setCurrentPage($currentPage);
        $this->setRequest($request);
    }

    /**
     * @param int $pageNum
     * @return string
     */
    public function getPageCall($pageNum)
    {
        return $this->request->setPageNumber($pageNum)->getScript();
    }

    public function getNextPage()
    {
        if($this->currentPage < $this->numPages)
        {
            return $this->currentPage + 1;
        }

        return null;
    }

    public function getPrevPage()
    {
        if($this->currentPage > 1)
        {
            return $this->currentPage - 1;
        }

        return null;
    }

    public function getNextCall()
    {
        if(!$this->getNextPage())
        {
            return null;
        }

        return $this->getPageCall($this->getNextPage());
    }

    /**
     * @return string|null
     */
    public function getPrevCall()
    {
        if(!$this->getPrevPage())
        {
            return null;
        }

        return $this->getPageCall($this->getPrevPage());
    }

    /**
     * Get an array of paginated page data.
     *
     * Example:
     * array(
     *     array ('num' => 1,     'call' => '/example/page/1',  'isCurrent' => false),
     *     array ('num' => '...', 'call' => NULL,               'isCurrent' => false),
     *     array ('num' => 3,     'call' => '/example/page/3',  'isCurrent' => false),
     *     array ('num' => 4,     'call' => '/example/page/4',  'isCurrent' => true ),
     *     array ('num' => 5,     'call' => '/example/page/5',  'isCurrent' => false),
     *     array ('num' => '...', 'call' => NULL,               'isCurrent' => false),
     *     array ('num' => 10,    'call' => '/example/page/10', 'isCurrent' => false),
     * )
     *
     * @return array
     */
    public function getPages()
    {
        $pages = array();

        if($this->numPages <= 1)
        {
            return array();
        }

        if($this->numPages <= $this->maxPagesToShow)
        {
            for($i = 1; $i <= $this->numPages; $i++)
            {
                $pages[] = $this->createPage($i, $i == $this->currentPage);
            }
        }
        else
        {
            // Determine the sliding range, centered around the current page.
            $numAdjacents = (int) floor(($this->maxPagesToShow - 3) / 2);

            if($this->currentPage + $numAdjacents > $this->numPages)
            {
                $slidingStart = $this->numPages - $this->maxPagesToShow + 2;
            }
            else
            {
                $slidingStart = $this->currentPage - $numAdjacents;
            }
            if($slidingStart < 2)
            {
                $slidingStart = 2;
            }

            $slidingEnd = $slidingStart + $this->maxPagesToShow - 3;
            if($slidingEnd >= $this->numPages)
            {
                $slidingEnd = $this->numPages - 1;
            }

            // Build the list of pages.
            $pages[] = $this->createPage(1, $this->currentPage == 1);
            if($slidingStart > 2)
            {
                $pages[] = $this->createPageEllipsis();
            }
            for($i = $slidingStart; $i <= $slidingEnd; $i++)
            {
                $pages[] = $this->createPage($i, $i == $this->currentPage);
            }
            if($slidingEnd < $this->numPages - 1)
            {
                $pages[] = $this->createPageEllipsis();
            }
            $pages[] = $this->createPage($this->numPages, $this->currentPage == $this->numPages);
        }

        return $pages;
    }


    /**
     * Create a page data structure.
     *
     * @param int $pageNum
     * @param bool $isCurrent
     * @return Array
     */
    protected function createPage($pageNum, $isCurrent = false)
    {
        return array(
            'num' => $pageNum,
            'call' => $this->getPageCall($pageNum),
            'isCurrent' => $isCurrent,
        );
    }

    /**
     * @return array
     */
    protected function createPageEllipsis()
    {
        return array(
            'num' => '...',
            'call' => null,
            'isCurrent' => false,
        );
    }

    /**
     * Render an HTML pagination control.
     *
     * @return string
     */
    public function toHtml()
    {
        if($this->getNumPages() <= 1)
        {
            return '';
        }

        $this->renderer->setPaginator($this);
        return $this->renderer->render();
    }

    /**
     * Render an HTML pagination control.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toHtml();
    }

    public function getCurrentPageFirstItem()
    {
        $first = ($this->currentPage - 1) * $this->itemsPerPage + 1;

        if($first > $this->totalItems)
        {
            return null;
        }

        return $first;
    }

    public function getCurrentPageLastItem()
    {
        $first = $this->getCurrentPageFirstItem();
        if($first === null)
        {
            return null;
        }

        $last = $first + $this->itemsPerPage - 1;
        if($last > $this->totalItems)
        {
            return $this->totalItems;
        }

        return $last;
    }

    public function setPreviousText($text)
    {
        $this->previousText = $text;
        return $this;
    }

    public function getPreviousText()
    {
        return $this->previousText;
    }

    public function setNextText($text)
    {
        $this->nextText = $text;
        return $this;
    }

    public function getNextText()
    {
        return $this->nextText;
    }
}
