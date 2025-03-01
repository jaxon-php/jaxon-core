<?php

namespace Jaxon\App\Component;

use Jaxon\Di\Container;
use Jaxon\Response\AjaxResponse;
use Jaxon\Response\Response;

trait AjaxResponseTrait
{
    /**
     * @var Response
     */
    protected $response;

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
     * @return Response
     */
    final protected function response(): AjaxResponse
    {
        return $this->response;
    }
}
