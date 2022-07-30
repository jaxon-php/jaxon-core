<?php

namespace Jaxon\Tests\TestRequestHandler;

use Jaxon\Jaxon;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Jaxon\Request\Target;
use Nyholm\Psr7Server\ServerRequestCreator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

use function Jaxon\jaxon;

class HookTest extends TestCase
{
    /**
     * @var Target
     */
    protected $xTarget;

    /**
     * @var mixed
     */
    protected $xCallable;

    /**
     * @var bool
     */
    protected $bEndRequest;

    /**
     * @var int
     */
    protected $nBootCount = 0;

    /**
     * @var string
     */
    protected $sMessage = '';

    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.response.send', false);
        jaxon()->setOption('core.prefix.class', '');
        jaxon()->register(Jaxon::CALLABLE_DIR, __DIR__ . '/../src/response', [
            'classes' => [
                'TestHk' => [
                    'functions' => [
                        '*' => [
                            '__before' => 'before',
                            '__after' => 'after',
                        ],
                        'three' => [
                            '__before' => ['before2'],
                        ],
                        'four' => [
                            '__after' => [
                                'after1' => ['p1'],
                                'after2' => ['p1', 'p2'],
                            ],
                        ],
                        'param' => [
                            '__before' => ['beforeParam' => ['__method__', '__args__']],
                        ],
                    ],
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
    public function testHookAllBefore()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'TestHk',
                'jxnmthd' => 'one',
                'jxnargs' => [],
            ])->withMethod('POST');
        });
        // Process the request and get the response
        jaxon()->processRequest();

        $xResponse = jaxon()->getResponse();
        $this->assertEquals(3, $xResponse->getCommandCount());
    }

    /**
     * @throws RequestException
     */
    public function testHookAllAfter()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'TestHk',
                'jxnmthd' => 'two',
                'jxnargs' => [],
            ])->withMethod('POST');
        });
        // Process the request and get the response
        jaxon()->processRequest();

        $xResponse = jaxon()->getResponse();
        $this->assertEquals(4, $xResponse->getCommandCount());
    }

    /**
     * @throws RequestException
     */
    public function testHookArrayBefore()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'TestHk',
                'jxnmthd' => 'three',
                'jxnargs' => [],
            ])->withMethod('POST');
        });
        // Process the request and get the response
        jaxon()->processRequest();

        $xResponse = jaxon()->getResponse();
        $this->assertEquals(5, $xResponse->getCommandCount());
    }

    /**
     * @throws RequestException
     */
    public function testHookArrayAfter()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'TestHk',
                'jxnmthd' => 'four',
                'jxnargs' => [],
            ])->withMethod('POST');
        });
        // Process the request and get the response
        jaxon()->processRequest();

        $xResponse = jaxon()->getResponse();
        $this->assertEquals(5, $xResponse->getCommandCount());
    }

    /**
     * @throws RequestException
     */
    public function testHookParamAccess()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'TestHk',
                'jxnmthd' => 'param',
                'jxnargs' => ['Svalue'],
            ])->withMethod('POST');
        });
        // Process the request and get the response
        jaxon()->processRequest();

        $xResponse = jaxon()->getResponse();
        $this->assertEquals(3, $xResponse->getCommandCount());
    }
}
