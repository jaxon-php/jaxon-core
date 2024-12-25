<?php

namespace Jaxon\App;

use Jaxon\App\Dialog\AlertInterface;
use Jaxon\App\Dialog\ModalInterface;

trait DialogTrait
{
    /**
     * @return AlertInterface
     */
    protected function alert(): AlertInterface
    {
        return $this->_response()->dialog;
    }

    /**
     * @return ModalInterface
     */
    protected function modal(): ModalInterface
    {
        return $this->_response()->dialog;
    }
}
