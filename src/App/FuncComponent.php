<?php

namespace Jaxon\App;

use Jaxon\App\Component\ComponentFactory;
use Jaxon\App\Pagination\FuncPaginator;
use Jaxon\Di\Container;

abstract class FuncComponent extends Component\BaseComponent
{
    use Component\AjaxResponseTrait;

    /**
     * @inheritDoc
     */
    final protected function initComponent(Container $di, ComponentFactory $xFactory): void
    {
        $this->setFactory($xFactory);
        $this->setAjaxResponse($di);
        // Allow the user app to setup the component.
        $this->setupComponent();
    }

    /**
     * Create a paginator.
     *
     * @param int $nPageNumber      The current page number
     * @param int $nItemsPerPage    The number of items per page
     * @param int $nTotalItems      The total number of items
     *
     * @return FuncPaginator
     */
    final public function paginator(int $nPageNumber, int $nItemsPerPage, int $nTotalItems): FuncPaginator
    {
        return new FuncPaginator($nPageNumber, $nItemsPerPage, $nTotalItems,
            $this->factory()->helper()->xPaginationRenderer, $this->response());
    }
}
