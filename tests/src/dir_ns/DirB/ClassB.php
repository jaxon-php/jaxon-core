<?php

namespace Jaxon\NsTests\DirB;

use Jaxon\Response\Response;
use Jaxon\Tests\Ns\Lib\ServiceInterface;
use function Jaxon\jaxon;

class ClassB
{
    /**
     * @di('attr' => 'service', 'class' => 'ServiceInterface')
     * @return Response
     */
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
