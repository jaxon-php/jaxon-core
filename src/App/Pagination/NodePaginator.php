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

use function trim;

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
        private PaginationRenderer $xRenderer, private NodeResponse $xResponse)
    {
        parent::__construct($nPageNumber, $nItemsPerPage, $nItemsCount);
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
        if(($xFunc = $xCall->func()) === null)
        {
            return;
        }

        $sHtml = trim((string)$this->xRenderer->getHtml($this));
        // The HTML code must always be displayed, even if it is empty.
        $this->xResponse->html($sHtml);

        // Set click handlers on the pagination links
        if($sHtml !== '')
        {
            $aParams = [
                'func' => $xFunc->withPage()->jsonSerialize(),
            ];
            $this->xResponse->addCommand('pg.paginate', $aParams);
        }
    }
}
