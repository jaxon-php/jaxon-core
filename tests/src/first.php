<?php

use Jaxon\Response\ResponseInterface;
use function Jaxon\jaxon;

function my_first_function(): ResponseInterface
{
    $xResponse = jaxon()->getResponse();
    $xResponse->alert('This is a response!!');
    return $xResponse;
}
