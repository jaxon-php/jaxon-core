<?php

namespace Jaxon\App\Component;

use Jaxon\Plugin\Request\CallableClass\ComponentHelper;

trait HelperTrait
{
    /**
     * @var ComponentHelper
     */
    private $xHelper;

    /**
     * @param ComponentHelper $xHelper
     *
     * @return void
     */
    private function setHelper(ComponentHelper $xHelper): void
    {
        $this->xHelper = $xHelper;
    }

    /**
     * @return ComponentHelper
     */
    protected function helper(): ComponentHelper
    {
        return $this->xHelper;
    }
}
