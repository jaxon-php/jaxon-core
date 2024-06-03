<?php

use Jaxon\Response\Response;
use function Jaxon\jaxon;

function my_first_function(): Response
{
    $xResponse = jaxon()->getResponse();
    $xResponse->alert('This is a response!!');
    return $xResponse;
}
