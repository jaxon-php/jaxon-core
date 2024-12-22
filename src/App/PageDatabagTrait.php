<?php

namespace Jaxon\App;

use Jaxon\Plugin\Response\Pagination\Paginator;

trait PageDatabagTrait
{
    /**
     * The current page number.
     *
     * @var int
     */
    private int $currentPage = 1;

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
     * Get the page number.
     *
     * @param int $pageNumber
     *
     * @return int
     */
    private function getPageNumber(int $pageNumber): int
    {
        // If no page number is provided, then get the value from the databag.
        return $pageNumber > 0 ? $pageNumber :
            (int)$this->bag($this->bagName())->get($this->bagAttr(), 1);
    }

    /**
     * Set the page number.
     *
     * @param int $currentPage
     *
     * @return void
     */
    private function setCurrentPage(int $currentPage): void
    {
        // Save the current page in the databag.
        $this->bag($this->bagName())->set($this->bagAttr(), $currentPage);
        $this->currentPage = $currentPage;
    }

    /**
     * Get the paginator for the component.
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
            // This callback will receive the final value of the current page number.
            ->page(function(int $currentPage) {
                $this->setCurrentPage($currentPage);
            });
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
