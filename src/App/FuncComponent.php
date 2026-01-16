<?php

namespace Jaxon\App;

use Jaxon\App\Pagination\Paginator;
use Jaxon\Di\Container;
use Jaxon\Plugin\Request\CallableClass\ComponentHelper;

abstract class FuncComponent extends Component\AbstractComponent
{
    use Component\HelperTrait;
    use Component\AjaxResponseTrait;
    use Component\ComponentTrait;

    /**
     * @inheritDoc
     */
    final protected function initComponent(Container $di, ComponentHelper $xHelper): void
    {
        $this->setHelper($xHelper);
        $this->setAjaxResponse($di);
        // Allow the user app to setup the component.
        $this->setupComponent();
    }

    /**
     * Create a paginator.
     *
     * @param int $nPageNumber     The current page number
     * @param int $nItemsPerPage    The number of items per page
     * @param int $nTotalItems      The total number of items
     *
     * @return Paginator
     */
    final public function paginator(int $nPageNumber, int $nItemsPerPage, int $nTotalItems): Paginator
    {
        return $this->response->paginator($nPageNumber, $nItemsPerPage, $nTotalItems);
    }
}
