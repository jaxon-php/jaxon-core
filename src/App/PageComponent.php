<?php

namespace Jaxon\App;

use Jaxon\Plugin\Response\Pagination\Paginator;

abstract class PageComponent extends Component
{
    /**
     * The current page number.
     *
     * @var int
     */
    private int $pageNumber = 1;

    /**
     * Get the pagination databag name.
     *
     * @return string
     */
    abstract protected function bagName(): string;

    /**
     * Get the pagination databag attribute.
     *
     * @return string
     */
    abstract protected function bagAttr(): string;

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
     * Get the page number.
     *
     * @param int $pageNumber
     *
     * @return int
     */
    private function getPageNumber(int $pageNumber): int
    {
        return $pageNumber > 0 ? $pageNumber :
            (int)$this->bag($this->bagName())->get($this->bagAttr(), 1);
    }

    /**
     * Set the page number.
     *
     * @param int $pageNumber
     *
     * @return void
     */
    private function setPageNumber(int $pageNumber): void
    {
        $this->bag($this->bagName())->set($this->bagAttr(), $pageNumber);
        $this->pageNumber = $pageNumber;
    }

    /**
     * Render a page, and return a paginator for the component.
     *
     * @param int $pageNumber
     *
     * @return Paginator
     */
    protected function paginator(int $pageNumber): Paginator
    {
        return $this->cl(Pagination::class)
            // Use the js class name as component item identifier.
            ->item($this->rq()->_class())
            ->paginator($this->getPageNumber($pageNumber), $this->limit(), $this->count())
            // This callback will receive the final value of the page number.
            ->page(function(int $page) {
                $this->setPageNumber($page);
            });
    }

    /**
     * Get the current page number
     *
     * @return int
     */
    protected function pageNumber(): int
    {
        return $this->pageNumber;
    }
}
