<?php

namespace Jaxon\Tests\TestRequestHandler;

use Jaxon\Exception\RequestException;
use Jaxon\Jaxon;
use Jaxon\Exception\SetupException;
use Nyholm\Psr7Server\ServerRequestCreator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class ParameterTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.response.send', false);
        jaxon()->register(Jaxon::CALLABLE_CLASS, 'Sample', dirname(__DIR__) . '/src/sample.php');
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
    public function testRequestWithNoPlugin()
    {
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'who' => 'Nobody',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));

        $this->assertFalse(jaxon()->di()->getRequestHandler()->canProcessRequest());
        // Process the request and get the response
        jaxon()->processRequest();

        $xResponse = jaxon()->getResponse();
        $this->assertEquals(0, $xResponse->getCommandCount());
    }

    public function testGetParameterProcessing()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withQueryParams([
                    'jxncall' => json_encode([
                        'args' => [
                            'string' => 'string',
                            'int' => 15,
                            'true' => true,
                            'false' => false,
                            'null' => null,
                            'empty' => '',
                            'array' => ['with', 'multiple', 'values'],
                        ],
                    ]),
                ])
                ->withMethod('GET'));

        $aParameter = jaxon()->di()->getRequestArguments();

        $this->assertIsArray($aParameter);

        $this->assertIsString($aParameter['string']);
        $this->assertEquals('string', $aParameter['string']);

        $this->assertIsInt($aParameter['int']);
        $this->assertEquals(15, $aParameter['int']);

        $this->assertIsBool($aParameter['true']);
        $this->assertTrue($aParameter['true']);
        $this->assertIsBool($aParameter['false']);
        $this->assertFalse($aParameter['false']);

        $this->assertNull($aParameter['null']);
        $this->assertEquals('', $aParameter['empty']);

        $this->assertIsArray($aParameter['array']);
        $this->assertCount(3, $aParameter['array']);
    }

    /**
     * @throws RequestException
     */
    public function testPostParameterProcessing()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'args' => [
                            'string' => 'string',
                            'int' => 15,
                            'true' => 58,
                            'false' => 'false',
                            'null' => null,
                            'empty' => '',
                            'array' => ['with', 'multiple', 'values'],
                        ],
                    ]),
                ])
                ->withMethod('POST'));

        $aParameter = jaxon()->di()->getRequestArguments();

        $this->assertIsArray($aParameter);

        $this->assertIsString($aParameter['string']);
        $this->assertEquals('string', $aParameter['string']);

        $this->assertIsInt($aParameter['int']);
        $this->assertEquals(15, $aParameter['int']);

        $this->assertIsInt($aParameter['true']);
        // $this->assertTrue($aParameter['true']);
        $this->assertIsString($aParameter['false']);
        // $this->assertFalse($aParameter['false']);

        $this->assertNull($aParameter['null']);
        $this->assertEquals('', $aParameter['empty']);

        $this->assertIsArray($aParameter['array']);
        $this->assertCount(3, $aParameter['array']);
    }

    /**
     * @throws RequestException
     */
    public function testUtf8ParameterProcessing()
    {
        jaxon()->setOption('core.decode_utf8', true);
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'args' => [
                            'string' => 'string',
                            'int' => 15,
                            'true' => 58,
                            'false' => 'false',
                            'null' => null,
                            'empty' => '',
                            'array' => ['with', 'multiple', 'values'],
                        ],
                    ]),
                ])
                ->withMethod('POST'));

        $aParameter = jaxon()->di()->getRequestArguments();

        $this->assertIsArray($aParameter);

        $this->assertIsString($aParameter['string']);
        $this->assertEquals('string', $aParameter['string']);

        $this->assertIsInt($aParameter['int']);
        $this->assertEquals(15, $aParameter['int']);

        $this->assertIsInt($aParameter['true']);
        // $this->assertTrue($aParameter['true']);
        $this->assertIsString($aParameter['false']);
        // $this->assertFalse($aParameter['false']);

        $this->assertNull($aParameter['null']);
        $this->assertEquals('', $aParameter['empty']);

        $this->assertIsArray($aParameter['array']);
        $this->assertCount(3, $aParameter['array']);
    }

    /**
     * @throws RequestException
     */
    public function testUrlEncodedParameterProcessing1()
    {
        // This cannot be changed using the request factory.
        unset($_SERVER['HTTP_CONTENT_TYPE']);
        $_SERVER['CONTENT_TYPE'] = 'multipart/form-data';
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'args' => [
                            'string' => 'string',
                            'int' => 15,
                            'true' => 58,
                            'false' => 'false',
                            'null' => null,
                            'empty' => '',
                            'array' => ['with', 'multiple', 'values'],
                        ],
                    ]),
                ])
                ->withMethod('POST'));

        $aParameter = jaxon()->di()->getRequestArguments();

        $this->assertIsArray($aParameter);

        $this->assertIsString($aParameter['string']);
        $this->assertEquals('string', $aParameter['string']);

        $this->assertIsInt($aParameter['int']);
        $this->assertEquals(15, $aParameter['int']);

        $this->assertIsInt($aParameter['true']);
        // $this->assertTrue($aParameter['true']);
        $this->assertIsString($aParameter['false']);
        // $this->assertFalse($aParameter['false']);

        $this->assertNull($aParameter['null']);
        $this->assertEquals('', $aParameter['empty']);

        $this->assertIsArray($aParameter['array']);
        $this->assertCount(3, $aParameter['array']);
    }

    /**
     * @throws RequestException
     */
    public function testUrlEncodedParameterProcessing2()
    {
        // This cannot be changed using the request factory.
        unset($_SERVER['CONTENT_TYPE']);
        $_SERVER['HTTP_CONTENT_TYPE'] = 'multipart/form-data';
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'args' => [
                            'string' => 'string',
                            'int' => 15,
                            'true' => 58,
                            'false' => 'false',
                            'null' => null,
                            'empty' => '',
                            'array' => ['with', 'multiple', 'values'],
                        ],
                    ]),
                ])
                ->withMethod('POST'));

        $aParameter = jaxon()->di()->getRequestArguments();

        $this->assertIsArray($aParameter);

        $this->assertIsString($aParameter['string']);
        $this->assertEquals('string', $aParameter['string']);

        $this->assertIsInt($aParameter['int']);
        $this->assertEquals(15, $aParameter['int']);

        $this->assertIsInt($aParameter['true']);
        // $this->assertTrue($aParameter['true']);
        $this->assertIsString($aParameter['false']);
        // $this->assertFalse($aParameter['false']);

        $this->assertNull($aParameter['null']);
        $this->assertEquals('', $aParameter['empty']);

        $this->assertIsArray($aParameter['array']);
        $this->assertCount(3, $aParameter['array']);
    }
}
