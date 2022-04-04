<?php

use Jaxon\Plugin\ResponsePlugin;

class SamplePlugin extends ResponsePlugin
{
    public function getName(): string
    {
        return 'plugin';
    }
}
