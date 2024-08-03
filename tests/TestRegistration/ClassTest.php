<?php

namespace Jaxon\Tests\TestRegistration;

use Jaxon\Exception\SetupException;
use Jaxon\Jaxon;
use Jaxon\Plugin\Request\CallableClass\CallableClassPlugin;
use Jaxon\Plugin\Request\CallableClass\CallableObject;
use PHPUnit\Framework\TestCase;
use TheClass;
use function file_get_contents;
use function Jaxon\jaxon;
use function strlen;

require_once __DIR__ . '/../src/classes.php';

class ClassTest extends TestCase
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

        jaxon()->register(Jaxon::CALLABLE_CLASS, 'Sample', __DIR__ . '/../src/sample.php');
        jaxon()->register(Jaxon::CALLABLE_CLASS, TheClass::class);

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

    public function testPluginName()
    {
        $this->assertEquals(Jaxon::CALLABLE_CLASS, $this->xPlugin->getName());
    }

    public function testCallableClassClass()
    {
        $xSampleCallable = $this->xPlugin->getCallable('Sample');
        $xClassCallable = $this->xPlugin->getCallable(TheClass::class);
        // Test callables classes
        $this->assertEquals(CallableObject::class, get_class($xSampleCallable));
        $this->assertEquals(CallableObject::class, get_class($xClassCallable));
        // Check methods
        $this->assertTrue($xSampleCallable->hasMethod('myMethod'));
        $this->assertFalse($xSampleCallable->hasMethod('yourMethod'));
    }

    public function testCallableDirJsCode()
    {
        $this->assertEquals(32, strlen($this->xPlugin->getHash()));
        // $this->assertEquals('927202fb3aaa987a88d943939c3efe36', $this->xPlugin->getHash());
        $this->assertEquals(strlen(file_get_contents(__DIR__ . '/../src/js/class.js')),
            strlen($this->xPlugin->getScript()));
    }

    public function testClassNotFound()
    {
        // No callable for classes that does not exist.
        $this->expectException(SetupException::class);
        $this->xPlugin->getCallable('Simple');
    }

    /**
     * @throws SetupException
     */
    public function testCallableClassUnknownOption()
    {
        // Register a class method as a function, with unknown option
        jaxon()->register(Jaxon::CALLABLE_CLASS, 'TheClass', [
            'include' => __DIR__ . '/../src/classes.php',
            'protected' => 'protectedMethod',
            'functions' => [
                '*' => [
                    '__unknown' => 'unknown',
                ],
            ],
        ]);

        $xCallable = $this->xPlugin->getCallable('TheClass');
        $this->assertTrue($xCallable->hasMethod('theMethod'));
    }

    public function testCallableDirIncorrectOption()
    {
        // Register a function with incorrect option
        $this->expectException(SetupException::class);
        jaxon()->register(Jaxon::CALLABLE_CLASS, 'Sample', true);
    }

    public function testCallableDirIncorrectPath()
    {
        // Register a class with incorrect name
        $this->expectException(SetupException::class);
        jaxon()->register(Jaxon::CALLABLE_CLASS, 'Sam:ple');
    }
}
