<?php

namespace Jaxon\Tests\TestRegistrationApp;

use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Request\CallableClass\CallableClassPlugin;
use Jaxon\Plugin\Request\CallableClass\CallableDirPlugin;
use Jaxon\Plugin\Request\CallableClass\CallableObjectProxy;
use Jaxon\Tests\Ns\Ajax\ClassA;
use Jaxon\Tests\Ns\Ajax\ClassB;
use Jaxon\Tests\Ns\Ajax\ClassC;
use PHPUnit\Framework\TestCase;


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
        jaxon()->app()->setup(dirname(__DIR__) . '/config/app/directories.php');

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
        $xClassACallable = $this->xClassPlugin->getCallableProxy('ClassA');
        $xClassBCallable = $this->xClassPlugin->getCallableProxy('ClassB');
        $xClassCCallable = $this->xClassPlugin->getCallableProxy('ClassC');
        // Test callables classes
        $this->assertEquals(CallableObjectProxy::class, get_class($xClassACallable));
        $this->assertEquals(CallableObjectProxy::class, get_class($xClassBCallable));
        $this->assertEquals(CallableObjectProxy::class, get_class($xClassCCallable));
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
        $xClassACallable = $this->xClassPlugin->getCallableProxy(ClassA::class);
        $xClassBCallable = $this->xClassPlugin->getCallableProxy(ClassB::class);
        $xClassCCallable = $this->xClassPlugin->getCallableProxy(ClassC::class);
        // Test callables classes
        $this->assertEquals(CallableObjectProxy::class, get_class($xClassACallable));
        $this->assertEquals(CallableObjectProxy::class, get_class($xClassBCallable));
        $this->assertEquals(CallableObjectProxy::class, get_class($xClassCCallable));
        // Check methods
        $this->assertTrue($xClassACallable->hasMethod('methodAa'));
        $this->assertTrue($xClassACallable->hasMethod('methodAb'));
        $this->assertFalse($xClassACallable->hasMethod('methodAc'));
    }
}
