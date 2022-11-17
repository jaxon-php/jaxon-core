<?php

namespace Jaxon\Tests\TestResponse;

use Jaxon\Jaxon;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\TestCase;

use function Jaxon\jaxon;
use function Jaxon\jq;

class PluginJQueryTest extends TestCase
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
    public function testHtml()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'TestJQuery',
                'jxnmthd' => 'html',
                'jxnargs' => [],
            ])->withMethod('POST');
        });
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $aCommands = jaxon()->getResponse()->getCommands();
        $this->assertCount(2, $aCommands);

        $this->assertEquals('jquery', $aCommands[0]['plg']);
        $this->assertEquals('jquery', $aCommands[0]['cmd']);
        $this->assertEquals("$('#path1').html('This is the html content')", (string)$aCommands[0]['data']);

        $this->assertEquals('jquery', $aCommands[1]['plg']);
        $this->assertEquals('jquery', $aCommands[1]['cmd']);
        $this->assertEquals("$('.path2', $('#context')).html('This is the html content')", (string)$aCommands[1]['data']);
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testAssign()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'TestJQuery',
                'jxnmthd' => 'assign',
                'jxnargs' => [],
            ])->withMethod('POST');
        });
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $aCommands = jaxon()->getResponse()->getCommands();
        $this->assertCount(3, $aCommands);

        $this->assertEquals('jquery', $aCommands[0]['plg']);
        $this->assertEquals('jquery', $aCommands[0]['cmd']);
        $this->assertEquals("$('#path1').value = 'This is the html content'", (string)$aCommands[0]['data']);

        $this->assertEquals('jquery', $aCommands[1]['plg']);
        $this->assertEquals('jquery', $aCommands[1]['cmd']);
        $this->assertEquals("$('#path3').value = $('#path2').value", (string)$aCommands[1]['data']);

        $this->assertEquals('jquery', $aCommands[2]['plg']);
        $this->assertEquals('jquery', $aCommands[2]['cmd']);
        $this->assertEquals("$('#path3').attr('name', $('#path2').attr('name'))", (string)$aCommands[2]['data']);
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testClick()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'TestJQuery',
                'jxnmthd' => 'click',
                'jxnargs' => [],
            ])->withMethod('POST');
        });
        // Process the request and get the response
        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getRequestHandler()->processRequest();
        $aCommands = jaxon()->getResponse()->getCommands();
        $this->assertCount(2, $aCommands);

        $this->assertEquals('jquery', $aCommands[0]['plg']);
        $this->assertEquals('jquery', $aCommands[0]['cmd']);
        $this->assertEquals("$('#path1').click(function(){jxnVar1=$(this).attr('data-value');" .
            "TestJQuery.html(jxnVar1);})", (string)$aCommands[0]['data']);

        $this->assertEquals('jquery', $aCommands[1]['plg']);
        $this->assertEquals('jquery', $aCommands[1]['cmd']);
        $this->assertEquals("$('#path1').click(function(){jxnVar1=$('.path', $('#context'));" .
            "TestJQuery.html(jxnVar1);})", (string)$aCommands[1]['data']);
    }
}
