<?php

/**
 * Renderer.php - Paginator renderer
 *
 * Render pagination links.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Ui\Pagination;

use Jaxon\Request\Call\Call;
use Jaxon\Request\Call\Parameter;
use Jaxon\Ui\View\Renderer as ViewRenderer;
use Jaxon\Ui\View\Store;

use function array_map;
use function array_pop;
use function array_shift;
use function array_walk;
use function floor;

class Renderer
{
    /**
     * The template renderer.
     *
     * Will be used to render HTML code for links.
     *
     * @var ViewRenderer
     */
    protected $xRenderer = null;

    /**
     * The Jaxon request to be paginated
     *
     * @var Call
     */
    protected $xCall = null;

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
     * @var integer
     */
    protected $nTotalPages = 0;

    /**
     * @var integer
     */
    protected $nCurrentPage = 0;

    /**
     * @var integer
     */
    protected $nMaxPagesToShow = 10;

    /**
     * The class contructor
     *
     * @param ViewRenderer  $xRenderer
     */
    public function __construct(ViewRenderer $xRenderer)
    {
        $this->xRenderer = $xRenderer;
    }

    /**
     * Set the text for the previous page link
     *
     * @param string $sText    The text for the previous page link
     *
     * @return void
     */
    public function setPreviousText(string $sText)
    {
        $this->sPreviousText = $sText;
    }

    /**
     * Set the text for the next page link
     *
     * @param string $sText    The text for the previous page link
     *
     * @return void
     */
    public function setNextText(string $sText)
    {
        $this->sNextText = $sText;
    }

    /**
     * Set the request to be paginated
     *
     * @param Call $xCall    The request to be paginated
     *
     * @return void
     */
    public function setRequest(Call $xCall)
    {
        $this->xCall = $xCall;
        // Append the page number to the parameter list, if not yet given.
        if(!$this->xCall->hasPageNumber())
        {
            $this->xCall->addParameter(Parameter::PAGE_NUMBER, 0);
        }
    }

    /**
     * Set the current page number
     *
     * @param int $nCurrentPage    The current page number
     *
     * @return void
     */
    public function setCurrentPage(int $nCurrentPage)
    {
        $this->nCurrentPage = $nCurrentPage;
    }

    /**
     * Set the max number of pages to show
     *
     * @param int $nMaxPagesToShow    The max number of pages to show
     *
     * @return void
     */
    public function setMaxPagesToShow(int $nMaxPagesToShow)
    {
        $this->nMaxPagesToShow = $nMaxPagesToShow;
        if($this->nMaxPagesToShow < 4)
        {
            $this->nMaxPagesToShow = 4;
        }
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
     * Render a link to a page.
     *
     * @param string $sTemplate    The template for the link to the page
     * @param string $sText    The text of the link if it is enabled
     * @param string $sCall    The call of the link if it is enabled
     *
     * @return null|Store
     */
    protected function renderLink(string $sTemplate, string $sText, string $sCall): ?Store
    {
        return $this->xRenderer->render('pagination::links/' . $sTemplate, [
            'text' => $sText,
            'call' => $sCall,
        ]);
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

        if($this->nTotalPages <= $this->nMaxPagesToShow)
        {
            for($i = 0; $i < $this->nTotalPages; $i++)
            {
                $aPageNumbers[] = $i + 1;
            }

            return $aPageNumbers;
        }

        // Determine the sliding range, centered around the current page.
        $nNumAdjacents = (int)floor(($this->nMaxPagesToShow - 4) / 2);

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
     * Get the pages.
     *
     * @param integer $nTotalPages    The total number of pages
     *
     * @return array
     */
    public function getPages(int $nTotalPages): array
    {
        $this->nTotalPages = $nTotalPages;

        $aPageNumbers = $this->getPageNumbers();
        $aPages = [$this->getPrevLink()];
        array_walk($aPageNumbers, function($nNumber) use(&$aPages) {
            $aPages[] = $this->getPageLink($nNumber);
        });
        $aPages[] = $this->getNextLink();

        return $aPages;
    }

    /**
     * Render an HTML pagination control.
     *
     * @param integer $nTotalPages    The total number of pages
     *
     * @return null|Store
     */
    public function render(int $nTotalPages): ?Store
    {
        $aLinks = array_map(function($aPage) {
            return $this->renderLink($aPage[0], $aPage[1], $aPage[2]);
        }, $this->getPages($nTotalPages));

        $aPrevLink = array_shift($aLinks); // The first entry in the array
        $aNextLink = array_pop($aLinks); // The last entry in the array
        return $this->xRenderer->render('pagination::wrapper',
            ['links' => $aLinks, 'prev' => $aPrevLink, 'next' => $aNextLink]);
    }
}
