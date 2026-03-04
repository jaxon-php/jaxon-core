<?php

namespace Jaxon\Tests\TestResponse;

use Jaxon\Jaxon;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\TestCase;

class PluginScriptTest extends TestCase
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

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testJeHtml()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestJeSelector',
                        'method' => 'html',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $aCommands = jaxon()->getResponse()->getCommands();
        $this->assertCount(2, $aCommands);

        $this->assertEquals('script', $aCommands[0]['options']['plugin']);
        $this->assertEquals('script.exec.expr', $aCommands[0]['name']);

        $this->assertEquals('script', $aCommands[1]['options']['plugin']);
        $this->assertEquals('script.exec.expr', $aCommands[1]['name']);
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testJqHtml()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestJqSelector',
                        'method' => 'html',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $aCommands = jaxon()->getResponse()->getCommands();
        $this->assertCount(2, $aCommands);

        $this->assertEquals('script', $aCommands[0]['options']['plugin']);
        $this->assertEquals('script.exec.expr', $aCommands[0]['name']);

        $this->assertEquals('script', $aCommands[1]['options']['plugin']);
        $this->assertEquals('script.exec.expr', $aCommands[1]['name']);
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testJeAssign()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestJeSelector',
                        'method' => 'assign',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $aCommands = jaxon()->getResponse()->getCommands();
        $this->assertCount(3, $aCommands);

        $this->assertEquals('script', $aCommands[0]['options']['plugin']);
        $this->assertEquals('script.exec.expr', $aCommands[0]['name']);

        $this->assertEquals('script', $aCommands[1]['options']['plugin']);
        $this->assertEquals('script.exec.expr', $aCommands[1]['name']);

        $this->assertEquals('script', $aCommands[2]['options']['plugin']);
        $this->assertEquals('script.exec.expr', $aCommands[2]['name']);
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testJqAssign()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestJqSelector',
                        'method' => 'assign',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $aCommands = jaxon()->getResponse()->getCommands();
        $this->assertCount(3, $aCommands);

        $this->assertEquals('script', $aCommands[0]['options']['plugin']);
        $this->assertEquals('script.exec.expr', $aCommands[0]['name']);

        $this->assertEquals('script', $aCommands[1]['options']['plugin']);
        $this->assertEquals('script.exec.expr', $aCommands[1]['name']);

        $this->assertEquals('script', $aCommands[2]['options']['plugin']);
        $this->assertEquals('script.exec.expr', $aCommands[2]['name']);
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testJeClick()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestJeSelector',
                        'method' => 'click',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $aCommands = jaxon()->getResponse()->getCommands();
        $this->assertCount(3, $aCommands);

        $this->assertEquals('script', $aCommands[0]['options']['plugin']);
        $this->assertEquals('script.exec.expr', $aCommands[0]['name']);

        $this->assertEquals('script', $aCommands[1]['options']['plugin']);
        $this->assertEquals('script.exec.expr', $aCommands[1]['name']);

        $this->assertEquals('script', $aCommands[2]['options']['plugin']);
        $this->assertEquals('script.exec.expr', $aCommands[2]['name']);
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testJqClick()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestJqSelector',
                        'method' => 'click',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $aCommands = jaxon()->getResponse()->getCommands();
        $this->assertCount(3, $aCommands);

        $this->assertEquals('script', $aCommands[0]['options']['plugin']);
        $this->assertEquals('script.exec.expr', $aCommands[0]['name']);

        $this->assertEquals('script', $aCommands[1]['options']['plugin']);
        $this->assertEquals('script.exec.expr', $aCommands[1]['name']);

        $this->assertEquals('script', $aCommands[2]['options']['plugin']);
        $this->assertEquals('script.exec.expr', $aCommands[2]['name']);
    }
}
