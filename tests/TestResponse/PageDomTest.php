<?php

namespace Jaxon\Tests\TestResponse;

use Jaxon\Jaxon;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\TestCase;

use function Jaxon\jaxon;

class PageDomTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.prefix.class', '');
        jaxon()->register(Jaxon::CALLABLE_DIR, __DIR__ . '/../src/response');
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
    public function testCommandAssign()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'TestDom',
                'jxnmthd' => 'assign',
                'jxnargs' => [],
            ])->withMethod('POST');
        });
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
    public function testCommandHtml()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'TestDom',
                'jxnmthd' => 'html',
                'jxnargs' => [],
            ])->withMethod('POST');
        });
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
    public function testCommandAppend()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'TestDom',
                'jxnmthd' => 'append',
                'jxnargs' => [],
            ])->withMethod('POST');
        });
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
    public function testCommandPrepend()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'TestDom',
                'jxnmthd' => 'prepend',
                'jxnargs' => [],
            ])->withMethod('POST');
        });
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
    public function testCommandReplace()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'TestDom',
                'jxnmthd' => 'replace',
                'jxnargs' => [],
            ])->withMethod('POST');
        });
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
    public function testCommandClear()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'TestDom',
                'jxnmthd' => 'clear',
                'jxnargs' => [],
            ])->withMethod('POST');
        });
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
    public function testCommandRemove()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'TestDom',
                'jxnmthd' => 'remove',
                'jxnargs' => [],
            ])->withMethod('POST');
        });
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
    public function testCommandInsertBefore()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'TestDom',
                'jxnmthd' => 'insertBefore',
                'jxnargs' => [],
            ])->withMethod('POST');
        });
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(1, $xResponse->getCommandCount());
    }
}
