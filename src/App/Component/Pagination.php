<?php

namespace Jaxon\App\Component;

use Jaxon\App\Pagination\Paginator;
use Jaxon\Di\Container;
use Jaxon\Plugin\Request\CallableClass\ComponentHelper;

class Pagination extends AbstractComponent
{
    use HelperTrait;
    use NodeResponseTrait;

    /**
     * @inheritDoc
     */
    final protected function initComponent(Container $di, ComponentHelper $xHelper): void
    {
        $this->setHelper($xHelper);
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
        return $this->nodeResponse->paginator($nPageNumber, $nItemsPerPage, $nTotalItems);
    }
}
