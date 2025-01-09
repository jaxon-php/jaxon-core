<?php

use function Jaxon\jaxon;

class Sample
{
    public function myMethod()
    {
        $xResponse = jaxon()->getResponse();
        $xResponse->alert('This is a response!!');
    }
}
