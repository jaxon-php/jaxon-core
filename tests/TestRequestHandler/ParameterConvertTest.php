<?php

namespace Jaxon\Tests\TestRequestHandler;

require dirname(__DIR__) . '/src/convert.php';

use Jaxon\Exception\RequestException;
use Jaxon\Jaxon;
use Jaxon\Exception\SetupException;
use Nyholm\Psr7Server\ServerRequestCreator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class ParameterConvertTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.response.send', false);
        jaxon()->register(Jaxon::CALLABLE_CLASS, 'Convert');
        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'convert');
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
    public function testRequestWithNoPlugin()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'func',
                        'name' => 'convert',
                        'args' => ['Nope', '10', 'Hello'],
                    ]),
                ])
                ->withMethod('POST'));

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        jaxon()->processRequest();
        $this->assertNotNull(jaxon()->getResponse());
        $this->assertEquals(1, jaxon()->getResponse()->getCommandCount());
    }

    public function testGetParameterProcessing()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'Convert',
                        'method' => 'convert',
                        'args' => ['Nope', '10', 'Hello'],
                    ]),
                ]));

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        jaxon()->processRequest();
        $this->assertNotNull(jaxon()->getResponse());
        $this->assertEquals(1, jaxon()->getResponse()->getCommandCount());
    }
}
