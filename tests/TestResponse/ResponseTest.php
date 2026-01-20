<?php

namespace Jaxon\Tests\TestResponse;

use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Jaxon\Jaxon;
use Jaxon\Plugin\Response\Databag\DatabagPlugin;
use Jaxon\Plugin\Response\Script\ScriptPlugin;
use Nyholm\Psr7Server\ServerRequestCreator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class ResponseTest extends TestCase
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

    public function testResponsePluginAccess()
    {
        $this->assertEquals(DatabagPlugin::class, get_class(jaxon()->getResponse()->bags));
        $this->assertEquals(ScriptPlugin::class, get_class(jaxon()->getResponse()->script));
        $this->assertNull(jaxon()->getResponse()->noname);
    }

    /**
     * @throws RequestException
     */
    public function testResponseContent()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'Misc',
                        'method' => 'simple',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));

        $this->assertEquals('{"jxn":{"commands":[]}}', jaxon()->di()->getResponseManager()->getOutput());
        // Process the request and get the response
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(1, $xResponse->getCommandCount());
        // Check the reponse content
        $sContent = jaxon()->di()->getResponseManager()->getOutput();
        $this->assertNotEmpty($sContent);
        $this->assertIsString($sContent);
        $this->assertEquals('application/json; charset="utf-8"', jaxon()->getContentType());
        $this->assertEquals('utf-8', jaxon()->getCharacterEncoding());

        $this->assertEquals('', $xResponse->getErrorMessage());
    }

    /**
     * @throws RequestException
     */
    public function testCommandOption()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'Misc',
                        'method' => 'simple',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));

        // Process the request and get the response
        jaxon()->di()->getRequestHandler()->processRequest();

        $xResponse = jaxon()->getResponse();
        $aCommands = $xResponse->getCommands();
        $this->assertCount(1, $aCommands);
        // Set an option on the response
        $this->assertEquals('value1', $aCommands[0]['options']['name1']);
        $this->assertEquals('value2', $aCommands[0]['options']['name2']);
    }

    /**
     * @throws RequestException
     */
    public function testMergeResponse()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'Misc',
                        'method' => 'merge',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));

        // Process the request and get the response
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(2, $xResponse->getCommandCount());
    }

    /**
     * @throws RequestException
     */
    public function testDebugCommand()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'Misc',
                        'method' => 'simple',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));

        jaxon()->di()->getResponseManager()->debug('This is the first debug message!!');
        // Process the request and get the response
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(2, $xResponse->getCommandCount());
    }

    /**
     * @throws RequestException
     */
    public function testAppendResponseBefore()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'Misc',
                        'method' => 'appendBefore',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));

        // Process the request and get the response
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(2, $xResponse->getCommandCount());
    }

    public function testResponseCommands()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'Misc',
                        'method' => 'commands',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));

        // Process the request and get the response
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(7, $xResponse->getCommandCount());

        $aCommands = $xResponse->getCommands();
        $this->assertEquals('node.create', $aCommands[0]['name']);
        $this->assertEquals('node.insert.before', $aCommands[1]['name']);
        $this->assertEquals('node.insert.before', $aCommands[2]['name']);
        $this->assertEquals('node.insert.after', $aCommands[3]['name']);
        $this->assertEquals('handler.event.add', $aCommands[4]['name']);
        $this->assertEquals('node.bind', $aCommands[5]['name']);
        $this->assertEquals('node.bind', $aCommands[6]['name']);
    }

    public function testResponseJsCommands()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'Misc',
                        'method' => 'jsCommands',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));

        // Process the request and get the response
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(4, $xResponse->getCommandCount());

        $aCommands = $xResponse->getCommands();
        $this->assertEquals('script.exec.expr', $aCommands[0]['name']);
        $this->assertEquals('script.exec.expr', $aCommands[1]['name']);
        $this->assertEquals('script.exec.expr', $aCommands[2]['name']);
        $this->assertEquals('script.exec.expr', $aCommands[3]['name']);
    }

    public function testResponsePaginator()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'Misc',
                        'method' => 'paginate',
                        'args' => [2],
                    ]),
                ])
                ->withMethod('POST'));

        // Process the request and get the response
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(2, $xResponse->getCommandCount());

        $aCommands = $xResponse->getCommands();
        $this->assertEquals('node.assign', $aCommands[0]['name']);
        $this->assertEquals('pg.paginate', $aCommands[1]['name']);
    }
}
