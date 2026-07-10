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

class NamespaceTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.response.send', false);
        jaxon()->setOption('core.prefix.class', '');
        jaxon()->register(Jaxon::CALLABLE_DIR, dirname(__DIR__) . '/src/dir_ns', 'Jaxon\NsTests');
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
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withQueryParams([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'Jaxon.NsTests.DirA.ClassA',
                        'method' => 'methodAa',
                        'args' => [],
                    ]),
                ])
                ->withMethod('GET'));

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getComponentPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        jaxon()->di()->getComponentPlugin()->processRequest();
    }

    /**
     * @throws RequestException
     */
    public function testPostRequestToJaxonClass()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'Jaxon.NsTests.DirB.ClassB',
                        'method' => 'methodBb',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getComponentPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        jaxon()->di()->getComponentPlugin()->processRequest();
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testRequestToJaxonClass()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'Jaxon.NsTests.DirC.ClassC',
                        'method' => 'methodCa',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        jaxon()->processRequest();
        $this->assertNotNull(jaxon()->getResponse());
        $this->assertEquals(2, jaxon()->getResponse()->getCommandCount());
        $xCallableObject = jaxon()->di()->getComponentPlugin()->getCallableProxy(ClassC::class);
        $this->assertEquals(ClassC::class, $xCallableObject->getClassName());

        $xAction = jaxon()->di()->getComponentPlugin()->getCallableAction();
        $this->assertNotNull($xAction);
        $this->assertTrue($xAction->isClass());
        $this->assertFalse($xAction->isFunction());
        $this->assertEquals('Jaxon.NsTests.DirC.ClassC', $xAction->getClassName());
        $this->assertEquals('methodCa', $xAction->getMethodName());
        $this->assertEquals('', $xAction->getFunctionName());
    }
}
