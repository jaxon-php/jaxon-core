<?php

namespace Jaxon\Tests\TestResponse;

use Jaxon\App\Databag\Databag;
use Jaxon\Jaxon;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Response\Databag\DatabagPlugin;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\TestCase;


class PluginDatabagTest extends TestCase
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
    public function testCommandGetValue()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestDatabag',
                        'method' => 'getValue',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(1, $xResponse->getCommandCount());
        $aCommand = $xResponse->getCommands()[0];
        $this->assertEquals('node.assign', $aCommand['name']);
        $this->assertEquals('div-id', $aCommand['args']['id']);
        $this->assertEquals('innerHTML', $aCommand['args']['attr']);
        $this->assertEquals('Default value', $aCommand['args']['value']);
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testCommandSetValue()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestDatabag',
                        'method' => 'setValue',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(1, $xResponse->getCommandCount());
        $aCommand = $xResponse->getCommands()[0];
        $this->assertEquals('bags', $aCommand['options']['plugin']);
        $this->assertEquals('databag.set', $aCommand['name']);

        /** @var Databag */
        $xDatabag = $aCommand['args']['values'];
        $this->assertEquals('value', $xDatabag->get('dataset', 'key'));
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testCommandUpdateValueWithMethodPost()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestDatabag',
                        'method' => 'updateValue',
                        'args' => [],
                    ]),
                    'jxnbags' => json_encode(['dataset' => ['key1' => 'value1']]),
                ])
                ->withMethod('POST'));
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(2, $xResponse->getCommandCount());

        // Test the assign command
        $aAssignCommand = $xResponse->getCommands()[0];
        $this->assertEquals('node.assign', $aAssignCommand['name']);
        $this->assertEquals('div-id', $aAssignCommand['args']['id']);
        $this->assertEquals('innerHTML', $aAssignCommand['args']['attr']);
        $this->assertEquals('value1', $aAssignCommand['args']['value']);

        // Test the databag update command
        $aBagCommand = $xResponse->getCommands()[1];
        $this->assertEquals('bags', $aBagCommand['options']['plugin']);
        $this->assertEquals('databag.set', $aBagCommand['name']);

        /** @var Databag */
        $xDatabag = $aBagCommand['args']['values'];
        $this->assertEquals('value1', $xDatabag->get('dataset', 'key1'));
        $this->assertEquals('value2', $xDatabag->get('dataset', 'key2'));
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testCommandUpdateValueWithMethodGet()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withQueryParams([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestDatabag',
                        'method' => 'updateValue',
                        'args' => [],
                    ]),
                    'jxnbags' => json_encode(['dataset' => ['key1' => 'value1']]),
                ])
                ->withMethod('GET'));
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(2, $xResponse->getCommandCount());

        // Test the assign command
        $aAssignCommand = $xResponse->getCommands()[0];
        $this->assertEquals('node.assign', $aAssignCommand['name']);
        $this->assertEquals('div-id', $aAssignCommand['args']['id']);
        $this->assertEquals('innerHTML', $aAssignCommand['args']['attr']);
        $this->assertEquals('value1', $aAssignCommand['args']['value']);

        // Test the databag update command
        $aBagCommand = $xResponse->getCommands()[1];
        $this->assertEquals('bags', $aBagCommand['options']['plugin']);
        $this->assertEquals('databag.set', $aBagCommand['name']);

        /** @var Databag */
        $xDatabag = $aBagCommand['args']['values'];
        $this->assertEquals('value1', $xDatabag->get('dataset', 'key1'));
        $this->assertEquals('value2', $xDatabag->get('dataset', 'key2'));
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testDecodeDatabagFromString()
    {
        // Inject the databag value directly into the plugin, as a string.
        jaxon()->di()->set(DatabagPlugin::class, fn() =>
            new DatabagPlugin(fn() => json_encode(['dataset' => ['key1' => 'value1']])));
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestDatabag',
                        'method' => 'updateValue',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(2, $xResponse->getCommandCount());

        // Test the assign command
        $aAssignCommand = $xResponse->getCommands()[0];
        $this->assertEquals('node.assign', $aAssignCommand['name']);
        $this->assertEquals('div-id', $aAssignCommand['args']['id']);
        $this->assertEquals('innerHTML', $aAssignCommand['args']['attr']);
        $this->assertEquals('value1', $aAssignCommand['args']['value']);

        // Test the databag update command
        $aBagCommand = $xResponse->getCommands()[1];
        $this->assertEquals('bags', $aBagCommand['options']['plugin']);
        $this->assertEquals('databag.set', $aBagCommand['name']);

        /** @var Databag */
        $xDatabag = $aBagCommand['args']['values'];
        $this->assertEquals('value1', $xDatabag->get('dataset', 'key1'));
        $this->assertEquals('value2', $xDatabag->get('dataset', 'key2'));
    }
}
