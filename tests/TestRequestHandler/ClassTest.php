<?php

namespace Jaxon\Tests\TestRequestHandler;

use Jaxon\Jaxon;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\TestCase;

use function Jaxon\jaxon;

class ClassTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.response.send', false);
        jaxon()->register(Jaxon::CALLABLE_CLASS, 'Sample', __DIR__ . '/../src/sample.php');
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
                'jxncls' => 'Sample',
                'jxnmthd' => 'myMethod',
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertFalse(jaxon()->di()->getCallableFunctionPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->assertTrue(jaxon()->di()->getCallableClassPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
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
                'jxncls' => 'Sample',
                'jxnmthd' => 'myMethod',
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertFalse(jaxon()->di()->getCallableFunctionPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->assertTrue(jaxon()->di()->getCallableClassPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
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
                'jxncls' => 'Sample',
                'jxnmthd' => 'myMethod',
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        jaxon()->processRequest();
        $this->assertNotNull(jaxon()->getResponse());
        $this->assertEquals(1, jaxon()->getResponse()->getCommandCount());
        $xCallableObject = jaxon()->di()->getCallableClassPlugin()->getCallable('Sample');
        $this->assertEquals('Sample', get_class($xCallableObject->getRegisteredObject()));

        $xTarget = jaxon()->di()->getCallableClassPlugin()->getTarget();
        $this->assertNotNull($xTarget);
        $this->assertTrue($xTarget->isClass());
        $this->assertFalse($xTarget->isFunction());
        $this->assertEquals('Sample', $xTarget->getClassName());
        $this->assertEquals('myMethod', $xTarget->getMethodName());
        $this->assertEquals('', $xTarget->getFunctionName());
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testRequestWithIncorrectClassName()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'Sam ple',
                'jxnmthd' => 'myMethod',
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->expectException(RequestException::class);
        jaxon()->processRequest();
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testRequestWithUnknownClassName()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'NotRegistered',
                'jxnmthd' => 'myMethod',
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->expectException(RequestException::class);
        jaxon()->processRequest();
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testRequestWithUnknownMethodName()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'Sample',
                'jxnmthd' => 'unknownMethod',
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->expectException(RequestException::class);
        jaxon()->processRequest();
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testRequestWithIncorrectMethodName()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'Sample',
                'jxnmthd' => 'my Method',
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->expectException(RequestException::class);
        jaxon()->processRequest();
    }
}
