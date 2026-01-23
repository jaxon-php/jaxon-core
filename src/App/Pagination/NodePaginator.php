<?php

/**
 * NodePaginator.php
 *
 * The paginator for node components.
 *
 * @package jaxon-core
 * @copyright 2026 Thierry Feuzeu
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Pagination;

use Jaxon\Response\NodeResponse;
use Jaxon\Script\JsExpr;

class NodePaginator extends Paginator
{
    /**
     * The constructor.
     *
     * @param int $nPageNumber      The current page number
     * @param int $nItemsPerPage    The number of items per page
     * @param int $nItemsCount      The total number of items
     * @param PaginationRenderer $xRenderer
     * @param NodeResponse $xResponse
     */
    public function __construct(int $nPageNumber, int $nItemsPerPage, int $nItemsCount,
        PaginationRenderer $xRenderer, private NodeResponse $xResponse)
    {
        parent::__construct($nPageNumber, $nItemsPerPage, $nItemsCount, $xRenderer);
    }

    /**
     * @inheritDoc
     */
    protected function showHtml(string $sHtml, array $aParams): void
    {
        [$aFunc] = $aParams;
        // The HTML code must always be displayed, even if it is empty.
        $this->xResponse->html($sHtml);
        // Set click handlers on the pagination links
        if($sHtml !== '')
        {
            $aParams = ['func' => $aFunc];
            $this->xResponse->addCommand('pg.paginate', $aParams);
        }
    }

    /**
     * Render the pagination links with a given javascript call.
     *
     * @param JsExpr $xCall
     *
     * @return void
     */
    public function render(JsExpr $xCall): void
    {
        $this->paginate($xCall);
    }
}
