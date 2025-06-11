<?php

class Sample
{
    public function myMethod()
    {
        $xResponse = jaxon()->getResponse();
        $xResponse->alert('This is a response!!');
    }
}
