<?php

namespace Jaxon\Tests\TestRequestHandler;

use Jaxon\Jaxon;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\TestCase;
use Jaxon\NsTests\DirA\ClassA;
use Jaxon\NsTests\DirB\ClassB;
use Jaxon\NsTests\DirC\ClassC;
use function Jaxon\jaxon;

class NamespaceTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.response.send', false);
        jaxon()->setOption('core.prefix.class', '');
        jaxon()->register(Jaxon::CALLABLE_DIR, __DIR__ . '/../src/dir_ns', 'Jaxon\NsTests');
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
     * @throws RequestException
     */
    public function testGetRequestToJaxonClass()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withQueryParams([
                'jxncls' => 'Jaxon.NsTests.DirA.ClassA',
                'jxnmthd' => 'methodAa',
                'jxnargs' => [],
            ])->withMethod('GET');
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getCallableClassPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->assertNotNull(jaxon()->di()->getCallableClassPlugin()->processRequest());
    }

    /**
     * @throws RequestException
     */
    public function testPostRequestToJaxonClass()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'Jaxon.NsTests.DirB.ClassB',
                'jxnmthd' => 'methodBb',
                'jxnargs' => [],
            ])->withMethod('POST');
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getCallableClassPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->assertNotNull(jaxon()->di()->getCallableClassPlugin()->processRequest());
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testRequestToJaxonClass()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'Jaxon.NsTests.DirC.ClassC',
                'jxnmthd' => 'methodCa',
                'jxnargs' => [],
            ])->withMethod('POST');
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        jaxon()->processRequest();
        $this->assertNotNull(jaxon()->getResponse());
        $this->assertEquals(2, jaxon()->getResponse()->getCommandCount());
        $xCallableObject = jaxon()->di()->getCallableClassPlugin()->getCallable(ClassC::class);
        $this->assertEquals(ClassC::class, get_class($xCallableObject->getRegisteredObject()));

        $xTarget = jaxon()->di()->getCallableClassPlugin()->getTarget();
        $this->assertNotNull($xTarget);
        $this->assertTrue($xTarget->isClass());
        $this->assertFalse($xTarget->isFunction());
        $this->assertEquals('Jaxon.NsTests.DirC.ClassC', $xTarget->getClassName());
        $this->assertEquals('methodCa', $xTarget->getMethodName());
        $this->assertEquals('', $xTarget->getFunctionName());
    }
}
