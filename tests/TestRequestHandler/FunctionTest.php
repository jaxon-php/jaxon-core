<?php

namespace Jaxon\Tests\TestRequestHandler;

use Jaxon\Jaxon;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\TestCase;

class FunctionTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.response.send', false);
        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'my_first_function',
            dirname(__DIR__) . '/src/first.php');
        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'my_second_function', [
            'alias' => 'my_alias_function',
            'include' => dirname(__DIR__) . '/src/functions.php',
        ]);
        // Register a class method as a function
        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'myMethod', [
            'alias' => 'my_third_function',
            'class' => 'Sample',
            'include' => dirname(__DIR__) . '/src/sample.php',
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
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withQueryParams([
                    'jxncall' => json_encode([
                        'type' => 'func',
                        'name' => 'my_first_function',
                        'args' => [],
                    ]),
                ]));

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getFunctionPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->assertFalse(jaxon()->di()->getComponentPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        jaxon()->di()->getFunctionPlugin()->processRequest();
        $this->assertCount(1, jaxon()->getResponse()->getCommands());
    }

    /**
     * @throws RequestException
     */
    public function testPostRequestToJaxonFunction()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'func',
                        'name' => 'my_first_function',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getFunctionPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->assertFalse(jaxon()->di()->getComponentPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        jaxon()->di()->getFunctionPlugin()->processRequest();
        $this->assertCount(1, jaxon()->getResponse()->getCommands());
    }

    /**
     * @throws RequestException
     */
    public function testRequestToFunctionWithoutReturn()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'func',
                        'name' => 'my_alias_function',
                        'args' => [],
                    ]),
                    ])
                ->withMethod('POST'));

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        // The function returns no data
        $this->assertNull(jaxon()->di()->getFunctionPlugin()->processRequest());
        $this->assertCount(1, jaxon()->getResponse()->getCommands());
    }

    /**
     * @throws RequestException
     */
    public function testRequestToJaxonFunction()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'func',
                        'name' => 'my_first_function',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getFunctionPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        jaxon()->di()->getFunctionPlugin()->processRequest();

        $xAction = jaxon()->di()->getFunctionPlugin()->getCallableAction();
        $this->assertNotNull($xAction);
        $this->assertFalse($xAction->isClass());
        $this->assertTrue($xAction->isFunction());
        $this->assertEquals('', $xAction->getClassName());
        $this->assertEquals('', $xAction->getMethodName());
        $this->assertEquals('my_first_function', $xAction->getFunctionName());
    }

    /**
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
                        'type' => 'func',
                        'name' => 'my_third_function',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getFunctionPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        jaxon()->di()->getFunctionPlugin()->processRequest();
    }

    /**
     * @throws RequestException
     */
    public function testRequestToJaxonFunctionIncorrectName()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'func',
                        'name' => 'my function', // A space in the function name
                        'args' => [],
                    ]),
                    ])
                ->withMethod('POST'));

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getFunctionPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->expectException(RequestException::class);
        jaxon()->di()->getFunctionPlugin()->processRequest();
    }
}
