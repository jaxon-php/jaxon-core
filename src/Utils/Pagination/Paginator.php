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
    protected $numPages = 0;

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
     * @var string
     */
    protected $previousText = '&laquo;';

    /**
     * @var string
     */
    protected $nextText = '&raquo;';

    /**
     * The pagination renderer
     *
     * @var Renderer
     */
    protected $renderer = null;

    /**
     * Tha Jaxon request to be paginated
     *
     * @var Request
     */
    protected $request = null;

    /**
     * The constructor
     *
     * @param Renderer $renderer
     */
    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    protected function updateNumPages()
    {
        $this->numPages = ($this->itemsPerPage == 0 ? 0 : (int)ceil($this->totalItems / $this->itemsPerPage));
    }

    /**
     * @param int $maxPagesToShow
     * @throws \InvalidArgumentException if $maxPagesToShow is less than 3.
     */
    public function setMaxPagesToShow($maxPagesToShow)
    {
        if($maxPagesToShow < 4)
        {
            throw new \InvalidArgumentException('maxPagesToShow cannot be less than 4.');
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
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        // Append the page number to the parameter list, if not yet given.
        if(($this->request) && !$this->request->hasPageNumber())
        {
            $this->request->addParameter(Parameter::PAGE_NUMBER, 0);
        }
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param int $totalItems
     * @param int $itemsPerPage
     * @param int $currentPage
     * @param Request $request
     *
     * @return Paginator
     */
    public function setup($totalItems, $itemsPerPage, $currentPage, $request)
    {
        $this->setTotalItems($totalItems);
        $this->setItemsPerPage($itemsPerPage);
        $this->setCurrentPage($currentPage);
        $this->setRequest($request);

        return $this;
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
     * [
     *     array ('num' => 1,     'call' => '/example/page/1',  'isCurrent' => false),
     *     array ('num' => '...', 'call' => NULL,               'isCurrent' => false),
     *     array ('num' => 3,     'call' => '/example/page/3',  'isCurrent' => false),
     *     array ('num' => 4,     'call' => '/example/page/4',  'isCurrent' => true ),
     *     array ('num' => 5,     'call' => '/example/page/5',  'isCurrent' => false),
     *     array ('num' => '...', 'call' => NULL,               'isCurrent' => false),
     *     array ('num' => 10,    'call' => '/example/page/10', 'isCurrent' => false),
     * ]
     *
     * @return array
     */
    public function getPages()
    {
        $pages = [];

        if($this->numPages <= 1)
        {
            return [];
        }

        if($this->numPages <= $this->maxPagesToShow)
        {
            for($i = 1; $i <= $this->numPages; $i++)
            {
                $pages[] = $this->createPage($i);
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

            $slidingEnd = $this->numPages;
            $slidingStartOffset = $this->currentPage + $numAdjacents + 2 - $this->numPages;
            if($slidingStartOffset < 0)
            {
                $slidingEnd = $this->currentPage + $numAdjacents;
                $slidingStartOffset = 0;
            }

            // Build the list of pages.
            if($slidingStart > 1)
            {
                $pages[] = $this->createPage(1);
                $pages[] = $this->createPageEllipsis();
            }
            for($i = $slidingStart - $slidingStartOffset; $i <= $slidingEnd + $slidingEndOffset; $i++)
            {
                $pages[] = $this->createPage($i);
            }
            if($slidingEnd < $this->numPages)
            {
                $pages[] = $this->createPageEllipsis();
                $pages[] = $this->createPage($this->numPages);
            }
        }

        return $pages;
    }


    /**
     * Create a page data structure.
     *
     * @param int $pageNum
     *
     * @return array<string,integer|string|boolean>
     */
    protected function createPage($pageNum)
    {
        return [
            'num' => $pageNum,
            'call' => $this->getPageCall($pageNum),
            'isCurrent' => ($this->currentPage == $pageNum),
        ];
    }

    /**
     * @return array<string,string|null|false>
     */
    protected function createPageEllipsis()
    {
        return [
            'num' => '...',
            'call' => null,
            'isCurrent' => false,
        ];
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

    /**
     * Render an HTML pagination control.
     *
     * @return string
     */
    public function render()
    {
        if($this->getNumPages() <= 1)
        {
            return '';
        }
        return $this->renderer->render($this);
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
