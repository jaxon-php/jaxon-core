<?php

namespace Jaxon\Tests\Registration;

use Jaxon\Jaxon;
use Jaxon\Request\Plugin\CallableClass\CallableClassPlugin;
use Jaxon\Request\Plugin\CallableClass\CallableDirPlugin;
use Jaxon\Request\Plugin\CallableClass\CallableObject;
use Jaxon\Exception\SetupException;
use PHPUnit\Framework\TestCase;

use function strlen;
use function file_get_contents;
use function jaxon;

class RegistrationTest extends TestCase
{
    /**
     * @var CallableClassPlugin
     */
    protected $xPlugin;


    public function setUp(): void
    {
        jaxon()->setOption('core.prefix.class', 'Jxn');

        jaxon()->register(Jaxon::CALLABLE_CLASS, 'Sample', [
            'include' => __DIR__ . '/../defs/sample.php',
            'functions' => [
                '*' => [
                    'asynchronous' => 'true',
                ],
            ],
        ]);

        jaxon()->register(Jaxon::CALLABLE_DIR, __DIR__ . '/../dir', [
            'autoload' => true,
            'classes' => [
                'ClassA' => [
                    'protected' => ['methodAa'],
                    'methodAb' => [
                        '__before' => 'methodAa',
                        '__after' => ['methodBb' => 'after'],
                    ],
                ],
                'ClassB' => [
                    'protected' => 'methodBb',
                    'methodBa' => [
                        '__before' => ['methodBb' => ['before', 'one']],
                        '__after' => ['methodBb'],
                    ],
                ],
                'ClassC' => [
                    'methodCa' => [
                        'upload' => "'methodBb'",
                    ],
                ],
            ],
        ]);

        $this->xPlugin = jaxon()->di()->getCallableClassPlugin();
    }

    public function tearDown(): void
    {
        jaxon()->reset();
        parent::tearDown();
    }

    public function callableClassOptions()
    {
        $xCallable = $this->xPlugin->getCallable('Sample');
        $this->assertEquals('', json_encode($xCallable->getOptions()));
    }

    public function testClassSampleOptions()
    {
        $aOptions = $this->xPlugin->getCallable('Sample')->getOptions();
        $this->assertIsArray($aOptions);
        $this->assertCount(1, $aOptions);
        $this->assertEquals('true', $aOptions['*']['asynchronous']);
    }

    public function testDirClassAOptions()
    {
        $aOptions = $this->xPlugin->getCallable('ClassA')->getOptions();
        $this->assertIsArray($aOptions);
        $this->assertCount(0, $aOptions);
    }

    public function testDirClassBOptions()
    {
        $aOptions = $this->xPlugin->getCallable('ClassB')->getOptions();
        $this->assertIsArray($aOptions);
        $this->assertCount(0, $aOptions);
    }

    public function testDirClassCOptions()
    {
        $aOptions = $this->xPlugin->getCallable('ClassC')->getOptions();
        $this->assertIsArray($aOptions);
        $this->assertCount(1, $aOptions);
        $this->assertEquals("'methodBb'", $aOptions['methodCa']['upload']);
    }

    public function testCallableDirJsCode()
    {
        $this->assertEquals(32, strlen($this->xPlugin->getHash()));
        // $this->assertEquals('adc33e67ac8195160f7648ea4289aae6', $this->xPlugin->getHash());
        $this->assertEquals(769, strlen($this->xPlugin->getScript()));
        // $this->assertEquals(file_get_contents(__DIR__ . '/../script/options.js'), $this->xPlugin->getScript());
    }
}
