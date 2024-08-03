<?php

namespace Jaxon\Tests\TestRegistration;

use Jaxon\Exception\SetupException;
use Jaxon\Jaxon;
use Jaxon\Plugin\Request\CallableClass\CallableClassPlugin;
use PHPUnit\Framework\TestCase;
use function Jaxon\jaxon;
use function strlen;

class RegistrationTest extends TestCase
{
    /**
     * @var CallableClassPlugin
     */
    protected $xPlugin;

    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.prefix.class', 'Jxn');

        jaxon()->register(Jaxon::CALLABLE_CLASS, 'Sample', [
            'include' => __DIR__ . '/../src/sample.php',
            'functions' => [
                '*' => [
                    'asynchronous' => 'true',
                ],
            ],
        ]);

        jaxon()->register(Jaxon::CALLABLE_DIR, __DIR__ . '/../src/dir', [
            'autoload' => true,
            'classes' => [
                'ClassA' => [
                    'protected' => ['methodAa'],
                    'functions' => [
                        '*' => [
                            'bags' => 'bag.name',
                        ],
                        'methodAb' => [
                            '__before' => 'methodAa',
                            '__after' => ['methodBb' => 'after'],
                        ],
                    ],
                ],
                'ClassB' => [
                    'protected' => 'methodBb',
                    'functions' => [
                        'methodBa' => [
                            '__before' => ['methodBb' => ['before', 'one']],
                            '__after' => ['methodBb'],
                            '__di' => ['attrName' => 'attrClass'],
                            'bags' => true, // will be silently ignored.
                        ],
                    ],
                ],
                'ClassC' => [
                    'protected' => 'methodCc',
                    'functions' => [
                        'methodCa' => [
                            'upload' => "'methodBb'",
                            'bags' => 5, // will be silently ignored.
                        ],
                    ],
                ],
                'ClassD' => [
                    'excluded' => true,
                ],
            ],
        ]);

        $this->xPlugin = jaxon()->di()->getCallableClassPlugin();
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
    public function callableClassOptions()
    {
        $xCallable = $this->xPlugin->getCallable('Sample');
        $this->assertEquals('', json_encode($xCallable->getOptions()));
    }

    /**
     * @throws SetupException
     */
    public function testClassSampleOptions()
    {
        $aOptions = $this->xPlugin->getCallable('Sample')->getOptions();
        $this->assertIsArray($aOptions);
        $this->assertCount(1, $aOptions);
        $this->assertEquals('true', $aOptions['*']['asynchronous']);
    }

    /**
     * @throws SetupException
     */
    public function testDirClassAOptions()
    {
        $aOptions = $this->xPlugin->getCallable('ClassA')->getOptions();
        $this->assertIsArray($aOptions);
        $this->assertCount(1, $aOptions);
    }

    /**
     * @throws SetupException
     */
    public function testDirClassBOptions()
    {
        $aOptions = $this->xPlugin->getCallable('ClassB')->getOptions();
        $this->assertIsArray($aOptions);
        $this->assertCount(0, $aOptions);
    }

    /**
     * @throws SetupException
     */
    public function testDirClassCOptions()
    {
        $aOptions = $this->xPlugin->getCallable('ClassC')->getOptions();
        $this->assertIsArray($aOptions);
        $this->assertCount(1, $aOptions);
        $this->assertEquals("'methodBb'", $aOptions['methodCa']['upload']);
    }

    /**
     * @throws SetupException
     */
    public function testCallableDirJsCode()
    {
        $this->assertEquals(32, strlen($this->xPlugin->getHash()));
        // $this->assertEquals('56222468ad00b31763366f1185ec564d', $this->xPlugin->getHash());
        $this->assertEquals(789, strlen($this->xPlugin->getScript()));
        // $this->assertEquals(file_get_contents(__DIR__ . '/../src/js/options.js'), $this->xPlugin->getScript());
    }

    /**
     * @throws SetupException
     */
    public function testJsCodeHash()
    {
        jaxon()->setOption('core.prefix.function', 'jxn_');
        $sHash1 = jaxon()->di()->getCodeGenerator()->getHash();

        // Register a function
        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'my_first_function',
            __DIR__ . '/../src/first.php');
        // Register a function with an alias
        $sHash2 = jaxon()->di()->getCodeGenerator()->getHash();

        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'my_second_function', [
            'alias' => 'my_alias_function',
            'upload' => "'html_field_id'",
        ]);
        $sHash3 = jaxon()->di()->getCodeGenerator()->getHash();
        $this->assertNotEquals($sHash1, $sHash2);
        $this->assertNotEquals($sHash1, $sHash3);
        $this->assertNotEquals($sHash3, $sHash1);
    }

    public function testInvalidPluginId()
    {
        require_once __DIR__ . '/../src/sample.php';
        // Register a class with an incorrect plugin id.
        $this->expectException(SetupException::class);
        jaxon()->register('PluginNotFound', 'Sample');
    }

    /**
     * @throws SetupException
     */
    public function testUnknownCallableClass()
    {
        // Register a class that does not exist.
        jaxon()->register(Jaxon::CALLABLE_CLASS, 'UnknownClass');
        $this->expectException(SetupException::class);
        $this->xPlugin->getCallable('UnknownClass');
    }
}
