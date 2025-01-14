<?php

namespace Jaxon\App\Dialog;

use Jaxon\Response\AjaxResponse;

trait DialogTrait
{
    /**
     * @return AjaxResponse
     */
    abstract protected function ajaxResponse(): AjaxResponse;

    /**
     * @return AlertInterface
     */
    protected function alert(): AlertInterface
    {
        return $this->ajaxResponse()->dialog;
    }

    /**
     * @return ModalInterface
     */
    protected function modal(): ModalInterface
    {
        return $this->ajaxResponse()->dialog;
    }
}
