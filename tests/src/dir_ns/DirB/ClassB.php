<?php

namespace Jaxon\NsTests\DirB;

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
