<?php

namespace Jaxon\Tests\TestRegistrationApp;

require_once __DIR__ . '/../src/packages.php';

use Jaxon\Exception\SetupException;
use PHPUnit\Framework\TestCase;
use SamplePackage;

use function Jaxon\jaxon;

class PackageTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->app()->setup(__DIR__ . '/../config/app/package.php');
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
        $this->assertTrue(jaxon()->di()->h(SamplePackage::class));
    }
}
