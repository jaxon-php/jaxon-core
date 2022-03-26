<?php

use Jaxon\Response\Response;

class Sample
{
    public function myMethod(): Response
    {
        $xResponse = jaxon()->getResponse();
        $xResponse->alert('This is a response!!');
        return $xResponse;
    }
}
