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
        $this->assertEquals('dom.assign', $aCommand['name']);
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
        $this->assertEquals('bags', $aCommand['options']['plugin']);
        $this->assertEquals('databag.set', $aCommand['name']);
        // $this->assertEquals('value', $aCommand['args']['values']['data']['dataset']['key']);
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
        $this->assertEquals('dom.assign', $aAssignCommand['name']);
        $this->assertEquals('div-id', $aAssignCommand['args']['id']);
        $this->assertEquals('innerHTML', $aAssignCommand['args']['attr']);
        $this->assertEquals('value1', $aAssignCommand['args']['value']);

        // Test the databag update command
        $aBagCommand = $xResponse->getCommands()[1];
        $this->assertEquals('bags', $aBagCommand['options']['plugin']);
        $this->assertEquals('databag.set', $aBagCommand['name']);
        // $this->assertEquals('value1', $aBagCommand['data']['dataset']['key1']);
        // $this->assertEquals('value2', $aBagCommand['data']['dataset']['key2']);
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
        $this->assertEquals('dom.assign', $aAssignCommand['name']);
        $this->assertEquals('div-id', $aAssignCommand['args']['id']);
        $this->assertEquals('innerHTML', $aAssignCommand['args']['attr']);
        $this->assertEquals('value1', $aAssignCommand['args']['value']);

        // Test the databag update command
        $aBagCommand = $xResponse->getCommands()[1];
        $this->assertEquals('bags', $aBagCommand['options']['plugin']);
        $this->assertEquals('databag.set', $aBagCommand['name']);
        // $this->assertEquals('value1', $aBagCommand['data']['dataset']['key1']);
        // $this->assertEquals('value2', $aBagCommand['data']['dataset']['key2']);
    }
}
