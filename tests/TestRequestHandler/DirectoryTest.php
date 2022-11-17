<?php

namespace Jaxon\Tests\TestRequestHandler;

use Jaxon\Jaxon;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\TestCase;
use function Jaxon\jaxon;

class DirectoryTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.response.send', false);
        jaxon()->setOption('core.prefix.class', '');
        jaxon()->register(Jaxon::CALLABLE_DIR, __DIR__ . '/../src/dir');
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
                'jxncls' => 'ClassA',
                'jxnmthd' => 'methodAa',
                'jxnargs' => [],
            ]);
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
                'jxncls' => 'ClassB',
                'jxnmthd' => 'methodBb',
                'jxnargs' => [],
            ]);
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
                'jxncls' => 'ClassC',
                'jxnmthd' => 'methodCa',
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        jaxon()->processRequest();
        $this->assertNotNull(jaxon()->getResponse());
        $this->assertEquals(1, jaxon()->getResponse()->getCommandCount());
        $xCallableObject = jaxon()->di()->getCallableClassPlugin()->getCallable('ClassC');
        $this->assertEquals('ClassC', get_class($xCallableObject->getRegisteredObject()));

        $xTarget = jaxon()->di()->getCallableClassPlugin()->getTarget();
        $this->assertNotNull($xTarget);
        $this->assertTrue($xTarget->isClass());
        $this->assertFalse($xTarget->isFunction());
        $this->assertEquals('ClassC', $xTarget->getClassName());
        $this->assertEquals('methodCa', $xTarget->getMethodName());
        $this->assertEquals('', $xTarget->getFunctionName());
    }
}
