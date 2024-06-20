<?php

namespace Jaxon\Tests\TestRegistration;

require_once __DIR__ . '/../src/packages.php';

use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Package;
use Jaxon\Utils\Http\UriException;
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
        jaxon()->setOption('core.prefix.class', '');
        jaxon()->setOption('core.request.uri', 'http://example.test/path');
        jaxon()->registerPackage(SamplePackage::class,
            ['option1' => 'value1', 'option2' => ['option3' => 'value3']]);
    }

    /**
     * @throws SetupException
     */
    public function tearDown(): void
    {
        jaxon()->reset();
        parent::tearDown();
    }

    /**
     * @throws UriException
     */
    public function testPackage()
    {
        $this->assertNotNull(jaxon()->package(SamplePackage::class));
        $this->assertEquals(SamplePackage::class, get_class(jaxon()->package(SamplePackage::class)));
        $xSamplePackage = jaxon()->package(SamplePackage::class);
        $xSamplePackage->ready();
        $sScript = jaxon()->getScript();
        $this->assertStringContainsString('SamplePackageClass = {}', $sScript);
    }

    public function testPackageOptions()
    {
        /** @var Package */
        $xPackage = jaxon()->di()->g(SamplePackage::class);
        $xPackageConfig = $xPackage->getConfig();
        $this->assertEquals('value1', $xPackageConfig->getOption('option1'));
        $this->assertEquals('value3', $xPackageConfig->getOption('option2.option3'));

        $this->assertEquals('value1', $xPackage->getOption('option1'));
        $this->assertEquals('value3', $xPackage->getOption('option2.option3'));
    }

    public function testRegisterInvalidPackage()
    {
        require_once __DIR__ . '/../src/sample.php';
        // Register a class which is not a package as a package.
        $this->expectException(SetupException::class);
        jaxon()->registerPackage('Sample');
    }

    public function testRegisterPackageWithIncorrectConfig()
    {
        // Register a package with incorrect config data type.
        $this->expectException(SetupException::class);
        jaxon()->registerPackage('BadConfigPackage');
    }

    public function testGetInvalidPackage()
    {
        $this->assertNull(jaxon()->package('Sample'));
    }

    public function testRegisterUnknownPackage()
    {
        // Register a class which doesn't exist.
        $this->expectException(SetupException::class);
        jaxon()->registerPackage('UnknownPackage');
    }

    public function testGetUnknownPackage()
    {
        $this->assertNull(jaxon()->package('UnknownPackage'));
    }
}
