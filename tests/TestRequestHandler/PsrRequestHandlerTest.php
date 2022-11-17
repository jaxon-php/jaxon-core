<?php

namespace Jaxon\Tests\TestRequestHandler;

require_once(__DIR__ . '/../../vendor/jaxon-php/jaxon-upload/src/start.php');

use Jaxon\App\View\TemplateView;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Jaxon\Upload\UploadResponse;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\UploadedFile;
use Nyholm\Psr7Server\ServerRequestCreator;
use Pimple\Container as AppContainer;
use Pimple\Psr11\Container as PsrContainer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\NullLogger;
use PHPUnit\Framework\TestCase;

use function Jaxon\jaxon;
use function Jaxon\Upload\registerUpload;

class PsrRequestHandlerTest extends TestCase
{
    private $xPsrConfigMiddleware;

    private $xPsrAjaxMiddleware;

    private $xPsrRequestHandler;

    private $xEmptyRequestHandler;

    /**
     * @var string
     */
    protected $sNameWhite;

    /**
     * @var string
     */
    protected $tmpDir;

    /**
     * @var string
     */
    protected $sSrcWhite;

    /**
     * @var string
     */
    protected $sPathWhite;

    /**
     * @var int
     */
    protected $sSizeWhite;

    public function setUp(): void
    {
        jaxon()->psr()
            ->logger(new NullLogger())
            ->container(new PsrContainer(new AppContainer()))
            ->view('default', '.php', function() {
                return jaxon()->di()->g(TemplateView::class);
            });

        $this->xPsrConfigMiddleware = jaxon()->psr()->config(__DIR__ . '/../config/app/classes.php');
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

        $this->tmpDir = realpath(__DIR__ . '/../upload/tmp');
        $this->sSrcWhite = __DIR__ . '/../upload/src/white.png';
        $this->sNameWhite = 'white.png';
        $this->sPathWhite = "{$this->tmpDir}/{$this->sNameWhite}";
        $this->sSizeWhite = filesize($this->sSrcWhite);
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
        $xRequest = jaxon()->di()->g(ServerRequestCreator::class)->fromGlobals()
            ->withParsedBody([
                'jxncls' => 'Sample',
                'jxnmthd' => 'myMethod',
                'jxnargs' => [],
            ])->withMethod('POST');

        // Call the config middleware
        $this->xPsrConfigMiddleware->process($xRequest, $this->xEmptyRequestHandler);
        // Call the ajax middleware
        $xPsrResponse = $this->xPsrAjaxMiddleware->process($xRequest, $this->xEmptyRequestHandler);

        $xJaxonResponse = jaxon()->getResponse();
        $this->assertNotNull($xJaxonResponse);
        $this->assertEquals(1, $xJaxonResponse->getCommandCount());
        $xCallableObject = jaxon()->di()->getCallableClassPlugin()->getCallable('Sample');
        $this->assertEquals('Sample', get_class($xCallableObject->getRegisteredObject()));

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
        $xRequest = jaxon()->di()->g(ServerRequestCreator::class)->fromGlobals()
            ->withQueryParams([
                'jxncls' => 'Sample',
                'jxnmthd' => 'myMethod',
                'jxnargs' => [],
            ])->withMethod('GET');

        // Call the config middleware
        $this->xPsrConfigMiddleware->process($xRequest, $this->xEmptyRequestHandler);
        // Call the request handler
        $xPsrResponse = $this->xPsrRequestHandler->handle($xRequest);

        $xJaxonResponse = jaxon()->getResponse();
        $this->assertNotNull($xJaxonResponse);
        $this->assertEquals(1, $xJaxonResponse->getCommandCount());
        $xCallableObject = jaxon()->di()->getCallableClassPlugin()->getCallable('Sample');
        $this->assertEquals('Sample', get_class($xCallableObject->getRegisteredObject()));

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

    /**
     * @throws RequestException
     * @throws SetupException
     */
    public function testAjaxUpload()
    {
        // Copy the file to the temp dir.
        @mkdir($this->tmpDir);
        @copy($this->sSrcWhite, $this->sPathWhite);

        registerUpload();
        jaxon()->setOption('core.upload.enabled', true);
        jaxon()->setOption('upload.default.dir', __DIR__ . '/../upload/dst');
        // Send a request to the registered class
        $xRequest = jaxon()->di()->g(ServerRequestCreator::class)->fromGlobals()
            ->withParsedBody([
                'jxncls' => 'Sample',
                'jxnmthd' => 'myMethod',
                'jxnargs' => [],
            ])->withUploadedFiles([
                'image' => new UploadedFile($this->sPathWhite, $this->sSizeWhite,
                    UPLOAD_ERR_OK, $this->sNameWhite, 'png'),
            ])->withMethod('POST');

        // Call the config middleware
        $this->xPsrConfigMiddleware->process($xRequest, $this->xEmptyRequestHandler);
        // Call the ajax middleware
        $xPsrResponse = $this->xPsrAjaxMiddleware->process($xRequest, $this->xEmptyRequestHandler);

        // Both responses must have the same content and content type
        $xJaxonResponse = jaxon()->getResponse();
        $this->assertEquals($xPsrResponse->getBody()->__toString(), $xJaxonResponse->getOutput());
        $this->assertEquals($xPsrResponse->getHeader('content-type')[0], $xJaxonResponse->getContentType());

        // Uploaded files
        $aFiles = jaxon()->upload()->files();
        $this->assertCount(1, $aFiles);
        $this->assertCount(1, $aFiles['image']);
        $xFile = $aFiles['image'][0];
        $this->assertEquals('white', $xFile->name());
        $this->assertEquals($this->sNameWhite, $xFile->filename());
        $this->assertEquals('png', $xFile->type());
        $this->assertEquals('png', $xFile->extension());
    }

    /**
     * @throws RequestException
     * @throws SetupException
     */
    public function testHttpUpload()
    {
        // Copy the file to the temp dir.
        @mkdir($this->tmpDir);
        @copy($this->sSrcWhite, $this->sPathWhite);

        registerUpload();
        jaxon()->setOption('core.upload.enabled', true);
        jaxon()->setOption('upload.default.dir', __DIR__ . '/../upload/dst');
        // Send a request to the registered class
        $xRequest = jaxon()->di()->g(ServerRequestCreator::class)->fromGlobals()
            ->withUploadedFiles([
                'image' => new UploadedFile($this->sPathWhite, $this->sSizeWhite,
                    UPLOAD_ERR_OK, $this->sNameWhite, 'png'),
            ])->withMethod('POST');

        // Call the config middleware
        $this->xPsrConfigMiddleware->process($xRequest, $this->xEmptyRequestHandler);
        // Call the ajax middleware
        $xPsrResponse = $this->xPsrAjaxMiddleware->process($xRequest, $this->xEmptyRequestHandler);

        // Both responses must have the same content and content type
        $xJaxonResponse = jaxon()->getResponse();
        $this->assertEquals($xPsrResponse->getBody()->__toString(), $xJaxonResponse->getOutput());
        $this->assertEquals($xPsrResponse->getHeader('content-type')[0], $xJaxonResponse->getContentType());

        $this->assertEquals(UploadResponse::class, get_class($xJaxonResponse));
        $this->assertNotEquals('', $xJaxonResponse->getUploadedFile());
        $this->assertEquals('', $xJaxonResponse->getErrorMessage());
        $this->assertEquals('text/html', $xJaxonResponse->getContentType());
        $this->assertStringContainsString('success', $xJaxonResponse->getOutput());

        // Return the file name for the next test
        return $xJaxonResponse->getUploadedFile();
    }

    /**
     * @depends testHttpUpload
     * @throws RequestException
     * @throws SetupException
     */
    public function testAjaxRequestAfterHttpUpload(string $sTempFile)
    {
        registerUpload();
        jaxon()->setOption('core.upload.enabled', true);

        jaxon()->setOption('upload.default.dir', __DIR__ . '/../upload/dst');
        // Ajax request following an HTTP upload
        $xRequest = jaxon()->di()->g(ServerRequestCreator::class)->fromGlobals()
            ->withParsedBody([
                'jxncls' => 'Sample',
                'jxnmthd' => 'myMethod',
                'jxnargs' => [],
                'jxnupl' => $sTempFile,
            ])->withMethod('POST');

        $this->assertNotEquals('', $sTempFile);
        // Call the config middleware
        $this->xPsrConfigMiddleware->process($xRequest, $this->xEmptyRequestHandler);
        // Call the ajax middleware
        $xPsrResponse = $this->xPsrAjaxMiddleware->process($xRequest, $this->xEmptyRequestHandler);

        // Both responses must have the same content and content type
        $xJaxonResponse = jaxon()->getResponse();
        $this->assertEquals($xPsrResponse->getBody()->__toString(), $xJaxonResponse->getOutput());
        $this->assertEquals($xPsrResponse->getHeader('content-type')[0], $xJaxonResponse->getContentType());

        // Uploaded files
        $aFiles = jaxon()->upload()->files();
        $this->assertCount(1, $aFiles);
        $this->assertCount(1, $aFiles['image']);
        $xFile = $aFiles['image'][0];
        $this->assertEquals('white', $xFile->name());
        $this->assertEquals($this->sNameWhite, $xFile->filename());
        $this->assertEquals('png', $xFile->type());
        $this->assertEquals('png', $xFile->extension());
    }
}
