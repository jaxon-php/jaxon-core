<?php

namespace Jaxon\NsTests\DirC;

use Jaxon\App\FuncComponent;
use Jaxon\Exception\SetupException;
use Jaxon\NsTests\DirB\ClassB;

class ClassC extends FuncComponent
{
    /**
     * @throws SetupException
     */
    public function methodCa()
    {
        $xResponse = jaxon()->getResponse();
        $xResponse->html('div', 'This is the div content!!');
        $this->cl(ClassB::class)->methodBb();
    }

    public function methodCb()
    {
        $xResponse = jaxon()->getResponse();
        $xResponse->html('div', 'This is the div content!!');
    }
}
