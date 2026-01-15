<?php

namespace Jaxon\App;

use Jaxon\App\Pagination\Paginator;

abstract class PageComponent extends NodeComponent
{
    /**
     * The current page number.
     *
     * @var int
     */
    private int $currentPage = 1;

    /**
     * Get the total number of items to paginate.
     *
     * @return int
     */
    abstract protected function count(): int;

    /**
     * Get the max number of items per page.
     *
     * @return int
     */
    abstract protected function limit(): int;

    /**
     * Get the paginator for the component.
     *
     * @param int $pageNumber
     *
     * @return Paginator
     */
    protected function paginator(int $pageNumber): Paginator
    {
        return $this->cl(Component\Pagination::class)
            // Use the js class name as component item identifier.
            ->item($this->rq()->_class())
            ->paginator($pageNumber > 0 ? $pageNumber : 1, $this->limit(), $this->count())
            // This callback will receive the final value of the current page number.
            ->page(fn(int $currentPage) => $this->currentPage = $currentPage);
    }

    /**
     * Get the current page number
     *
     * @return int
     */
    protected function currentPage(): int
    {
        return $this->currentPage;
    }
}
