<?php

namespace Jaxon\App\Component;

use Jaxon\App\Component\ComponentFactory;
use Jaxon\App\Pagination\Paginator;
use Jaxon\Di\Container;

class Pagination extends AbstractComponent
{
    use NodeResponseTrait;

    /**
     * @inheritDoc
     */
    final protected function initComponent(Container $di, ComponentFactory $xFactory): void
    {
        $this->setFactory($xFactory);
        $this->setNodeResponse($di);
    }

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
        return $this->node()->paginator($nPageNumber, $nItemsPerPage, $nTotalItems);
    }
}
