<?php

namespace Jaxon\Tests\TestRequestHandler;

use Jaxon\Jaxon;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\TestCase;


class ClassTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.response.send', false);
        jaxon()->register(Jaxon::CALLABLE_CLASS, 'Sample', dirname(__DIR__) . '/src/sample.php');
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
                        'name' => 'Sample',
                        'method' => 'myMethod',
                        'args' => [],
                    ]),
                ]));

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertFalse(jaxon()->di()->getCallableFunctionPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->assertTrue(jaxon()->di()->getCallableClassPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        jaxon()->di()->getCallableClassPlugin()->processRequest();
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
                        'name' => 'Sample',
                        'method' => 'myMethod',
                        'args' => [],
                    ]),
                ]));

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertFalse(jaxon()->di()->getCallableFunctionPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->assertTrue(jaxon()->di()->getCallableClassPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        jaxon()->di()->getCallableClassPlugin()->processRequest();
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
                        'name' => 'Sample',
                        'method' => 'myMethod',
                        'args' => [],
                    ]),
                ]));

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        jaxon()->processRequest();
        $this->assertNotNull(jaxon()->getResponse());
        $this->assertEquals(1, jaxon()->getResponse()->getCommandCount());
        $xCallableObject = jaxon()->di()->getCallableClassPlugin()->getCallable('Sample');
        $this->assertEquals('Sample', $xCallableObject->getClassName());

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
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'Sam ple',
                        'method' => 'myMethod',
                        'args' => [],
                    ]),
                ]));

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
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'NotRegistered',
                        'method' => 'myMethod',
                        'args' => [],
                    ]),
                ]));

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
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'Sample',
                        'method' => 'unknownMethod',
                        'args' => [],
                    ]),
                ]));

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
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'Sample',
                        'method' => 'my Method',
                        'args' => [],
                    ]),
                ]));

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->expectException(RequestException::class);
        jaxon()->processRequest();
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testRequestToExcludedClass()
    {
        jaxon()->setAppOption('', true);
        jaxon()->register(Jaxon::CALLABLE_CLASS, 'Excluded', [
            'include' => dirname(__DIR__) . '/src/excluded.php',
            'functions' => [
                '*' => [
                    'excluded' => true,
                ],
            ],
        ]);
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'Excluded',
                        'method' => 'action',
                        'args' => [],
                    ]),
                ]));

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->expectException(RequestException::class);
        jaxon()->processRequest();
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testRequestToExcludedMethod()
    {
        jaxon()->setAppOption('', true);
        jaxon()->register(Jaxon::CALLABLE_CLASS, 'Excluded', [
            'include' => dirname(__DIR__) . '/src/excluded.php',
            'functions' => [
                'action' => [
                    'excluded' => true,
                ],
            ],
        ]);
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'Excluded',
                        'method' => 'action',
                        'args' => [],
                    ]),
                ]));

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->expectException(RequestException::class);
        jaxon()->processRequest();
    }
}
