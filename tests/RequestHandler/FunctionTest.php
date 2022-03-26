<?php

namespace Jaxon\Tests\RequestHandler;

use Jaxon\Jaxon;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\TestCase;

class FunctionTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'my_first_function',
            __DIR__ . '/../defs/first.php');
        // Register a class method as a function
        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'myMethod', [
            'alias' => 'my_third_function',
            'class' => 'Sample',
            'include' => __DIR__ . '/../defs/sample.php',
        ]);
    }

    /**
     * @throws SetupException
     */
    public function tearDown(): void
    {
        jaxon()->reset();
        parent::tearDown();
    }

    public function testGetRequestToJaxonFunction()
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
            // Add GET parameters to the request
            return $xRequestCreator->fromGlobals()->withQueryParams([
                'jxnfun' => 'my_first_function',
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getCallableFunctionPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->assertFalse(jaxon()->di()->getCallableClassPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->assertNull(jaxon()->di()->getCallableClassPlugin()->getTarget());
    }

    public function testPostRequestToJaxonFunction()
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
            // Add GET parameters to the request
            return $xRequestCreator->fromGlobals()->withParsedBody([
                'jxnfun' => 'my_first_function',
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getRequestHandler()->canProcessRequest());
        $this->assertTrue(jaxon()->di()->getCallableFunctionPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->assertFalse(jaxon()->di()->getCallableClassPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $xTarget = jaxon()->di()->getCallableFunctionPlugin()->getTarget();
        $this->assertNotNull($xTarget);
    }

    /**
     * @throws RequestException
     */
    public function testRequestToJaxonFunction()
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
            // Add GET parameters to the request
            return $xRequestCreator->fromGlobals()->withParsedBody([
                'jxnfun' => 'my_first_function',
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getCallableFunctionPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->assertTrue(jaxon()->di()->getCallableFunctionPlugin()->processRequest());
    }

    /**
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
            // Add GET parameters to the request
            return $xRequestCreator->fromGlobals()->withParsedBody([
                'jxnfun' => 'my_third_function',
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getCallableFunctionPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->assertTrue(jaxon()->di()->getCallableFunctionPlugin()->processRequest());
    }

    /**
     * @throws RequestException
     */
    public function testRequestToJaxonFunctionIncorrectName()
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
            // Add GET parameters to the request
            return $xRequestCreator->fromGlobals()->withParsedBody([
                'jxnfun' => 'my function', // A space in the function name
                'jxnargs' => [],
            ]);
        });

        $this->assertTrue(jaxon()->di()->getCallableFunctionPlugin()->canProcessRequest(jaxon()->di()->getRequest()));
        $this->expectException(RequestException::class);
        jaxon()->di()->getCallableFunctionPlugin()->processRequest();
    }
}
