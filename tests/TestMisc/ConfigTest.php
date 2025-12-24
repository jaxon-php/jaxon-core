<?php

namespace Jaxon\Tests\TestMisc;

use Jaxon\Exception\SetupException;
use PHPUnit\Framework\TestCase;

use function dirname;

final class ConfigTest extends TestCase
{
    /**
     * @var array
     */
    protected $aMaxDataDepthOptions = [
        'core' => [
            'one' => [
                'two' => [
                    'three' => [
                        'four' => [
                            'five' => [
                                'six' => [
                                    'seven' => [
                                        'eight' => [
                                            'nine' => [
                                                'ten' => [
                                                    'param' => 'Value',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    /**
     * @throws SetupException
     */
    protected function setUp(): void
    {
        jaxon()->setOptions(['core' => ['language' => 'en']]);
        jaxon()->setOption('core.prefix.function', 'jaxon_');
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
     * @throws SetupException
     */
    public function testPhpConfigReader()
    {
        jaxon()->config()->load(dirname(__DIR__) . '/config/config.php', 'jaxon');
        $this->assertEquals('en', jaxon()->getOption('core.language'));
        $this->assertEquals('jaxon_', jaxon()->getOption('core.prefix.function'));
        $this->assertFalse(jaxon()->getOption('core.debug.on'));
        $this->assertFalse(jaxon()->hasOption('core.debug.off'));
    }

    /**
     * @throws SetupException
     */
    public function testJsonConfigReader()
    {
        jaxon()->config()->load(dirname(__DIR__) . '/config/config.json', 'jaxon');
        $this->assertEquals('en', jaxon()->getOption('core.language'));
        $this->assertEquals('jaxon_', jaxon()->getOption('core.prefix.function'));
        $this->assertFalse(jaxon()->getOption('core.debug.on'));
        $this->assertFalse(jaxon()->hasOption('core.debug.off'));
    }

    /**
     * @throws SetupException
     */
    public function testReadOptionNames()
    {
        jaxon()->config()->load(dirname(__DIR__) . '/config/config.json');
        $aOptionNames = jaxon()->config()->getOptionNames('jaxon.core');
        $this->assertIsArray($aOptionNames);
        $this->assertCount(3, $aOptionNames);
    }

    /**
     * @throws SetupException
     */
    public function testSimpleArrayValues()
    {
        jaxon()->config()->load(dirname(__DIR__) . '/config/array.php');
        $aOption = jaxon()->getOption('core.array');
        $this->assertIsArray($aOption);
        $this->assertCount(4, $aOption);
        $this->assertEmpty(jaxon()->config()->getOptionNames('core.array'));
    }

    /**
     * @throws SetupException
     */
    public function testSetOptionsDataDepth()
    {
        $this->expectException(SetupException::class);
        jaxon()->setOptions($this->aMaxDataDepthOptions);
    }

    /**
     * @throws SetupException
     */
    public function testNewConfigDataDepth()
    {
        $this->expectException(SetupException::class);
        jaxon()->config()->newConfig($this->aMaxDataDepthOptions);
    }

    /**
     * @throws SetupException
     */
    public function testLoadConfigDataDepth()
    {
        $this->expectException(SetupException::class);
        jaxon()->config()->load(dirname(__DIR__) . '/config/depth.php');
    }

    /**
     * @throws SetupException
     */
    public function testEmptyFileName()
    {
        $this->assertEmpty(jaxon()->config()->read(''));
    }

    /**
     * @throws SetupException
     */
    public function testMissingPhpFile()
    {
        $this->expectException(SetupException::class);
        jaxon()->config()->load(dirname(__DIR__) . '/config/missing.php');
    }

    /**
     * @throws SetupException
     */
    public function testMissingJsonFile()
    {
        $this->expectException(SetupException::class);
        jaxon()->config()->load(dirname(__DIR__) . '/config/missing.json');
    }

    /**
     * @throws SetupException
     */
    public function testMissingYamlFile()
    {
        $this->expectException(SetupException::class);
        jaxon()->config()->load(dirname(__DIR__) . '/config/missing.yml');
    }

    /**
     * @throws SetupException
     */
    public function testErrorInPhpFile()
    {
        $this->expectException(SetupException::class);
        jaxon()->config()->load(dirname(__DIR__) . '/config/error.php');
    }

    /**
     * @throws SetupException
     */
    public function testErrorInJsonFile()
    {
        $this->expectException(SetupException::class);
        jaxon()->config()->load(dirname(__DIR__) . '/config/error.json');
    }

    /**
     * @throws SetupException
     */
    public function testErrorInYamlFile()
    {
        $this->expectException(SetupException::class);
        jaxon()->config()->load(dirname(__DIR__) . '/config/error.yml');
    }

    /**
     * @throws SetupException
     */
    public function testUnsupportedFileExtension()
    {
        $this->expectException(SetupException::class);
        jaxon()->config()->load(dirname(__DIR__) . '/config/config.ini');
    }
}
