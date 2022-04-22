<?php

namespace Jaxon\NsTests\DirB;

use Jaxon\Response\ResponseInterface;

class ClassB
{
    public function methodBa(): ResponseInterface
    {
        $xResponse = jaxon()->getResponse();
        $xResponse->html('div', 'This is the div content!!');
        return $xResponse;
    }

    public function methodBb(): ResponseInterface
    {
        $xResponse = jaxon()->getResponse();
        $xResponse->html('div', 'This is the div content!!');
        return $xResponse;
    }
}
