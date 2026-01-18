<?php

use Jaxon\App\FuncComponent;

class TestCb extends FuncComponent
{
    public function simple()
    {
        $this->response()->alert('This is the global response!');
    }

    /**
     * @throws Exception
     */
    public function error()
    {
        throw new Exception('This method throws an exception!');
    }
}
