<?php

namespace Jaxon\App;

use Jaxon\App\Pagination\PageNumberInput;
use Jaxon\App\Pagination\PageNumberInputBag;

trait PageDatabagTrait
{
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
     * @return PageNumberInput
     */
    protected function makeInput(): PageNumberInput
    {
        $bag = $this->bag($this->bagName());
        return new PageNumberInputBag(fn() => (int)$bag->get($this->bagAttr(), 1),
            fn(int $currentPage) => $bag->set($this->bagAttr(), $currentPage));
    }
}
