<?php

use function Jaxon\jaxon;

function my_second_function()
{
    // Using the global response. No need to return.
    $xResponse = jaxon()->getResponse();
    $xResponse->alert('This is a response!!');
}

function my_third_function()
{

}
