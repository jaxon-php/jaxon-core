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
    public function testHtml()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestJQuery',
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
        // $this->assertEquals("$('#path1').html('This is the html content')", (string)$aCommands[0]['data']);

        $this->assertEquals('script', $aCommands[1]['options']['plugin']);
        $this->assertEquals('script.exec.expr', $aCommands[1]['name']);
        // $this->assertEquals("$('.path2', $('#context')).html('This is the html content')", (string)$aCommands[1]['data']);
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testAssign()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestJQuery',
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
        // $this->assertEquals("$('#path1').value = 'This is the html content'", (string)$aCommands[0]['data']);

        $this->assertEquals('script', $aCommands[1]['options']['plugin']);
        $this->assertEquals('script.exec.expr', $aCommands[1]['name']);
        // $this->assertEquals("$('#path3').value = $('#path2').value", (string)$aCommands[1]['data']);

        $this->assertEquals('script', $aCommands[2]['options']['plugin']);
        $this->assertEquals('script.exec.expr', $aCommands[2]['name']);
        // $this->assertEquals("$('#path3').attr('name', $('#path2').attr('name'))", (string)$aCommands[2]['data']);
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testClick()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'TestJQuery',
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
        // $this->assertEquals("$('#path1').click((e) => " .
        //     "{TestJQuery.html($(e.currentTarget).attr('data-value'));})", (string)$aCommands[0]['data']);

        $this->assertEquals('script', $aCommands[1]['options']['plugin']);
        $this->assertEquals('script.exec.expr', $aCommands[1]['name']);
        // $this->assertEquals("$('#path1').click((e) => " .
        //     "{TestJQuery.html($('.path', $('#context')));})", (string)$aCommands[1]['data']);

        $this->assertEquals('script', $aCommands[2]['options']['plugin']);
        $this->assertEquals('script.exec.expr', $aCommands[2]['name']);
        // $this->assertEquals("$('#path1').click((e) => {\$('#path2').toggle()})", (string)$aCommands[2]['data']);
    }
}
