<?php

namespace Jaxon\Tests\TestRegistrationApp;

require_once dirname(__DIR__) . '/src/classes.php';

use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Code\MinifierInterface;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\TestCase;

use function Jaxon\Dialogs\_register as register_dialogs;

/**
 * Tests with the assets export options in the "lib" section of the config file.
 */
class ExportAssetsInAppTest extends TestCase
{
    private $jsDir = '';

    public function setUp(): void
    {
        $this->jsDir = realpath(dirname(__DIR__) . '/src/js');
        register_dialogs();
        jaxon()->app()->setup(dirname(__DIR__) . '/config/app/assets.app.php');
        // The asset() method sets the options in the "app" section of the config.
        jaxon()->config()->asset(true, true, 'http://example.test/js', $this->jsDir);
    }

    /**
     * @throws SetupException
     */
    public function tearDown(): void
    {
        // Delete the generated js files
        $sHash = jaxon()->di()->getCodeGenerator()->getHash();
        @unlink("{$this->jsDir}/$sHash.js");
        @unlink("{$this->jsDir}/$sHash.min.js");
        @unlink("{$this->jsDir}/assets.js");
        @unlink("{$this->jsDir}/assets.min.js");

        jaxon()->reset();
        parent::tearDown();
    }

    public function testExportFileContent()
    {
        jaxon()->setAppOptions(['export' => false, 'minify' => false], 'assets.js');
        $sScript = jaxon()->getScript();
        // file_put_contents(dirname(__DIR__) . '/src/js/assets.app.html', $sScript);
        $this->assertEquals(file_get_contents(dirname(__DIR__) . '/src/js/assets.app.html'), $sScript);
    }

    public function testScriptExportMinified()
    {
        jaxon()->setAppOption('assets.js.minify', true);
        $sScript = jaxon()->getScript();
        // file_put_contents(dirname(__DIR__) . '/src/js/app.link.min.html', $sScript);
        // Check that the return value is a file URI, and not js code.
        $this->assertStringNotContainsString('SamplePackageClass = {', $sScript);
        $this->assertStringContainsString('http://example.test/js', $sScript);
        $this->assertStringContainsString('.min.js', $sScript);
    }

    public function testScriptExportNotMinified()
    {
        jaxon()->setAppOption('assets.js.minify', false);
        $sScript = jaxon()->getScript();
        // file_put_contents(dirname(__DIR__) . '/src/js/app.link.html', $sScript);
        // Check that the return value is a file URI, and not js code.
        $this->assertStringNotContainsString('SamplePackageClass = {', $sScript);
        $this->assertStringContainsString('http://example.test/js', $sScript);
        $this->assertStringNotContainsString('.min.js', $sScript);
        $this->assertStringContainsString('.js', $sScript);
    }

    public function testScriptErrorMinifier()
    {
        // Register a minifier that always fails.
        jaxon()->di()->set(MinifierInterface::class, fn() =>
            new class implements MinifierInterface {
                public function minifyJsCode(string $sCode): string|false
                {
                    return false;
                }
                public function minifyCssCode(string $sCode): string|false
                {
                    return false;
                }
            });
        // The js file must be generated but not minified.
        jaxon()->setAppOption('assets.js.minify', true);
        $sScript = jaxon()->getScript();
        // Check that the return value is a file URI, and not js code.
        $this->assertStringNotContainsString('SamplePackageClass = {', $sScript);
        $this->assertStringContainsString('http://example.test/js', $sScript);
        $this->assertStringNotContainsString('.min.js', $sScript);
        $this->assertStringContainsString('.js', $sScript);
    }

    public function testScriptExportErrorIncorrectDir()
    {
        // Change the js dir
        jaxon()->setAppOption('assets.js.dir', dirname(__DIR__) . '/src/script'); // This dir must not exist.
        $sScript = jaxon()->getScript();
        $this->assertStringContainsString('SamplePackageClass = {', $sScript);
    }

    public function testScriptExportErrorIncorrectFile()
    {
        // The js subdir path is corrupted (with the '\0' char).
        jaxon()->setAppOption('assets.js.file', "\0js/app");
        $sScript = jaxon()->getScript();
        $this->assertStringContainsString('SamplePackageClass = {', $sScript);
    }

    public function testSetupIncorrectFile()
    {
        $this->expectException(SetupException::class);
        jaxon()->app()->setup(dirname(__DIR__) . '/config/app/not-found.php');
    }

    public function testSetupIncorrectConfig()
    {
        $this->expectException(SetupException::class);
        jaxon()->app()->setup(dirname(__DIR__) . '/config/app/app-error.php');
    }

    public function testJaxonClassAnnotations()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'Jaxon.NsTests.DirB.ClassB',
                        'method' => 'methodBa',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));

        $this->assertTrue(jaxon()->canProcessRequest());
        jaxon()->di()->getCallableClassPlugin()->processRequest();
    }

    public function testRequestToJaxonClass()
    {
        // The server request
        jaxon()->di()->set(ServerRequestInterface::class, fn($c) =>
            $c->g(ServerRequestCreator::class)
                ->fromGlobals()
                ->withParsedBody([
                    'jxncall' => json_encode([
                        'type' => 'class',
                        'name' => 'Jaxon.NsTests.DirB.ClassB',
                        'method' => 'methodBb',
                        'args' => [],
                    ]),
                ])
                ->withMethod('POST'));

        $this->assertTrue(jaxon()->canProcessRequest());
        $this->expectException(RequestException::class);
        // The processRequest() method now calls httpResponse(), which throws an exception.
        jaxon()->processRequest();
        // $this->expectException(RequestException::class);
        // jaxon()->httpResponse();
    }
}
