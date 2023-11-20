<?php

namespace Jaxon\Tests\TestRegistrationApp;

require_once __DIR__ . '/../../vendor/jaxon-php/jaxon-dialogs/src/start.php';
require_once __DIR__ . '/../src/classes.php';

use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Code\MinifierInterface;
use Jaxon\Utils\Http\UriException;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\TestCase;

use function Jaxon\jaxon;
use function Jaxon\Dialogs\registerDialogLibraries;

class RegistrationTest extends TestCase
{
    private $jsDir = '';

    public function setUp(): void
    {
        $this->jsDir = realpath(__DIR__ . '/../src/js');
        registerDialogLibraries();
        jaxon()->app()->setup(__DIR__ . '/../config/app/app.php');
        jaxon()->app()->asset(true, true, 'http://example.test/js', $this->jsDir);
    }

    /**
     * @throws SetupException
     */
    public function tearDown(): void
    {
        // Delete the generated js files
        $sHash = jaxon()->di()->getCodeGenerator()->getHash();
        @unlink($this->jsDir . "/$sHash.js");
        @unlink($this->jsDir . "/$sHash.min.js");

        jaxon()->reset();
        parent::tearDown();
    }

    /**
     * @throws UriException
     */
    public function testScriptExportMinified()
    {
        jaxon()->setOption('js.app.minify', true);
        $sScript = jaxon()->getScript();
        // Check that the return value is a file URI, and not js code.
        $this->assertStringNotContainsString('SamplePackageClass = {}', $sScript);
        $this->assertStringContainsString('http://example.test/js', $sScript);
        $this->assertStringContainsString('.min.js', $sScript);
    }

    /**
     * @throws UriException
     */
    public function testScriptExportNotMinified()
    {
        jaxon()->setOption('js.app.minify', false);
        $sScript = jaxon()->getScript();
        // Check that the return value is a file URI, and not js code.
        $this->assertStringNotContainsString('SamplePackageClass = {}', $sScript);
        $this->assertStringContainsString('http://example.test/js', $sScript);
        $this->assertStringNotContainsString('.min.js', $sScript);
        $this->assertStringContainsString('.js', $sScript);
    }

    /**
     * @throws UriException
     */
    public function testScriptErrorMinifier()
    {
        // Register a minifier that always fails.
        jaxon()->di()->set(MinifierInterface::class, function() {
            return new class implements MinifierInterface {
                public function minify(string $sJsFile, string $sMinFile): bool
                {
                    return false;
                }
            };
        });
        // The js file must be generated but not minified.
        jaxon()->setOption('js.app.minify', true);
        $sScript = jaxon()->getScript();
        // Check that the return value is a file URI, and not js code.
        $this->assertStringNotContainsString('SamplePackageClass = {}', $sScript);
        $this->assertStringContainsString('http://example.test/js', $sScript);
        $this->assertStringNotContainsString('.min.js', $sScript);
        $this->assertStringContainsString('.js', $sScript);
    }

    /**
     * @throws UriException
     */
    public function testScriptExportErrorIncorrectDir()
    {
        // Change the js dir
        jaxon()->setOption('js.app.dir', __DIR__ . '/../src/script'); // This dir must not exist.
        $this->assertStringContainsString('SamplePackageClass = {}', jaxon()->script());
    }

    /**
     * @throws UriException
     */
    public function testScriptExportErrorIncorrectFile()
    {
        // Change the js dir
        jaxon()->setOption('js.app.file', 'js/app'); // This dir must not exist.
        $this->assertStringContainsString('SamplePackageClass = {}', jaxon()->script());
    }

    public function testSetupIncorrectFile()
    {
        $this->expectException(SetupException::class);
        jaxon()->app()->setup(__DIR__ . '/../config/app/not-found.php');
    }

    public function testSetupIncorrectConfig()
    {
        $this->expectException(SetupException::class);
        jaxon()->app()->setup(__DIR__ . '/../config/app/app-error.php');
    }

    /**
     * @throws RequestException
     */
    public function testJaxonClassAnnotations()
    {
        jaxon()->setOption('core.annotations.on', true);
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'Jaxon.NsTests.DirB.ClassB',
                'jxnmthd' => 'methodBa',
                'jxnargs' => [],
            ])->withMethod('POST');
        });

        $this->assertTrue(jaxon()->app()->canProcessRequest());
        $this->assertNotNull(jaxon()->di()->getCallableClassPlugin()->processRequest());
    }

    /**
     * @throws RequestException
     */
    public function testRequestToJaxonClass()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withParsedBody([
                'jxncls' => 'Jaxon.NsTests.DirB.ClassB',
                'jxnmthd' => 'methodBb',
                'jxnargs' => [],
            ])->withMethod('POST');
        });

        $this->assertTrue(jaxon()->app()->canProcessRequest());
        jaxon()->app()->processRequest();
        $this->expectException(RequestException::class);
        jaxon()->app()->httpResponse();
    }
}
