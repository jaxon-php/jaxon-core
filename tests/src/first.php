<?php

use Jaxon\Response\Response;

function my_first_function(): Response
{
    $xResponse = jaxon()->getResponse();
    $xResponse->alert('This is a response!!');
    return $xResponse;
}
