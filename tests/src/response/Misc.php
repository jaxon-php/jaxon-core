<?php

use Jaxon\App\CallableClass;
use function Jaxon\jaxon;

class Misc extends CallableClass
{
    public function simple()
    {
        $this->response->alert('This is the global response!');
        $aCommands = $this->response->getCommands();
        $aCommands[0]->setOption('name', 'value');
    }

    public function merge()
    {
        $this->response->alert('This is the global response!');

        $xResponse = jaxon()->newResponse();
        $xResponse->debug('This is a different response!');
    }

    public function appendbefore()
    {
        $this->response->alert('This is the global response!');
        $xResponse = jaxon()->newResponse();
        $xResponse->debug('This is a different response!');
    }
}
