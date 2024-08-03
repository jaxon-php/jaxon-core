<?php

use Jaxon\Plugin\AbstractResponsePlugin;

class SamplePlugin extends AbstractResponsePlugin
{
    public function getName(): string
    {
        return 'plugin';
    }
}
