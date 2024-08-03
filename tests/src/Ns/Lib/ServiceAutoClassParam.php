<?php

namespace Jaxon\Tests\Ns\Lib;

class ServiceAutoClassParam
{
    /**
     * @var Service $service
     */
    private $service;

    public function __construct(Service $serv)
    {
        $this->service = $serv;
    }

    public function serviceSource(): string
    {
        return $this->service->getSource();
    }
}
