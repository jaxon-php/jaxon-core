<?php

use Jaxon\App\CallableClass;
use Jaxon\Response\Response;

class TestCb extends CallableClass
{
    public function simple(): Response
    {
        $this->response->alert('This is the global response!');
        return $this->response;
    }

    /**
     * @throws Exception
     */
    public function error(): Response
    {
        throw new Exception('This method throws an exception!');
    }
}
