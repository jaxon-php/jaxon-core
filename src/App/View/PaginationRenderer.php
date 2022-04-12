<?php

/**
 * PaginationRenderer.php - Paginator renderer
 *
 * Render pagination links.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\View;

use Jaxon\Request\Call\Paginator;

use function array_map;
use function array_pop;
use function array_shift;

class PaginationRenderer
{
    /**
     * The viev renderer.
     *
     * @var ViewRenderer
     */
    protected $xRenderer = null;

    /**
     * The class constructor
     *
     * @param ViewRenderer  $xRenderer
     */
    public function __construct(ViewRenderer $xRenderer)
    {
        $this->xRenderer = $xRenderer;
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
     * Render an HTML pagination control.
     *
     * @param Paginator $xPaginator The paginator
     *
     * @return null|Store
     */
    public function render(Paginator $xPaginator): ?Store
    {
        $aLinks = array_map(function($aPage) {
            return $this->renderLink($aPage[0], $aPage[1], $aPage[2]);
        }, $xPaginator->links());
        $aPrevLink = array_shift($aLinks); // The first entry in the array
        $aNextLink = array_pop($aLinks); // The last entry in the array

        return $this->xRenderer->render('pagination::wrapper', [
            'links' => $aLinks,
            'prev' => $aPrevLink,
            'next' => $aNextLink,
        ]);
    }
}
