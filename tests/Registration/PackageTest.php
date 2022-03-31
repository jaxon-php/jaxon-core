<?php

namespace Jaxon\Tests\Registration;

use Jaxon\Jaxon;
use Jaxon\Exception\SetupException;
use Lagdo\TwitterFeed\Package as TwitterPackage;
use PHPUnit\Framework\TestCase;

class PackageTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.prefix.class', '');
        jaxon()->registerPackage(TwitterPackage::class);
    }

    /**
     * @throws SetupException
     */
    public function tearDown(): void
    {
        jaxon()->reset();
        parent::tearDown();
    }

    public function testPackage()
    {
        $this->assertNotNull(jaxon()->package(TwitterPackage::class));
        $this->assertEquals(TwitterPackage::class, get_class(jaxon()->package(TwitterPackage::class)));
    }

    public function testRegisterInvalidPackage()
    {
        require_once __DIR__ . '/../defs/sample.php';
        // Register a class which is not a plugin as a plugin.
        $this->expectException(SetupException::class);
        jaxon()->registerPackage('Sample');
    }

    public function testRegisterPackageWithBadConfig()
    {
        require_once __DIR__ . '/../defs/packages.php';
        // Register a class which is not a plugin as a plugin.
        $this->expectException(SetupException::class);
        jaxon()->registerPackage('BadConfigPackage');
    }

    public function testGetInvalidPackage()
    {
        $this->assertNull(jaxon()->package('Sample'));
    }
}
