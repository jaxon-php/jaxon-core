<?php

namespace Jaxon\Tests\TestRegistrationApp;

use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Request\CallableComponent\ComponentPlugin;
use Jaxon\Plugin\Request\CallableComponent\DirectoryPlugin;
use Jaxon\Plugin\Request\CallableComponent\ComponentProxy;
use Jaxon\Tests\Ns\Ajax\ClassA;
use Jaxon\Tests\Ns\Ajax\ClassB;
use Jaxon\Tests\Ns\Ajax\ClassC;
use PHPUnit\Framework\TestCase;


class DirectoryTest extends TestCase
{
    /**
     * @var DirectoryPlugin
     */
    protected $xDirPlugin;

    /**
     * @var ComponentPlugin
     */
    protected $xClassPlugin;

    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->app()->setup(dirname(__DIR__) . '/config/app/directories.php');

        $this->xDirPlugin = jaxon()->di()->getDirectoryPlugin();
        $this->xClassPlugin = jaxon()->di()->getComponentPlugin();
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
        $this->assertEquals(ComponentProxy::class, get_class($xClassACallable));
        $this->assertEquals(ComponentProxy::class, get_class($xClassBCallable));
        $this->assertEquals(ComponentProxy::class, get_class($xClassCCallable));
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
        $this->assertEquals(ComponentProxy::class, get_class($xClassACallable));
        $this->assertEquals(ComponentProxy::class, get_class($xClassBCallable));
        $this->assertEquals(ComponentProxy::class, get_class($xClassCCallable));
        // Check methods
        $this->assertTrue($xClassACallable->hasMethod('methodAa'));
        $this->assertTrue($xClassACallable->hasMethod('methodAb'));
        $this->assertFalse($xClassACallable->hasMethod('methodAc'));
    }
}
