<?php

namespace Jaxon\Tests\TestRequestHandler;

require_once(__DIR__ . '/../../vendor/jaxon-php/jaxon-upload/src/start.php');

use Jaxon\Jaxon;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Jaxon\Request\Upload\NameGeneratorInterface;
use Jaxon\Upload\UploadResponse;
use Nyholm\Psr7\UploadedFile;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\TestCase;

use function Jaxon\jaxon;
use function filesize;
use function Jaxon\Upload\registerUpload;

class UploadTest extends TestCase
{
    /**
     * @var string
     */
    protected $sNameWhite;

    /**
     * @var string
     */
    protected $sPathWhite;

    /**
     * @var int
     */
    protected $sSizeWhite;

    /**
     * @var string
     */
    protected $sNameBlue;

    /**
     * @var string
     */
    protected $sPathBlue;

    /**
     * @var int
     */
    protected $sSizeBlue;

    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        registerUpload();
        jaxon()->setOption('core.upload.enabled', true);

        jaxon()->setOption('core.response.send', false);
        $tmpDir = __DIR__ . '/../upload/tmp';
        @mkdir($tmpDir);

        $sSrcWhite = __DIR__ . '/../upload/src/white.png';
        $this->sNameWhite = 'white.png';
        $this->sPathWhite = "$tmpDir/{$this->sNameWhite}";
        $this->sSizeWhite = filesize($sSrcWhite);
        // Copy the file to the temp dir.
        @copy($sSrcWhite, $this->sPathWhite);

        $sSrcBlue = __DIR__ . '/../upload/src/blue.png';
        $this->sNameBlue = 'blue.png';
        $this->sPathBlue = "$tmpDir/{$this->sNameBlue}";
        $this->sSizeBlue = filesize($sSrcBlue);
        // Copy the file to the temp dir.
        @copy($sSrcBlue, $this->sPathBlue);

        jaxon()->register(Jaxon::CALLABLE_CLASS, 'Sample', __DIR__ . '/../src/sample.php');
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
    public function testAjaxUpload()
    {
        jaxon()->setOption('upload.default.dir', __DIR__ . '/../upload/dst');
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'Sample',
                'jxnmthd' => 'myMethod',
                'jxnargs' => [],
            ])->withUploadedFiles([
                'image' => new UploadedFile($this->sPathWhite, $this->sSizeWhite,
                    UPLOAD_ERR_OK, $this->sNameWhite, 'png'),
            ])->withMethod('POST');
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getUploadHandler()->canProcessRequest(jaxon()->di()->getRequest()));
        jaxon()->processRequest();

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
     */
    public function testAjaxUploadMultipleFiles()
    {
        jaxon()->setOption('upload.default.dir', __DIR__ . '/../upload/dst');
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'Sample',
                'jxnmthd' => 'myMethod',
                'jxnargs' => [],
            ])->withUploadedFiles([
                'image' => [
                    new UploadedFile($this->sPathWhite, $this->sSizeWhite,
                        UPLOAD_ERR_OK, $this->sNameWhite, 'png'),
                    new UploadedFile($this->sPathBlue, $this->sSizeBlue,
                        UPLOAD_ERR_OK, $this->sNameBlue, 'png'),
                ],
            ])->withMethod('POST');
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getUploadHandler()->canProcessRequest(jaxon()->di()->getRequest()));
        jaxon()->processRequest();

        // Uploaded files
        $aFiles = jaxon()->upload()->files();
        $this->assertCount(1, $aFiles);
        $this->assertCount(2, $aFiles['image']);
        $xFile = $aFiles['image'][0];
        $this->assertEquals('white', $xFile->name());
        $this->assertEquals($this->sNameWhite, $xFile->filename());
        $this->assertEquals('png', $xFile->type());
        $this->assertEquals('png', $xFile->extension());
        $xFile = $aFiles['image'][1];
        $this->assertEquals('blue', $xFile->name());
        $this->assertEquals($this->sNameBlue, $xFile->filename());
        $this->assertEquals('png', $xFile->type());
        $this->assertEquals('png', $xFile->extension());
    }

    /**
     * @throws RequestException
     */
    public function testAjaxUploadMultipleNames()
    {
        jaxon()->setOption('upload.default.dir', __DIR__ . '/../upload/dst');
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'Sample',
                'jxnmthd' => 'myMethod',
                'jxnargs' => [],
            ])->withUploadedFiles([
                'white' => [
                    new UploadedFile($this->sPathWhite, $this->sSizeWhite,
                        UPLOAD_ERR_OK, $this->sNameWhite, 'png'),
                ],
                'blue' => [
                    new UploadedFile($this->sPathBlue, $this->sSizeBlue,
                        UPLOAD_ERR_OK, $this->sNameBlue, 'png'),
                ],
            ])->withMethod('POST');
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getUploadHandler()->canProcessRequest(jaxon()->di()->getRequest()));
        jaxon()->processRequest();

        // Uploaded files
        $aFiles = jaxon()->upload()->files();
        $this->assertCount(2, $aFiles);
        $this->assertCount(1, $aFiles['white']);
        $this->assertCount(1, $aFiles['blue']);
        $xFile = $aFiles['white'][0];
        $this->assertEquals('white', $xFile->name());
        $this->assertEquals($this->sNameWhite, $xFile->filename());
        $this->assertEquals('png', $xFile->type());
        $this->assertEquals('png', $xFile->extension());
        $xFile = $aFiles['blue'][0];
        $this->assertEquals('blue', $xFile->name());
        $this->assertEquals($this->sNameBlue, $xFile->filename());
        $this->assertEquals('png', $xFile->type());
        $this->assertEquals('png', $xFile->extension());
    }

    /**
     * @throws RequestException
     */
    public function testAjaxUploadNameSanitizer()
    {
        jaxon()->setOption('upload.default.dir', __DIR__ . '/../upload/dst');
        jaxon()->upload()->sanitizer(function($sFilename, $sVarName) {
            return $sVarName === 'image' ? 'img_' . $sFilename : $sFilename;
        });
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'Sample',
                'jxnmthd' => 'myMethod',
                'jxnargs' => [],
            ])->withUploadedFiles([
                'image' => new UploadedFile($this->sPathWhite, $this->sSizeWhite,
                    UPLOAD_ERR_OK, $this->sNameWhite, 'png'),
            ])->withMethod('POST');
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getUploadHandler()->canProcessRequest(jaxon()->di()->getRequest()));
        jaxon()->processRequest();

        // Uploaded files
        $aFiles = jaxon()->upload()->files();
        $this->assertCount(1, $aFiles);
        $this->assertCount(1, $aFiles['image']);
        $xFile = $aFiles['image'][0];
        $this->assertEquals('img_white', $xFile->name());
        $this->assertEquals('png', $xFile->type());
        $this->assertEquals('png', $xFile->extension());
        $this->assertEquals($this->sNameWhite, $xFile->filename());
    }

    /**
     * @throws RequestException
     */
    public function testHttpUpload()
    {
        jaxon()->setOption('upload.default.dir', __DIR__ . '/../upload/dst');
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withUploadedFiles([
                'image' => new UploadedFile($this->sPathWhite, $this->sSizeWhite,
                    UPLOAD_ERR_OK, $this->sNameWhite, 'png'),
            ])->withMethod('POST');
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getUploadHandler()->canProcessRequest(jaxon()->di()->getRequest()));
        jaxon()->di()->getResponseManager()->debug('Testing the HTTP upload!!');
        jaxon()->processRequest();

        $xResponse = jaxon()->getResponse();
        $this->assertEquals(UploadResponse::class, get_class($xResponse));
        $this->assertNotEquals('', $xResponse->getUploadedFile());
        $this->assertEquals('', $xResponse->getErrorMessage());
        $this->assertEquals('text/html', $xResponse->getContentType());
        $this->assertStringContainsString('success', $xResponse->getOutput());
        $this->assertStringContainsString('Testing the HTTP upload!!', $xResponse->getOutput());

        // Return the file name for the next test
        return $xResponse->getUploadedFile();
    }

    /**
     * @depends testHttpUpload
     * @throws RequestException
     */
    public function testAjaxRequestAfterHttpUpload(string $sTempFile)
    {
        jaxon()->setOption('upload.default.dir', __DIR__ . '/../upload/dst');
        // Ajax request following an HTTP upload
        jaxon()->di()->set(ServerRequestInterface::class, function($c) use($sTempFile) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'Sample',
                'jxnmthd' => 'myMethod',
                'jxnargs' => [],
                'jxnupl' => $sTempFile,
            ])->withMethod('POST');
        });

        $this->assertNotEquals('', $sTempFile);
        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getUploadHandler()->canProcessRequest(jaxon()->di()->getRequest()));
        jaxon()->processRequest();

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
     */
    public function testUploadFileTypeValidationOk()
    {
        jaxon()->setOption('upload.default.dir', __DIR__ . '/../upload/dst');
        jaxon()->setOption('upload.default.types', ['png']);
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'Sample',
                'jxnmthd' => 'myMethod',
                'jxnargs' => [],
            ])->withUploadedFiles([
                'image' => new UploadedFile($this->sPathWhite, $this->sSizeWhite,
                    UPLOAD_ERR_OK, $this->sNameWhite, 'png'),
            ])->withMethod('POST');
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getUploadHandler()->canProcessRequest(jaxon()->di()->getRequest()));
        jaxon()->processRequest();

        // Return the file name for the next test
        $aFiles = jaxon()->upload()->files();
        $this->assertCount(1, $aFiles);
        $this->assertCount(1, $aFiles['image']);
        $xFile = $aFiles['image'][0];
        $this->assertEquals('white', $xFile->name());
        $this->assertEquals($this->sNameWhite, $xFile->filename());
    }

    /**
     * @throws RequestException
     */
    public function testUploadFileTypeValidationError()
    {
        jaxon()->setOption('upload.default.dir', __DIR__ . '/../upload/dst');
        jaxon()->setOption('upload.default.types', ['jpg']);
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'Sample',
                'jxnmthd' => 'myMethod',
                'jxnargs' => [],
            ])->withUploadedFiles([
                'image' => new UploadedFile($this->sPathWhite, $this->sSizeWhite,
                    UPLOAD_ERR_OK, $this->sNameWhite, 'png'),
            ])->withMethod('POST');
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getUploadHandler()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->expectException(RequestException::class);
        jaxon()->processRequest();
    }

    /**
     * @throws RequestException
     */
    public function testUploadFileExtensionValidationOk()
    {
        jaxon()->setOption('upload.default.dir', __DIR__ . '/../upload/dst');
        jaxon()->setOption('upload.default.extensions', ['png']);
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'Sample',
                'jxnmthd' => 'myMethod',
                'jxnargs' => [],
            ])->withUploadedFiles([
                'image' => new UploadedFile($this->sPathWhite, $this->sSizeWhite,
                    UPLOAD_ERR_OK, $this->sNameWhite, 'png'),
            ])->withMethod('POST');
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getUploadHandler()->canProcessRequest(jaxon()->di()->getRequest()));
        jaxon()->processRequest();

        // Return the file name for the next test
        $aFiles = jaxon()->upload()->files();
        $this->assertCount(1, $aFiles);
        $this->assertCount(1, $aFiles['image']);
        $xFile = $aFiles['image'][0];
        $this->assertEquals('white', $xFile->name());
        $this->assertEquals($this->sNameWhite, $xFile->filename());
    }

    /**
     * @throws RequestException
     */
    public function testUploadFileExtensionValidationError()
    {
        jaxon()->setOption('upload.default.dir', __DIR__ . '/../upload/dst');
        jaxon()->setOption('upload.default.extensions', ['jpg']);
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'Sample',
                'jxnmthd' => 'myMethod',
                'jxnargs' => [],
            ])->withUploadedFiles([
                'image' => new UploadedFile($this->sPathWhite, $this->sSizeWhite,
                    UPLOAD_ERR_OK, $this->sNameWhite, 'png'),
            ])->withMethod('POST');
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getUploadHandler()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->expectException(RequestException::class);
        jaxon()->processRequest();
    }

    /**
     * @throws RequestException
     */
    public function testUploadFileMaxSizeValidationOk()
    {
        jaxon()->setOption('upload.default.dir', __DIR__ . '/../upload/dst');
        jaxon()->setOption('upload.default.max-size', 30000);
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'Sample',
                'jxnmthd' => 'myMethod',
                'jxnargs' => [],
            ])->withUploadedFiles([
                'image' => new UploadedFile($this->sPathWhite, $this->sSizeWhite,
                    UPLOAD_ERR_OK, $this->sNameWhite, 'png'),
            ])->withMethod('POST');
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getUploadHandler()->canProcessRequest(jaxon()->di()->getRequest()));
        jaxon()->processRequest();

        // Return the file name for the next test
        $aFiles = jaxon()->upload()->files();
        $this->assertCount(1, $aFiles);
        $this->assertCount(1, $aFiles['image']);
        $xFile = $aFiles['image'][0];
        $this->assertEquals('white', $xFile->name());
        $this->assertEquals($this->sNameWhite, $xFile->filename());
    }

    /**
     * @throws RequestException
     */
    public function testUploadFileMaxSizeValidationError()
    {
        jaxon()->setOption('upload.default.dir', __DIR__ . '/../upload/dst');
        jaxon()->setOption('upload.default.max-size', 25000);
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'Sample',
                'jxnmthd' => 'myMethod',
                'jxnargs' => [],
            ])->withUploadedFiles([
                'image' => new UploadedFile($this->sPathWhite, $this->sSizeWhite,
                    UPLOAD_ERR_OK, $this->sNameWhite, 'png'),
            ])->withMethod('POST');
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getUploadHandler()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->expectException(RequestException::class);
        jaxon()->processRequest();
    }

    /**
     * @throws RequestException
     */
    public function testUploadFileMinSizeValidationOk()
    {
        jaxon()->setOption('upload.default.dir', __DIR__ . '/../upload/dst');
        jaxon()->setOption('upload.default.min-size', 25000);
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'Sample',
                'jxnmthd' => 'myMethod',
                'jxnargs' => [],
            ])->withUploadedFiles([
                'image' => new UploadedFile($this->sPathWhite, $this->sSizeWhite,
                    UPLOAD_ERR_OK, $this->sNameWhite, 'png'),
            ])->withMethod('POST');
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getUploadHandler()->canProcessRequest(jaxon()->di()->getRequest()));
        jaxon()->processRequest();

        // Return the file name for the next test
        $aFiles = jaxon()->upload()->files();
        $this->assertCount(1, $aFiles);
        $this->assertCount(1, $aFiles['image']);
        $xFile = $aFiles['image'][0];
        $this->assertEquals('white', $xFile->name());
        $this->assertEquals($this->sNameWhite, $xFile->filename());
    }

    /**
     * @throws RequestException
     */
    public function testUploadFileMinSizeValidationError()
    {
        jaxon()->setOption('upload.default.dir', __DIR__ . '/../upload/dst');
        jaxon()->setOption('upload.default.min-size', 30000);
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'Sample',
                'jxnmthd' => 'myMethod',
                'jxnargs' => [],
            ])->withUploadedFiles([
                'image' => new UploadedFile($this->sPathWhite, $this->sSizeWhite,
                    UPLOAD_ERR_OK, $this->sNameWhite, 'png'),
            ])->withMethod('POST');
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getUploadHandler()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->expectException(RequestException::class);
        jaxon()->processRequest();
    }

    /**
     * @throws RequestException
     */
    public function testHttpUploadFailure()
    {
        jaxon()->setOption('upload.default.dir', __DIR__ . '/../upload/dst');
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withUploadedFiles([
                'image' => new UploadedFile($this->sPathWhite, $this->sSizeWhite,
                    UPLOAD_ERR_FORM_SIZE, $this->sNameWhite, 'png'),
            ])->withMethod('POST');
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        jaxon()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(UploadResponse::class, get_class($xResponse));
        $this->assertEquals('', $xResponse->getUploadedFile());
        $this->assertNotEquals('', $xResponse->getErrorMessage());
    }

    /**
     * @throws RequestException
     */
    public function testHttpUploadDirAccessError()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withUploadedFiles([
                'image' => new UploadedFile($this->sPathWhite, $this->sSizeWhite,
                    UPLOAD_ERR_OK, $this->sNameWhite, 'png'),
            ])->withMethod('POST');
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        jaxon()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(UploadResponse::class, get_class($xResponse));
        $this->assertEquals('', $xResponse->getUploadedFile());
        $this->assertNotEquals('', $xResponse->getErrorMessage());
    }

    /**
     * @throws RequestException
     */
    public function testHttpUploadErrorNoDir()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withUploadedFiles([
                'image' => new UploadedFile($this->sPathWhite, $this->sSizeWhite,
                    UPLOAD_ERR_OK, $this->sNameWhite, 'png'),
            ])->withMethod('POST');
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        jaxon()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(UploadResponse::class, get_class($xResponse));
        $this->assertEquals('', $xResponse->getUploadedFile());
        $this->assertNotEquals('', $xResponse->getErrorMessage());
    }

    /**
     * @throws RequestException
     */
    /*public function testHttpUploadErrorDirNotFound()
    {
        jaxon()->setOption('upload.default.dir', __DIR__ . '/../upload/not-found');
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withUploadedFiles([
                'image' => new UploadedFile($this->sPathWhite, $this->sSizeWhite,
                    UPLOAD_ERR_OK, $this->sNameWhite, 'png'),
            ])->withMethod('POST');
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        jaxon()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(UploadResponse::class, get_class($xResponse));
        $this->assertEquals('', $xResponse->getUploadedFile());
        $this->assertNotEquals('', $xResponse->getErrorMessage());
    }*/

    /**
     * @throws RequestException
     */
    /*public function testHttpUploadErrorNoTmpDir()
    {
        jaxon()->setOption('upload.files.image.dir', __DIR__ . '/../upload/dst');
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withUploadedFiles([
                'image' => new UploadedFile($this->sPathWhite, $this->sSizeWhite,
                    UPLOAD_ERR_OK, $this->sNameWhite, 'png'),
            ])->withMethod('POST');
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        jaxon()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(UploadResponse::class, get_class($xResponse));
        $this->assertEquals('', $xResponse->getUploadedFile());
        $this->assertNotEquals('', $xResponse->getErrorMessage());
    }*/

    /**
     * @throws RequestException
     */
    /*public function testHttpUploadErrorDirCreation()
    {
        jaxon()->setOption('upload.default.dir', __DIR__ . '/../upload/dst');
        // Upload file and dir name generator
        jaxon()->di()->set(NameGeneratorInterface::class, function() {
            return new class implements NameGeneratorInterface
            {
                public function random(int $nLength): string
                {
                    // A file or dir with this name cannot be created.
                    return "test/tos";
                }
            };
        });
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withUploadedFiles([
                'image' => new UploadedFile($this->sPathWhite, $this->sSizeWhite,
                    UPLOAD_ERR_OK, $this->sNameWhite, 'png'),
            ])->withMethod('POST');
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        jaxon()->processRequest();
        $xResponse = jaxon()->getResponse();
        $this->assertEquals(UploadResponse::class, get_class($xResponse));
        $this->assertEquals('', $xResponse->getUploadedFile());
        $this->assertNotEquals('', $xResponse->getErrorMessage());
    }*/

    /**
     * @throws RequestException
     */
    public function testRequestWithNoPluginNoUpload()
    {
        jaxon()->setOption('core.upload.enabled', false);
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxnwho' => 'Nobody',
                'jxnargs' => [],
            ])->withMethod('POST');
        });

        $this->assertFalse(jaxon()->di()->getRequestHandler()->canProcessRequest());
    }

    /**
     * @throws RequestException
     */
    public function testAjaxRequestAfterHttpUploadIncorrectFile()
    {
        jaxon()->setOption('upload.default.dir', __DIR__ . '/../upload/dst');
        // Ajax request following an HTTP upload
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'Sample',
                'jxnmthd' => 'myMethod',
                'jxnargs' => [],
                'jxnupl' => 'not valid',
            ]);
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getUploadHandler()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->expectException(RequestException::class);
        jaxon()->processRequest();
    }

    /**
     * @throws RequestException
     */
    public function testAjaxRequestAfterHttpUploadUnknownFile()
    {
        jaxon()->setOption('upload.default.dir', __DIR__ . '/../upload/dst');
        // Ajax request following an HTTP upload
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'Sample',
                'jxnmthd' => 'myMethod',
                'jxnargs' => [],
                'jxnupl' => 'unknown',
            ]);
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getUploadHandler()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->expectException(RequestException::class);
        jaxon()->processRequest();
    }
}
