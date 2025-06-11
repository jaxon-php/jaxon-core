<?php

namespace Jaxon\NsTests\DirA;

class ClassA
{
    public function methodAa()
    {
        $xResponse = jaxon()->getResponse();
        $xResponse->html('div', 'This is the div content!!');
    }

    public function methodAb()
    {
        $xResponse = jaxon()->getResponse();
        $xResponse->html('div', 'This is the div content!!');
    }
}
