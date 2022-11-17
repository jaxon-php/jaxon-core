<?php

namespace Jaxon\Tests\TestRegistration;

require_once __DIR__ . '/../src/packages.php';

use Jaxon\Exception\SetupException;
use Jaxon\Utils\Http\UriException;
use Lagdo\TwitterFeed\Package as TwitterPackage;
use PHPUnit\Framework\TestCase;
use SamplePackage;
use function Jaxon\jaxon;
use function Jaxon\pm;

class PackageTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.prefix.class', '');
        jaxon()->setOption('core.request.uri', 'http://example.test/path');
        jaxon()->registerPackage(TwitterPackage::class);
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
        $this->assertNotNull(jaxon()->package(TwitterPackage::class));
        $this->assertEquals(TwitterPackage::class, get_class(jaxon()->package(TwitterPackage::class)));
        $this->assertNotNull(jaxon()->package(SamplePackage::class));
        $this->assertEquals(SamplePackage::class, get_class(jaxon()->package(SamplePackage::class)));
        $xSamplePackage = jaxon()->package(SamplePackage::class);
        $xSamplePackage->ready();
        $sScript = jaxon()->getScript();
        $this->assertStringContainsString('SamplePackageClass = {}', $sScript);
    }

    public function testPackageOptions()
    {
        $xPackageConfig = jaxon()->di()->getPackageConfig(SamplePackage::class);
        $this->assertEquals('value1', $xPackageConfig->getOption('option1'));
        $this->assertEquals('value3', $xPackageConfig->getOption('option2.option3'));

        $xPackage = jaxon()->di()->g(SamplePackage::class);
        $this->assertEquals('value1', $xPackage->getOption('option1'));
        $this->assertEquals('value3', $xPackage->getOption('option2.option3'));
    }

    /**
     * @throws UriException
     */
    public function testTwitterPackageNotReady()
    {
        // Without the ready(), the Lagdo.TwitterFeed.Ajax.Client object must be defined,
        // and the jaxon.twitterFeed.initFetch() function must not be called.
        $sScript = jaxon()->getScript();
        $this->assertStringContainsString('Lagdo.TwitterFeed.Ajax.Client = {}', $sScript);
        $this->assertStringNotContainsString('jaxon.twitterFeed.initFetch()', $sScript);
    }

    /**
     * @throws UriException
     */
    public function testTwitterPackageReady()
    {
        // With the ready(), the Lagdo.TwitterFeed.Ajax.Client object must be defined,
        // and the jaxon.twitterFeed.initFetch() function must be called.
        $xTwitterPackage = jaxon()->package(TwitterPackage::class);
        $xTwitterPackage->ready();
        $sScript = jaxon()->getScript();
        $this->assertStringContainsString('Lagdo.TwitterFeed.Ajax.Client = {}', $sScript);
        $this->assertStringContainsString('jaxon.twitterFeed.initFetch()', $sScript);
        $this->assertStringContainsString('twitter_feed', $xTwitterPackage->html());
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
