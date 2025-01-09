<?php

namespace Jaxon\NsTests\DirB;

use Jaxon\Tests\Ns\Lib\ServiceInterface;
use function Jaxon\jaxon;

class ClassB
{
    /**
     * @di('attr' => 'service', 'class' => 'ServiceInterface')
     */
    public function methodBa()
    {
        $xResponse = jaxon()->getResponse();
        $xResponse->html('div', 'This is the div content!!');
    }

    public function methodBb()
    {
        $xResponse = jaxon()->getResponse();
        $xResponse->html('div', 'This is the div content!!');
    }
}
