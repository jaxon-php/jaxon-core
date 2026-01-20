<?php

namespace Jaxon\Tests\TestResponse;

use Jaxon\Exception\SetupException;
use Jaxon\Jaxon;
use Jaxon\Plugin\Response\Databag\DatabagPlugin;
use Jaxon\Plugin\Response\Script\ScriptPlugin;
use Nyholm\Psr7Server\ServerRequestCreator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class ResponseCallTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.prefix.class', '');
        jaxon()->register(Jaxon::CALLABLE_DIR, dirname(__DIR__) . '/src/response');
    }

    /**
     * @throws SetupException
     */
    public function tearDown(): void
    {
        jaxon()->reset();
        parent::tearDown();
    }

    public function testCallConfirm()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestComponent',
                        'method' => 'confirm',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));

        // Process the request and get the response
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(1, $xResponse->getCommandCount());

        $aCommands = $xResponse->getCommands();
        $this->assertEquals('script.exec.expr', $aCommands[0]['name']);
    }

    public function testCallConfirmElseWarning()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestComponent',
                        'method' => 'confirmElseWarning',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));

        // Process the request and get the response
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(1, $xResponse->getCommandCount());

        $aCommands = $xResponse->getCommands();
        $this->assertEquals('script.exec.expr', $aCommands[0]['name']);
    }

    public function testCallConfirmElseError()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestComponent',
                        'method' => 'confirmElseError',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));

        // Process the request and get the response
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(1, $xResponse->getCommandCount());

        $aCommands = $xResponse->getCommands();
        $this->assertEquals('script.exec.expr', $aCommands[0]['name']);
    }

    public function testCallConditionIfeq()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestComponent',
                        'method' => 'ifeq',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));

        // Process the request and get the response
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(1, $xResponse->getCommandCount());

        $aCommands = $xResponse->getCommands();
        $this->assertEquals('script.exec.expr', $aCommands[0]['name']);
    }

    public function testCallConditionIfteq()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestComponent',
                        'method' => 'ifteq',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));

        // Process the request and get the response
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(1, $xResponse->getCommandCount());

        $aCommands = $xResponse->getCommands();
        $this->assertEquals('script.exec.expr', $aCommands[0]['name']);
    }

    public function testCallConditionIfne()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestComponent',
                        'method' => 'ifne',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));

        // Process the request and get the response
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(1, $xResponse->getCommandCount());

        $aCommands = $xResponse->getCommands();
        $this->assertEquals('script.exec.expr', $aCommands[0]['name']);
    }

    public function testCallConditionIfnte()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestComponent',
                        'method' => 'ifnte',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));

        // Process the request and get the response
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(1, $xResponse->getCommandCount());

        $aCommands = $xResponse->getCommands();
        $this->assertEquals('script.exec.expr', $aCommands[0]['name']);
    }

    public function testCallConditionIfgt()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestComponent',
                        'method' => 'ifgt',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));

        // Process the request and get the response
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(1, $xResponse->getCommandCount());

        $aCommands = $xResponse->getCommands();
        $this->assertEquals('script.exec.expr', $aCommands[0]['name']);
    }

    public function testCallConditionIfge()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestComponent',
                        'method' => 'ifge',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));

        // Process the request and get the response
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(1, $xResponse->getCommandCount());

        $aCommands = $xResponse->getCommands();
        $this->assertEquals('script.exec.expr', $aCommands[0]['name']);
    }

    public function testCallConditionIflt()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestComponent',
                        'method' => 'iflt',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));

        // Process the request and get the response
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(1, $xResponse->getCommandCount());

        $aCommands = $xResponse->getCommands();
        $this->assertEquals('script.exec.expr', $aCommands[0]['name']);
    }

    public function testCallConditionIfle()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestComponent',
                        'method' => 'ifle',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));

        // Process the request and get the response
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(1, $xResponse->getCommandCount());

        $aCommands = $xResponse->getCommands();
        $this->assertEquals('script.exec.expr', $aCommands[0]['name']);
    }
}
