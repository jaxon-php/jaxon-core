<?php

namespace Jaxon\App\Component;

use Jaxon\App\Databag\DatabagContext;
use Jaxon\Di\Container;
use Jaxon\Response\AjaxResponse;
use Jaxon\Response\Response;

trait AjaxResponseTrait
{
    /**
     * @var Response
     */
    protected readonly Response $response;

    /**
     * @param Container $di
     *
     * @return void
     */
    private function setAjaxResponse(Container $di): void
    {
        $this->response = $di->getResponse();
    }

    /**
     * Get the ajax response
     *
     * @return Response
     */
    final protected function response(): AjaxResponse
    {
        return $this->response;
    }

    /**
     * Get the ajax response
     *
     * @return Response
     */
    final protected function ajaxResponse(): AjaxResponse
    {
        return $this->response;
    }

    /**
     * Get a data bag.
     *
     * @param string  $sBagName
     *
     * @return DatabagContext
     */
    protected function bag(string $sBagName): DatabagContext
    {
        return $this->response()->bag($sBagName);
    }
}
