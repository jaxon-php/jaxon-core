<?php

namespace Jaxon\NsTests\DirC;

use Jaxon\App\CallableClass;
use Jaxon\Exception\SetupException;
use Jaxon\NsTests\DirB\ClassB;
use Jaxon\Response\Response;
use function Jaxon\jaxon;

class ClassC extends CallableClass
{
    /**
     * @throws SetupException
     */
    public function methodCa(): Response
    {
        $xResponse = jaxon()->getResponse();
        $xResponse->html('div', 'This is the div content!!');
        $this->cl(ClassB::class)->methodBb();
        return $xResponse;
    }

    public function methodCb(): Response
    {
        $xResponse = jaxon()->getResponse();
        $xResponse->html('div', 'This is the div content!!');
        return $xResponse;
    }
}
