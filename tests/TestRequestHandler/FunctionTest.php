<?php

namespace Jaxon\Tests\TestRequestHandler;

use Jaxon\Jaxon;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\TestCase;
use function Jaxon\jaxon;

class FunctionTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.response.send', false);
        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'my_first_function',
            __DIR__ . '/../src/first.php');
        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'my_second_function', [
            'alias' => 'my_alias_function',
            'include' => __DIR__ . '/../src/functions.php',
        ]);
        // Register a class method as a function
        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'myMethod', [
            'alias' => 'my_third_function',
            'class' => 'Sample',
            'include' => __DIR__ . '/../src/sample.php',
        ]);
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
    public function testGetRequestToJaxonFunction()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withQueryParams([
                'jxnfun' => 'my_first_function',
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getCallableFunctionPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->assertFalse(jaxon()->di()->getCallableClassPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->assertNotNull(jaxon()->di()->getCallableFunctionPlugin()->processRequest());
        $this->assertCount(1, jaxon()->getResponse()->getCommands());
    }

    /**
     * @throws RequestException
     */
    public function testPostRequestToJaxonFunction()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxnfun' => 'my_first_function',
                'jxnargs' => [],
            ])->withMethod('POST');
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getCallableFunctionPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->assertFalse(jaxon()->di()->getCallableClassPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->assertNotNull(jaxon()->di()->getCallableFunctionPlugin()->processRequest());
        $this->assertCount(1, jaxon()->getResponse()->getCommands());
    }

    /**
     * @throws RequestException
     */
    public function testRequestToFunctionWithoutReturn()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxnfun' => 'my_alias_function',
                'jxnargs' => [],
            ])->withMethod('POST');
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        // The function returns no data
        $this->assertNull(jaxon()->di()->getCallableFunctionPlugin()->processRequest());
        $this->assertCount(1, jaxon()->getResponse()->getCommands());
    }

    /**
     * @throws RequestException
     */
    public function testRequestToJaxonFunction()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxnfun' => 'my_first_function',
                'jxnargs' => [],
            ])->withMethod('POST');
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getCallableFunctionPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->assertNotNull(jaxon()->di()->getCallableFunctionPlugin()->processRequest());

        $xTarget = jaxon()->di()->getCallableFunctionPlugin()->getTarget();
        $this->assertNotNull($xTarget);
        $this->assertFalse($xTarget->isClass());
        $this->assertTrue($xTarget->isFunction());
        $this->assertEquals('', $xTarget->getClassName());
        $this->assertEquals('', $xTarget->getMethodName());
        $this->assertEquals('my_first_function', $xTarget->getFunctionName());
    }

    /**
     * @throws RequestException
     */
    public function testRequestToJaxonClass()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxnfun' => 'my_third_function',
                'jxnargs' => [],
            ])->withMethod('POST');
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getCallableFunctionPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->assertNotNull(jaxon()->di()->getCallableFunctionPlugin()->processRequest());
    }

    /**
     * @throws RequestException
     */
    public function testRequestToJaxonFunctionIncorrectName()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxnfun' => 'my function', // A space in the function name
                'jxnargs' => [],
            ])->withMethod('POST');
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getCallableFunctionPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->expectException(RequestException::class);
        jaxon()->di()->getCallableFunctionPlugin()->processRequest();
    }
}
