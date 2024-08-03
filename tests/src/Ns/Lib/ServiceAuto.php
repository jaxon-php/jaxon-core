<?php

namespace Jaxon\Tests\Ns\Lib;

class ServiceAuto
{
    /**
     * @var Service $service
     */
    private $service;

    public function __construct(Service $s)
    {
        $this->service = $s;
    }

    public function serviceSource(): string
    {
        return $this->service->getSource();
    }
}
