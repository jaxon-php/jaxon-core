<?php

namespace Jaxon\Tests\TestResponse;

use Jaxon\Jaxon;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\TestCase;

use function Jaxon\jaxon;

class PluginDataBagTest extends TestCase
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
    public function testCommandGetValue()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'TestDataBag',
                'jxnmthd' => 'getValue',
                'jxnargs' => [],
            ])->withMethod('POST');
        });
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(1, $xResponse->getCommandCount());
        $aCommand = $xResponse->getCommands()[0];
        $this->assertEquals('as', $aCommand['cmd']);
        $this->assertEquals('div-id', $aCommand['id']);
        $this->assertEquals('innerHTML', $aCommand['prop']);
        $this->assertEquals('Default value', $aCommand['data']);
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testCommandSetValue()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'TestDataBag',
                'jxnmthd' => 'setValue',
                'jxnargs' => [],
            ])->withMethod('POST');
        });
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(1, $xResponse->getCommandCount());
        $aCommand = $xResponse->getCommands()[0];
        // $this->assertEquals('', json_encode($aCommand));
        $this->assertEquals('bags', $aCommand['plg']);
        $this->assertEquals('bags.set', $aCommand['cmd']);
        $this->assertEquals('value', $aCommand['data']['dataset']['key']);
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testCommandUpdateValueWithMethodPost()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'TestDataBag',
                'jxnmthd' => 'updateValue',
                'jxnargs' => [],
                'jxnbags' => '{"dataset":{"key1":"value1"}}',
            ])->withMethod('POST');
        });
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(2, $xResponse->getCommandCount());

        // Test the assign command
        $aAssignCommand = $xResponse->getCommands()[0];
        $this->assertEquals('as', $aAssignCommand['cmd']);
        $this->assertEquals('div-id', $aAssignCommand['id']);
        $this->assertEquals('innerHTML', $aAssignCommand['prop']);
        $this->assertEquals('value1', $aAssignCommand['data']);

        // Test the databag update command
        $aBagCommand = $xResponse->getCommands()[1];
        $this->assertEquals('bags', $aBagCommand['plg']);
        $this->assertEquals('bags.set', $aBagCommand['cmd']);
        $this->assertEquals('value1', $aBagCommand['data']['dataset']['key1']);
        $this->assertEquals('value2', $aBagCommand['data']['dataset']['key2']);
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testCommandUpdateValueWithMethodGet()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withQueryParams([
                'jxncls' => 'TestDataBag',
                'jxnmthd' => 'updateValue',
                'jxnargs' => [],
                'jxnbags' => '{"dataset":{"key1":"value1"}}',
            ])->withMethod('GET');
        });
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(2, $xResponse->getCommandCount());

        // Test the assign command
        $aAssignCommand = $xResponse->getCommands()[0];
        $this->assertEquals('as', $aAssignCommand['cmd']);
        $this->assertEquals('div-id', $aAssignCommand['id']);
        $this->assertEquals('innerHTML', $aAssignCommand['prop']);
        $this->assertEquals('value1', $aAssignCommand['data']);

        // Test the databag update command
        $aBagCommand = $xResponse->getCommands()[1];
        $this->assertEquals('bags', $aBagCommand['plg']);
        $this->assertEquals('bags.set', $aBagCommand['cmd']);
        $this->assertEquals('value1', $aBagCommand['data']['dataset']['key1']);
        $this->assertEquals('value2', $aBagCommand['data']['dataset']['key2']);
    }
}
