<?php

namespace Jaxon\Tests\TestResponse;

use Jaxon\Jaxon;
use Jaxon\Exception\AppException;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\TestCase;

class PageJsTest extends TestCase
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
    public function testCommandRedirect()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestJs',
                        'method' => 'redirect',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(2, $xResponse->getCommandCount());
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testCommandConfirm()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestJs',
                        'method' => 'confirm',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(3, $xResponse->getCommandCount());
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testNestedConfirm()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestJs',
                        'method' => 'nestedConfirm',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());

        $this->expectException(AppException::class);
        jaxon()->di()->getRequestHandler()->processRequest();
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testCommandAlert()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestJs',
                        'method' => 'message',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(1, $xResponse->getCommandCount());
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testCommandDialog()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestJs',
                        'method' => 'dialog',
                        'args' => ['Message title', 'Message content'],
                    ]),
                ])
                ->withMethod('POST'));
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(1, $xResponse->getCommandCount());
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testCommandDebug()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestJs',
                        'method' => 'debug',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));

        // Add a debug message using the reponse manager.
        $xResponseManager = jaxon()->di()->getResponseManager();
        $xResponseManager->debug('This is a debug message!!');

        // Error message
        $xResponseManager->setErrorMessage($xResponseManager->trans('Error!!!'));

        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();

        $xResponse = $xResponseManager->ajaxResponse();
        $this->assertEquals(2, $xResponse->getCommandCount());

        $aDebugMessages = $xResponseManager->getDebugMessages();
        $this->assertNotEmpty($aDebugMessages);
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testCommandCall()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestJs',
                        'method' => 'call',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(1, $xResponse->getCommandCount());
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testCommandSetEvent()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestJs',
                        'method' => 'setEvent',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(1, $xResponse->getCommandCount());
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testCommandOnClick()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestJs',
                        'method' => 'onClick',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(1, $xResponse->getCommandCount());
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testCommandAddHandler()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestJs',
                        'method' => 'addHandler',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(1, $xResponse->getCommandCount());
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testCommandRemoveHandler()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestJs',
                        'method' => 'removeHandler',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(1, $xResponse->getCommandCount());
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testCommandSleep()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestJs',
                        'method' => 'sleep',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(1, $xResponse->getCommandCount());
    }
}
