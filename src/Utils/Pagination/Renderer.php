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

namespace Jaxon\Utils\Pagination;

use Jaxon\Contracts\Template\Renderer as TemplateRenderer;

use Jaxon\Request\Factory\Request;
use Jaxon\Request\Factory\Parameter;

class Renderer
{
    /**
     * The template renderer.
     *
     * Will be used to render HTML code for links.
     *
     * @var TemplateRenderer
     */
    protected $xRenderer = null;

    /**
     * The Jaxon request to be paginated
     *
     * @var Request
     */
    protected $xRequest = null;

    /**
     * @var string
     */
    protected $previousText = '&laquo;';

    /**
     * @var string
     */
    protected $nextText = '&raquo;';

    /**
     * @var string
     */
    protected $ellipsysText = '...';

    /**
     * @var integer
     */
    protected $totalPages = 0;

    /**
     * @var integer
     */
    protected $currentPage = 0;

    /**
     * The class contructor
     *
     * @param TemplateRenderer          $xRenderer
     */
    public function __construct(TemplateRenderer $xRenderer)
    {
        $this->xRenderer = $xRenderer;
    }

    /**
     * Set the text for the previous page link
     *
     * @param string $text The text for the previous page link
     *
     * @return void
     */
    public function setPreviousText($text)
    {
        $this->previousText = $text;
    }

    /**
     * Set the text for the next page link
     *
     * @param string $text The text for the previous page link
     *
     * @return void
     */
    public function setNextText($text)
    {
        $this->nextText = $text;
    }

    /**
     * Set the request to be paginated
     *
     * @param Request $xRequest The request to be paginated
     *
     * @return void
     */
    public function setRequest(Request $xRequest)
    {
        $this->xRequest = $xRequest;
        // Append the page number to the parameter list, if not yet given.
        if(($this->xRequest) && !$this->xRequest->hasPageNumber())
        {
            $this->xRequest->addParameter(Parameter::PAGE_NUMBER, 0);
        }
    }

    /**
     * Get the js call to a given page
     *
     * @param int $pageNum The page number
     *
     * @return string
     */
    protected function getPageCall($pageNum)
    {
        return $this->xRequest->setPageNumber($pageNum)->getScript();
    }

    /**
     * Render the previous link.
     *
     * @param integer   $nNumber        The page number
     * @param string    $sTemplate      The template for the call to the page
     * @param string    $sEnabledText   The text of the link if it is enabled
     * @param string    $sDisabledText  The text of the link if it is disabled
     *
     * @return string
     */
    protected function getLink($nNumber, $sTemplate, $sEnabledText, $sDisabledText)
    {
        if($nNumber > 0)
        {
            return $this->xRenderer->render('pagination::links/' . $sTemplate, [
                'text' => $sEnabledText,
                'call' => $this->getPageCall($nNumber),
            ]);
        }
        return $this->xRenderer->render('pagination::links/disabled', ['text' => $sDisabledText]);
    }

    /**
     * Render the previous link.
     *
     * @return string
     */
    protected function getPrevLink()
    {
        $nNumber = ($this->currentPage > 1 ? $this->currentPage - 1 : 0);
        return $this->getLink($nNumber, 'prev', $this->previousText, $this->previousText);
    }

    /**
     * Render the next link.
     *
     * @return string
     */
    protected function getNextLink()
    {
        $nNumber = ($this->currentPage < $this->totalPages ? $this->currentPage + 1 : 0);
        return $this->getLink($nNumber, 'next', $this->nextText, $this->nextText);
    }

    /**
     * Render the pagination links.
     *
     * @param integer        $nNumber         The page number
     *
     * @return string
     */
    protected function getPageLink($nNumber)
    {
        $sTemplate = ($nNumber == $this->currentPage ? 'current' : 'enabled');
        return $this->getLink($nNumber, $sTemplate, $nNumber, $this->ellipsysText);
    }

    /**
     * Render an HTML pagination control.
     *
     * @param array     $aPageNumbers       The page numbers to be rendered
     * @param integer   $currentPage        The current page number
     * @param integer   $totalPages         The total number of pages
     *
     * @return string
     */
    public function render(array $aPageNumbers, $currentPage, $totalPages)
    {
        $this->currentPage = $currentPage;
        $this->totalPages = $totalPages;

        $aLinks = array_map(function($nNumber) {
            return $this->getPageLink($nNumber);
        }, $aPageNumbers);

        return $this->xRenderer->render('pagination::wrapper', [
            'links' => $aLinks,
            'prev' => $this->getPrevLink(),
            'next' => $this->getNextLink(),
        ]);
    }
}
