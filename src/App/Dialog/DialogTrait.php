<?php

namespace Jaxon\App\Dialog;

use Jaxon\Response\AjaxResponse;

trait DialogTrait
{
    /**
     * @return AjaxResponse
     */
    abstract protected function response(): AjaxResponse;

    /**
     * @return AlertInterface
     */
    protected function alert(): AlertInterface
    {
        return $this->response()->dialog;
    }

    /**
     * @return ModalInterface
     */
    protected function modal(): ModalInterface
    {
        return $this->response()->dialog;
    }
}
