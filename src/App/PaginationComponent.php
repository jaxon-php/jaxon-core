<?php

namespace Jaxon\App;

use Jaxon\Plugin\Response\Pagination\Paginator;

class PaginationComponent extends AbstractCallable
{
    use ComponentTrait;

    /**
     * @var int
     */
    private $nPageNumber = 0;

    /**
     * @var int
     */
    private $nTotalItems = 0;

    /**
     * @var int
     */
    private $nItemsPerPage = 10;

    /**
     * @param int $nPageNumber
     *
     * @return PaginationComponent
     */
    final public function pageNumber(int $nPageNumber): PaginationComponent
    {
        $this->nPageNumber = $nPageNumber;

        return $this;
    }

    /**
     * @param int $nTotalItems
     *
     * @return PaginationComponent
     */
    final public function totalItems(int $nTotalItems): PaginationComponent
    {
        $this->nTotalItems = $nTotalItems;

        return $this;
    }

    /**
     * @param int $nItemsPerPage
     *
     * @return PaginationComponent
     */
    final public function itemsPerPage(int $nItemsPerPage): PaginationComponent
    {
        $this->nItemsPerPage = $nItemsPerPage;

        return $this;
    }

    /**
     * @return Paginator
     */
    final public function paginator(): Paginator
    {
        return $this->_response()->paginator($this->nPageNumber, $this->nItemsPerPage, $this->nTotalItems);
    }
}
