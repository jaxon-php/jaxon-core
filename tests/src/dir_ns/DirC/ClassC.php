<?php

namespace Jaxon\NsTests\DirC;

use Jaxon\App\CallableClass;
use Jaxon\Exception\SetupException;
use Jaxon\NsTests\DirB\ClassB;
use function Jaxon\jaxon;

class ClassC extends CallableClass
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
