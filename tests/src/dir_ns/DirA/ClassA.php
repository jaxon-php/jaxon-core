<?php

namespace Jaxon\NsTests\DirA;

use function Jaxon\jaxon;

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
