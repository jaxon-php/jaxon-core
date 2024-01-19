<?php

use Jaxon\Response\Response;
use function Jaxon\jaxon;

class ClassD
{
    public function methodDa(): Response
    {
        $xResponse = jaxon()->getResponse();
        $xResponse->html('div', 'This is the div content!!');
        return $xResponse;
    }
}
