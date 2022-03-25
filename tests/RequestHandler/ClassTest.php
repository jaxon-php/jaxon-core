<?php

namespace Jaxon\Tests\RequestHandler;

use Jaxon\Jaxon;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\TestCase;

use function jaxon;
use function rq;

class ClassTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->register(Jaxon::CALLABLE_CLASS, 'Sample', __DIR__ . '/../defs/sample.php');
    }

    /**
     * @throws SetupException
     */
    public function tearDown(): void
    {
        jaxon()->reset();
        parent::tearDown();
    }

    public function testGetRequestToJaxonClass()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function() {
            $xRequestFactory = new Psr17Factory();
            $xRequestCreator = new ServerRequestCreator(
                $xRequestFactory, // ServerRequestFactory
                $xRequestFactory, // UriFactory
                $xRequestFactory, // UploadedFileFactory
                $xRequestFactory  // StreamFactory
            );
            return $xRequestCreator->fromGlobals()->withQueryParams([
                'jxncls' => 'Sample',
                'jxnmthd' => 'myMethod',
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertFalse(jaxon()->di()->getCallableFunctionPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->assertTrue(jaxon()->di()->getCallableClassPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->assertNull(jaxon()->di()->getCallableFunctionPlugin()->getTarget());
    }

    public function testPostRequestToJaxonClass()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function() {
            $xRequestFactory = new Psr17Factory();
            $xRequestCreator = new ServerRequestCreator(
                $xRequestFactory, // ServerRequestFactory
                $xRequestFactory, // UriFactory
                $xRequestFactory, // UploadedFileFactory
                $xRequestFactory  // StreamFactory
            );
            return $xRequestCreator->fromGlobals()->withParsedBody([
                'jxncls' => 'Sample',
                'jxnmthd' => 'myMethod',
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertFalse(jaxon()->di()->getCallableFunctionPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->assertTrue(jaxon()->di()->getCallableClassPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $xTarget = jaxon()->di()->getCallableClassPlugin()->getTarget();
        $this->assertNotNull($xTarget);
    }

    /**
     * @throws SetupException
     * @throws RequestException
     */
    public function testRequestToJaxonClass()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function() {
            $xRequestFactory = new Psr17Factory();
            $xRequestCreator = new ServerRequestCreator(
                $xRequestFactory, // ServerRequestFactory
                $xRequestFactory, // UriFactory
                $xRequestFactory, // UploadedFileFactory
                $xRequestFactory  // StreamFactory
            );
            return $xRequestCreator->fromGlobals()->withParsedBody([
                'jxncls' => 'Sample',
                'jxnmthd' => 'myMethod',
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getCallableClassPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->assertTrue(jaxon()->di()->getCallableClassPlugin()->processRequest());
    }
}
