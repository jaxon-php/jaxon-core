<?php

namespace Jaxon\Tests\TestRegistrationApp;

use Jaxon\Exception\SetupException;
use Jaxon\Tests\Ns\Lib\Service;
use Jaxon\Tests\Ns\Lib\ServiceAuto;
use Jaxon\Tests\Ns\Lib\ServiceInterface;
use PHPUnit\Framework\TestCase;
use function jaxon;

class ContainerTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->app()->setup(__DIR__ . '/../config/app/container.php');
    }

    /**
     * @throws SetupException
     */
    public function tearDown(): void
    {
        jaxon()->reset();
        parent::tearDown();
    }

    public function testContainer()
    {
        $this->assertTrue(jaxon()->di()->h('service_config'));
        $this->assertTrue(jaxon()->di()->h(Service::class));
        $this->assertTrue(jaxon()->di()->h(ServiceInterface::class));
        $this->assertTrue(jaxon()->di()->h(ServiceAuto::class));
    }
}
