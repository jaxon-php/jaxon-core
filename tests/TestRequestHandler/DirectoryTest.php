<?php

namespace Jaxon\Tests\TestRequestHandler;

use Jaxon\Jaxon;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\TestCase;

class DirectoryTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.response.send', false);
        jaxon()->setOption('core.prefix.class', '');
        jaxon()->register(Jaxon::CALLABLE_DIR, dirname(__DIR__) . '/src/dir', [
            'classes' => [
                'ClassC' => [
                    'functions' => [
                        'methodCc' => [
                            'excluded' => true,
                        ],
                    ],
                ],
                'ClassD' => [
                    'excluded' => true,
                ],
            ],
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
    public function testGetRequestToJaxonClass()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withQueryParams([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'ClassA',
                        'method' => 'methodAa',
                        'args' => [],
                    ]),
                ]));

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
                        'name' => 'ClassB',
                        'method' => 'methodBb',
                        'args' => [],
                    ]),
                ]));

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
                        'name' => 'ClassC',
                        'method' => 'methodCa',
                        'args' => [],
                    ]),
                ]));

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        jaxon()->processRequest();
        $this->assertNotNull(jaxon()->getResponse());
        $this->assertEquals(1, jaxon()->getResponse()->getCommandCount());
        $xCallableObject = jaxon()->di()->getComponentPlugin()->getCallableProxy('ClassC');
        $this->assertEquals('ClassC', $xCallableObject->getClassName());

        $xAction = jaxon()->di()->getComponentPlugin()->getCallableAction();
        $this->assertNotNull($xAction);
        $this->assertTrue($xAction->isClass());
        $this->assertFalse($xAction->isFunction());
        $this->assertEquals('ClassC', $xAction->getClassName());
        $this->assertEquals('methodCa', $xAction->getMethodName());
        $this->assertEquals('', $xAction->getFunctionName());
    }
}
