<?php

namespace Jaxon\Tests\TestMisc;

use Jaxon\Exception\SetupException;
use Jaxon\Tests\Ns\Lib\Service;
use Jaxon\Tests\Ns\Lib\ServiceAuto;
use Jaxon\Tests\Ns\Lib\ServiceAutoClassParam;
use Jaxon\Tests\Ns\Lib\ServiceAutoParam;
use Jaxon\Tests\Ns\Lib\ServiceExt;
use Jaxon\Tests\Ns\Lib\ServiceInterface;
use Pimple\Container as AppContainer;
use Pimple\Psr11\Container as PsrContainer;
use PHPUnit\Framework\TestCase;
use ReflectionException;

use function get_class;
use function Jaxon\jaxon;

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

    public function testHasValues()
    {
        $this->assertTrue(jaxon()->di()->h('service_config'));
        $this->assertTrue(jaxon()->di()->h(Service::class));
        $this->assertTrue(jaxon()->di()->h(ServiceInterface::class));
        $this->assertTrue(jaxon()->di()->h(ServiceAuto::class));
    }

    public function testValueTypes()
    {
        $this->assertIsArray(jaxon()->di()->g('service_config'));
        $this->assertEquals(Service::class, get_class(jaxon()->di()->g(Service::class)));
        $this->assertEquals(Service::class, get_class(jaxon()->di()->g(ServiceInterface::class)));
        $this->assertEquals(ServiceAuto::class, get_class(jaxon()->di()->g(ServiceAuto::class)));
    }

    public function testContainerServiceSources()
    {
        $service = jaxon()->di()->g(ServiceAuto::class);
        $this->assertEquals(ServiceAuto::class, get_class($service));
        $this->assertEquals('Class only', $service->serviceSource());

        $service = jaxon()->di()->g(ServiceAutoClassParam::class);
        $this->assertEquals(ServiceAutoClassParam::class, get_class($service));
        $this->assertEquals('Class + parameter', $service->serviceSource());

        $service = jaxon()->di()->g(ServiceAutoParam::class);
        $this->assertEquals(ServiceAutoParam::class, get_class($service));
        $this->assertEquals('Parameter only', $service->serviceSource());
    }

    /**
     * @throws SetupException
     */
    public function testAppContainer()
    {
        $xContainer = new AppContainer();
        $xAppContainer = new PsrContainer($xContainer);
        // Register a class in the other container
        $xContainer[ServiceExt::class] = function() {
            return new ServiceExt();
        };
        jaxon()->di()->setContainer($xAppContainer);
        // Access the class from the Jaxon container
        $this->assertFalse(jaxon()->di()->h(ServiceExt::class));
        $this->assertTrue(jaxon()->di()->has(ServiceExt::class));
        $this->assertEquals(ServiceExt::class, get_class(jaxon()->di()->get(ServiceExt::class)));
    }

    /**
     * @throws ReflectionException
     * @throws SetupException
     */
    public function testMakeWithIncorrectParam()
    {
        $this->assertEquals(null, jaxon()->di()->make(true));
        $this->assertEquals(null, jaxon()->di()->make(true));
        $this->expectException(ReflectionException::class);
        jaxon()->di()->make('service_config');
    }
}
