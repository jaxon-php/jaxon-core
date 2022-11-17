<?php

use Jaxon\Response\Response;
use function Jaxon\jaxon;

class Sample
{
    public function myMethod(): Response
    {
        $xResponse = jaxon()->getResponse();
        $xResponse->alert('This is a response!!');
        return $xResponse;
    }
}
