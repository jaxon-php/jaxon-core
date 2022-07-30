<?php

namespace Jaxon\Tests\TestRegistrationApp;

require_once __DIR__ . '/../src/classes.php';

use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Request\CallableClass\CallableClassPlugin;
use Jaxon\Plugin\Request\CallableClass\CallableObject;
use PHPUnit\Framework\TestCase;
use TheClass;
use function file_get_contents;
use function Jaxon\jaxon;
use function strlen;

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
        jaxon()->app()->setup(__DIR__ . '/../config/app/classes.php');

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

    /**
     * @throws SetupException
     */
    public function testCallableDirJsCode()
    {
        $this->assertEquals(32, strlen($this->xPlugin->getHash()));
        // $this->assertEquals('927202fb3aaa987a88d943939c3efe36', $this->xPlugin->getHash());
        $this->assertEquals(strlen(file_get_contents(__DIR__ . '/../src/js/class.js')),
            strlen($this->xPlugin->getScript()));
    }

    public function testClassNotFound()
    {
        // No callable for standard PHP functions.
        $this->expectException(SetupException::class);
        $this->xPlugin->getCallable('Simple');
    }
}
