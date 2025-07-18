<?php

namespace Jaxon\App;

use Jaxon\App\Pagination\Paginator;
use Jaxon\Di\Container;
use Jaxon\Plugin\Request\CallableClass\ComponentHelper;

class FuncComponent extends Component\AbstractComponent
{
    use Component\HelperTrait;
    use Component\AjaxResponseTrait;
    use Component\ComponentTrait;

    /**
     * @inheritDoc
     */
    final protected function initComponent(Container $di, ComponentHelper $xHelper)
    {
        $this->setHelper($xHelper);
        $this->setAjaxResponse($di);
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
