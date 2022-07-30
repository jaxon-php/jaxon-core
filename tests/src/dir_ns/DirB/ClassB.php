<?php

namespace Jaxon\NsTests\DirB;

use Jaxon\Response\ResponseInterface;
use Jaxon\Tests\Ns\Lib\ServiceInterface;
use function Jaxon\jaxon;

class ClassB
{
    /**
     * @di('attr' => 'service', 'class' => 'ServiceInterface')
     * @return ResponseInterface
     */
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
