<?php

namespace Jaxon\Tests\Response;

use Jaxon\Jaxon;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Jaxon\Response\Plugin\DataBag\DataBagPlugin;
use Jaxon\Response\Plugin\JQuery\JQueryPlugin;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

use function jaxon;

class ResponseTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.prefix.class', '');
        jaxon()->register(Jaxon::CALLABLE_DIR, __DIR__ . '/../defs/response');
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
        $this->assertEquals(DataBagPlugin::class, get_class(jaxon()->getResponse()->bags));
        $this->assertEquals(JQueryPlugin::class, get_class(jaxon()->getResponse()->jquery));
    }

    /**
     * @throws RequestException
     */
    public function testResponseContent()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function() {
            $xRequestFactory = new Psr17Factory();
            $xRequestCreator = new ServerRequestCreator(
                $xRequestFactory, // ServerRequestFactory
                $xRequestFactory, // UriFactory
                $xRequestFactory, // UploadedFileFactory
                $xRequestFactory  // StreamFactory
            );
            return $xRequestCreator->fromGlobals()->withParsedBody([
                'jxncls' => 'Misc',
                'jxnmthd' => 'simple',
                'jxnargs' => [],
            ])->withMethod('POST');
        });

        $this->assertEmpty(jaxon()->di()->getResponseManager()->getResponseContent());
        // Process the request and get the response
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(1, $xResponse->getCommandCount());
        // Check the reponse content
        $aContent = jaxon()->di()->getResponseManager()->getResponseContent();
        $this->assertCount(2, $aContent);
        $this->assertArrayHasKey('type', $aContent);
        $this->assertIsString($aContent['type']);
        $this->assertArrayHasKey('content', $aContent);
        $this->assertIsString($aContent['content']);
    }

    /**
     * @throws RequestException
     */
    public function testMergeResponse()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function() {
            $xRequestFactory = new Psr17Factory();
            $xRequestCreator = new ServerRequestCreator(
                $xRequestFactory, // ServerRequestFactory
                $xRequestFactory, // UriFactory
                $xRequestFactory, // UploadedFileFactory
                $xRequestFactory  // StreamFactory
            );
            return $xRequestCreator->fromGlobals()->withParsedBody([
                'jxncls' => 'Misc',
                'jxnmthd' => 'merge',
                'jxnargs' => [],
            ])->withMethod('POST');
        });

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
        jaxon()->di()->set(ServerRequestInterface::class, function() {
            $xRequestFactory = new Psr17Factory();
            $xRequestCreator = new ServerRequestCreator(
                $xRequestFactory, // ServerRequestFactory
                $xRequestFactory, // UriFactory
                $xRequestFactory, // UploadedFileFactory
                $xRequestFactory  // StreamFactory
            );
            return $xRequestCreator->fromGlobals()->withParsedBody([
                'jxncls' => 'Misc',
                'jxnmthd' => 'simple',
                'jxnargs' => [],
            ])->withMethod('POST');
        });

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
        jaxon()->di()->set(ServerRequestInterface::class, function() {
            $xRequestFactory = new Psr17Factory();
            $xRequestCreator = new ServerRequestCreator(
                $xRequestFactory, // ServerRequestFactory
                $xRequestFactory, // UriFactory
                $xRequestFactory, // UploadedFileFactory
                $xRequestFactory  // StreamFactory
            );
            return $xRequestCreator->fromGlobals()->withParsedBody([
                'jxncls' => 'Misc',
                'jxnmthd' => 'appendBefore',
                'jxnargs' => [],
            ])->withMethod('POST');
        });

        // Process the request and get the response
        jaxon()->di()->getRequestHandler()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(2, $xResponse->getCommandCount());
    }

    /**
     * @throws RequestException
     */
    public function testMergeResponseWithUpload()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function() {
            $xRequestFactory = new Psr17Factory();
            $xRequestCreator = new ServerRequestCreator(
                $xRequestFactory, // ServerRequestFactory
                $xRequestFactory, // UriFactory
                $xRequestFactory, // UploadedFileFactory
                $xRequestFactory  // StreamFactory
            );
            return $xRequestCreator->fromGlobals()->withParsedBody([
                'jxncls' => 'Misc',
                'jxnmthd' => 'mergeWithUpload',
                'jxnargs' => [],
            ])->withMethod('POST');
        });

        $this->expectException(RequestException::class);
        // Process the request and get the response
        jaxon()->di()->getRequestHandler()->processRequest();
    }
}
