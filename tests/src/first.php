<?php

function my_first_function()
{
    $xResponse = jaxon()->getResponse();
    $xResponse->alert('This is a response!!');
}
