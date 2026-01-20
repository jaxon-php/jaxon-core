<?php

namespace Jaxon\Tests\TestRequestHandler;

use Jaxon\App\View\TemplateView;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Pimple\Container as AppContainer;
use Pimple\Psr11\Container as PsrContainer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\NullLogger;
use PHPUnit\Framework\TestCase;


class PsrRequestHandlerTest extends TestCase
{
    private $xPsrConfigMiddleware;

    private $xPsrAjaxMiddleware;

    private $xPsrRequestHandler;

    private $xEmptyRequestHandler;

    public function setUp(): void
    {
        jaxon()->psr()
            ->logger(new NullLogger())
            ->container(new PsrContainer(new AppContainer()))
            ->view('default', '.php', fn() => jaxon()->di()->g(TemplateView::class));

        $this->xPsrConfigMiddleware = jaxon()->psr()->config(dirname(__DIR__) . '/config/app/classes.php');
        $this->xPsrAjaxMiddleware = jaxon()->psr()->ajax();
        $this->xPsrRequestHandler = jaxon()->psr()->handler();
        // PSR request handler that does nothing, useful to call the config middleware.
        $this->xEmptyRequestHandler = new class implements RequestHandlerInterface
        {
            private $xPsr17Factory;

            public function __construct()
            {
                $this->xPsr17Factory = new Psr17Factory();
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->xPsr17Factory->createResponse();
            }
        };
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
    public function testJaxonRequestToAjaxMiddleware()
    {
        // The server request is provided to the PSR components, and not registered in the container.
        $xRequest = jaxon()->di()->g(ServerRequestCreator::class)
            ->fromGlobals()
            ->withParsedBody([
                'jxncall' => json_encode([
                    'type' => 'class',
                    'name' => 'Sample',
                    'method' => 'myMethod',
                    'args' => [],
                ]),
            ])
            ->withMethod('POST');

        // Call the config middleware
        $this->xPsrConfigMiddleware->process($xRequest, $this->xEmptyRequestHandler);
        // Call the ajax middleware
        $xPsrResponse = $this->xPsrAjaxMiddleware->process($xRequest, $this->xEmptyRequestHandler);

        $xJaxonResponse = jaxon()->getResponse();
        $this->assertNotNull($xJaxonResponse);
        $this->assertEquals(1, $xJaxonResponse->getCommandCount());
        $xCallableObject = jaxon()->di()->getCallableClassPlugin()->getCallable('Sample');
        $this->assertEquals('Sample', $xCallableObject->getClassName());

        $xTarget = jaxon()->di()->getCallableClassPlugin()->getTarget();
        $this->assertNotNull($xTarget);
        $this->assertTrue($xTarget->isClass());
        $this->assertFalse($xTarget->isFunction());
        $this->assertEquals('Sample', $xTarget->getClassName());
        $this->assertEquals('myMethod', $xTarget->getMethodName());
        $this->assertEquals('', $xTarget->getFunctionName());

        // Both responses must have the same content and content type
        $this->assertEquals($xPsrResponse->getBody()->__toString(), $xJaxonResponse->getOutput());
        $this->assertEquals($xPsrResponse->getHeader('content-type')[0], $xJaxonResponse->getContentType());
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testJaxonRequestToRequestHandler()
    {
        // The server request is provided to the PSR components, and not registered in the container.
        $xRequest = jaxon()->di()->g(ServerRequestCreator::class)
            ->fromGlobals()
            ->withQueryParams([
                'jxncall' => json_encode([
                    'type' => 'class',
                    'name' => 'Sample',
                    'method' => 'myMethod',
                    'args' => [],
                ]),
            ])
            ->withMethod('GET');

        // Call the config middleware
        $this->xPsrConfigMiddleware->process($xRequest, $this->xEmptyRequestHandler);
        // Call the request handler
        $xPsrResponse = $this->xPsrRequestHandler->handle($xRequest);

        $xJaxonResponse = jaxon()->getResponse();
        $this->assertNotNull($xJaxonResponse);
        $this->assertEquals(1, $xJaxonResponse->getCommandCount());
        $xCallableObject = jaxon()->di()->getCallableClassPlugin()->getCallable('Sample');
        $this->assertEquals('Sample', $xCallableObject->getClassName());

        $xTarget = jaxon()->di()->getCallableClassPlugin()->getTarget();
        $this->assertNotNull($xTarget);
        $this->assertTrue($xTarget->isClass());
        $this->assertFalse($xTarget->isFunction());
        $this->assertEquals('Sample', $xTarget->getClassName());
        $this->assertEquals('myMethod', $xTarget->getMethodName());
        $this->assertEquals('', $xTarget->getFunctionName());

        // Both responses must have the same content and content type
        $this->assertEquals($xPsrResponse->getBody()->__toString(), $xJaxonResponse->getOutput());
        $this->assertEquals($xPsrResponse->getHeader('content-type')[0], $xJaxonResponse->getContentType());
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testHttpRequestToAjaxMiddleware()
    {
        // The server request is provided to the PSR components.
        $xRequest = jaxon()->di()->g(ServerRequestCreator::class)->fromGlobals();

        // Call the config middleware
        $this->xPsrConfigMiddleware->process($xRequest, $this->xEmptyRequestHandler);
        // Call the ajax middleware
        $this->xPsrAjaxMiddleware->process($xRequest, $this->xEmptyRequestHandler);

        $this->assertEquals(0, jaxon()->getResponse()->getCommandCount());
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testHttpRequestToRequestHandler()
    {
        // The server request is provided to the PSR components.
        $xRequest = jaxon()->di()->g(ServerRequestCreator::class)->fromGlobals();

        // Call the config middleware
        $this->xPsrConfigMiddleware->process($xRequest, $this->xEmptyRequestHandler);
        // Call the request handler
        $this->expectException(RequestException::class);
        $this->xPsrRequestHandler->handle($xRequest);
    }
}
