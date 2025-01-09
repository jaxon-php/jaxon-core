<?php

use Jaxon\App\CallableClass;

class TestCb extends CallableClass
{
    public function simple()
    {
        $this->response->alert('This is the global response!');
    }

    /**
     * @throws Exception
     */
    public function error()
    {
        throw new Exception('This method throws an exception!');
    }
}
