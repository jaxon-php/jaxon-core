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

namespace Jaxon\Ui\Pagination;

use Jaxon\Request\Call\Call;
use Jaxon\Ui\View\Store;
use Jaxon\Exception\SetupException;

use function array_map;
use function ceil;

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
     * @param string $sText The text for the previous page link
     *
     * @return Paginator
     */
    public function setPreviousText(string $sText): Paginator
    {
        $this->xRenderer->setPreviousText($sText);
        return $this;
    }

    /**
     * Set the text for the next page link
     *
     * @param string $sText The text for the previous page link
     *
     * @return Paginator
     */
    public function setNextText(string $sText): Paginator
    {
        $this->xRenderer->setNextText($sText);
        return $this;
    }

    /**
     * Update the number of pages
     *
     * @return Paginator
     */
    protected function updateTotalPages(): Paginator
    {
        $this->nTotalPages = ($this->nItemsPerPage == 0 ? 0 : (int)ceil($this->nTotalItems / $this->nItemsPerPage));
        return $this;
    }

    /**
     * Set the max number of pages to show
     *
     * @param int $nMaxPagesToShow The max number of pages to show
     *
     * @return Paginator
     */
    public function setMaxPagesToShow(int $nMaxPagesToShow): Paginator
    {
        $this->xRenderer->setMaxPagesToShow($nMaxPagesToShow);
        return $this;
    }

    /**
     * Set the current page number
     *
     * @param int $nCurrentPage The current page number
     *
     * @return Paginator
     */
    protected function setCurrentPage(int $nCurrentPage): Paginator
    {
        $this->xRenderer->setCurrentPage($nCurrentPage);
        return $this;
    }

    /**
     * Set the number of items per page
     *
     * @param int $nItemsPerPage The number of items per page
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
     * @param int $nTotalItems The total number of items
     *
     * @return Paginator
     */
    protected function setTotalItems(int $nTotalItems): Paginator
    {
        $this->nTotalItems = $nTotalItems;
        return $this->updateTotalPages();
    }

    /**
     * Setup the paginator
     *
     * @param int $nTotalItems The total number of items
     * @param int $nItemsPerPage The number of items per page
     * @param int $nCurrentPage The current page number
     * @param Call $xCall The call to be paginated
     *
     * @return Paginator
     */
    public function setup(int $nTotalItems, int $nItemsPerPage, int $nCurrentPage, Call $xCall): Paginator
    {
        $this->setTotalItems($nTotalItems)->setItemsPerPage($nItemsPerPage)->setCurrentPage($nCurrentPage);
        $this->xRenderer->setRequest($xCall);
        return $this;
    }

    /**
     * Get the pages.
     *
     * @return array
     * @throws SetupException
     */
    public function getPages(): array
    {
        return array_map(function($aPage) {
            return (object)['type' => $aPage[0], 'text' => $aPage[1], 'call' => $aPage[2]];
        }, $this->xRenderer->getPages($this->nTotalPages));
    }

    /**
     * Render an HTML pagination control.
     *
     * @return null|Store
     * @throws SetupException
     */
    public function render(): ?Store
    {
        return $this->xRenderer->render($this->nTotalPages);
    }

    /**
     * Render an HTML pagination control.
     *
     * @return string
     * @throws SetupException
     */
    public function __toString()
    {
        if($this->nTotalPages < 2)
        {
            return '';
        }
        return $this->render()->__toString();
    }
}
