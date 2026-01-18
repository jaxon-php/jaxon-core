<?php

use Jaxon\App\FuncComponent;

class TestDatabag extends FuncComponent
{
    public function getValue()
    {
        $sValue = $this->response()->bag('dataset')->get('key', 'Default value');
        $this->response()->html('div-id', $sValue);
    }

    public function setValue()
    {
        $this->response()->bag('dataset')->set('key', 'value');
    }

    public function updateValue()
    {
        $this->response()->bag('dataset')->set('key2', 'value2');
        $sValue = $this->response()->bag('dataset')->get('key1', 'Default value');
        $this->response()->html('div-id', $sValue);
    }
}
