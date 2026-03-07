<?php

namespace Jaxon\Tests\TestRegistration;

require_once dirname(__DIR__) . '/src/packages.php';

use Jaxon\Exception\SetupException;
use Jaxon\Plugin\AbstractPackage;
use Jaxon\Utils\Http\UriException;
use PHPUnit\Framework\TestCase;
use EmptyPackage;
use SamplePackage;

use function get_class;

class PackageTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.prefix.class', '');
        jaxon()->setOption('core.request.uri', 'http://example.test/path');
        jaxon()->registerPackage(EmptyPackage::class);
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
    public function testEmptyPackage()
    {
        $xEmptyPackage = jaxon()->package(EmptyPackage::class);
        $this->assertNotNull($xEmptyPackage);
        $this->assertNotNull($xEmptyPackage->view());
        $this->assertEquals(EmptyPackage::class, get_class($xEmptyPackage));
        $this->assertEquals('', $xEmptyPackage->html());
        $this->assertEquals('', $xEmptyPackage->getHtml());
        $this->assertEquals('', $xEmptyPackage->getInlineScript());
        $this->assertEquals('', $xEmptyPackage->getReadyScript());
    }

    public function testPackageOptions()
    {
        /** @var AbstractPackage */
        $xPackage = jaxon()->di()->g(SamplePackage::class);
        $xPackageConfig = $xPackage->getConfig();
        $this->assertEquals('value1', $xPackageConfig->getOption('option1'));
        $this->assertEquals('value3', $xPackageConfig->getOption('option2.option3'));

        $this->assertEquals('value1', $xPackage->getOption('option1'));
        $this->assertEquals('value3', $xPackage->getOption('option2.option3'));
    }

    public function testRegisterInvalidPackage()
    {
        require_once dirname(__DIR__) . '/src/sample.php';
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
