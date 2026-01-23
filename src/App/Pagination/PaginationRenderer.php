<?php

/**
 * Renderer.php
 *
 * The default pagination renderer.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Pagination;

use Jaxon\App\View\ViewRenderer;

use function array_map;
use function count;
use function trim;

class PaginationRenderer
{
    /**
     * The constructor.
     *
     * @param ViewRenderer $xRenderer
     */
    public function __construct(private ViewRenderer $xRenderer)
    {
        $this->xRenderer = $xRenderer;
    }

    /**
     * @param Page $xPage
     *
     * @return string
     */
    private function renderPage(Page $xPage): string
    {
        return $this->xRenderer->render("pagination::links/{$xPage->sType}", [
            'page' => $xPage->nNumber,
            'text' => $xPage->sText,
        ])->__toString();
    }

    /**
     * Render an array of pagination links
     *
     * @param Page[] $aPages
     * @param Page $xPrevPage
     * @param Page $xNextPage
     *
     * @return string
     */
    private function render(array $aPages, Page $xPrevPage, Page $xNextPage): string
    {
        return trim($this->xRenderer->render('pagination::wrapper', [
            'links' => array_map(fn($xPage) => $this->renderPage($xPage), $aPages),
            'prev' => $this->renderPage($xPrevPage),
            'next' => $this->renderPage($xNextPage),
        ])->__toString());
    }

    /**
     * @inheritDoc
     */
    public function getHtml(Paginator $xPaginator): string
    {
        [$xPrevPage, $aPages, $xNextPage] = $xPaginator->pages();
        if($xPrevPage === null || $xNextPage === null || count($aPages) === 0)
        {
            return '';
        }

        return $this->render($aPages, $xPrevPage, $xNextPage);
    }
}
