<?php

use Jaxon\Response\Response;
use function Jaxon\jaxon;

class ClassB
{
    public function methodBa(): Response
    {
        $xResponse = jaxon()->getResponse();
        $xResponse->html('div', 'This is the div content!!');
        return $xResponse;
    }

    public function methodBb(): Response
    {
        $xResponse = jaxon()->getResponse();
        $xResponse->html('div', 'This is the div content!!');
        return $xResponse;
    }
}
