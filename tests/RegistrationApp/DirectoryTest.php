<?php

namespace Jaxon\Tests\RegistrationApp;

use Jaxon\Request\Plugin\CallableClass\CallableClassPlugin;
use Jaxon\Request\Plugin\CallableClass\CallableDirPlugin;
use Jaxon\Request\Plugin\CallableClass\CallableObject;
use Jaxon\Exception\SetupException;
use PHPUnit\Framework\TestCase;

use Jaxon\Tests\Ns\Ajax\ClassA;
use Jaxon\Tests\Ns\Ajax\ClassB;
use Jaxon\Tests\Ns\Ajax\ClassC;

use function jaxon;

class DirectoryTest extends TestCase
{
    /**
     * @var CallableDirPlugin
     */
    protected $xDirPlugin;

    /**
     * @var CallableClassPlugin
     */
    protected $xClassPlugin;

    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->app()->setup(__DIR__ . '/../config/app/directories.php');

        $this->xDirPlugin = jaxon()->di()->getCallableDirPlugin();
        $this->xClassPlugin = jaxon()->di()->getCallableClassPlugin();
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
    public function testCallableDirClasses()
    {
        $xClassACallable = $this->xClassPlugin->getCallable('ClassA');
        $xClassBCallable = $this->xClassPlugin->getCallable('ClassB');
        $xClassCCallable = $this->xClassPlugin->getCallable('ClassC');
        // Test callables classes
        $this->assertEquals(CallableObject::class, get_class($xClassACallable));
        $this->assertEquals(CallableObject::class, get_class($xClassBCallable));
        $this->assertEquals(CallableObject::class, get_class($xClassCCallable));
        // Check methods
        $this->assertTrue($xClassACallable->hasMethod('methodAa'));
        $this->assertTrue($xClassACallable->hasMethod('methodAb'));
        $this->assertFalse($xClassACallable->hasMethod('methodAc'));
    }

    /**
     * @throws SetupException
     */
    public function testCallableNsClasses()
    {
        $xClassACallable = $this->xClassPlugin->getCallable(ClassA::class);
        $xClassBCallable = $this->xClassPlugin->getCallable(ClassB::class);
        $xClassCCallable = $this->xClassPlugin->getCallable(ClassC::class);
        // Test callables classes
        $this->assertEquals(CallableObject::class, get_class($xClassACallable));
        $this->assertEquals(CallableObject::class, get_class($xClassBCallable));
        $this->assertEquals(CallableObject::class, get_class($xClassCCallable));
        // Check methods
        $this->assertTrue($xClassACallable->hasMethod('methodAa'));
        $this->assertTrue($xClassACallable->hasMethod('methodAb'));
        $this->assertFalse($xClassACallable->hasMethod('methodAc'));
    }
}
