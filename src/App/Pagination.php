<?php

namespace Jaxon\App;

use Jaxon\App\Pagination\Paginator;

class Pagination extends AbstractNodeComponent
{
    /**
     * Create a paginator.
     *
     * @param int $nPageNumber      The current page number
     * @param int $nItemsPerPage    The number of items per page
     * @param int $nTotalItems      The total number of items
     *
     * @return Paginator
     */
    final public function paginator(int $nPageNumber, int $nItemsPerPage, int $nTotalItems): Paginator
    {
        return $this->ajaxResponse()->paginator($nPageNumber, $nItemsPerPage, $nTotalItems);
    }

    /**
     * @inheritDoc
     */
    final public function render()
    {}
}
