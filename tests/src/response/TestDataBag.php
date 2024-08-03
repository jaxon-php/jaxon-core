<?php

use Jaxon\App\CallableClass;
use Jaxon\Response\Response;

class TestDataBag extends CallableClass
{
    public function getValue(): Response
    {
        $sValue = $this->response->bag('dataset')->get('key', 'Default value');
        $this->response->html('div-id', $sValue);
        return $this->response;
    }

    public function setValue(): Response
    {
        $this->response->bag('dataset')->set('key', 'value');
        return $this->response;
    }

    public function updateValue(): Response
    {
        $this->response->bag('dataset')->set('key2', 'value2');
        $sValue = $this->response->bag('dataset')->get('key1', 'Default value');
        $this->response->html('div-id', $sValue);
        return $this->response;
    }
}
