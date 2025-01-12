<?php

namespace Jaxon\App;

use Jaxon\Plugin\Response\Pagination\Paginator;

class Pagination extends AbstractComponent
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
        return $this->_response()->paginator($nPageNumber, $nItemsPerPage, $nTotalItems);
    }

    /**
     * @inheritDoc
     */
    final public function render()
    {}
}
