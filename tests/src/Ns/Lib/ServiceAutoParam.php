<?php

namespace Jaxon\Tests\Ns\Lib;

class ServiceAutoParam
{
    /**
     * @var Service $service
     */
    private $service;

    public function __construct($service)
    {
        $this->service = $service;
    }

    public function serviceSource(): string
    {
        return $this->service->getSource();
    }
}
