<?php

namespace Jaxon\Tests\TestRegistrationApp;

require_once __DIR__ . '/../src/classes.php';

use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Code\MinifierInterface;
use Jaxon\Utils\Http\UriException;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\TestCase;

use function jaxon;

class RegistrationTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->app()->asset(true, true, 'http://example.test/js',
            realpath(__DIR__ . '/../src/js'))->setup(__DIR__ . '/../config/app/app.php');
    }

    /**
     * @throws SetupException
     */
    public function tearDown(): void
    {
        // Delete the generated js files
        $jsDir = realpath(__DIR__ . '/../src/js');
        $sHash = jaxon()->di()->getCodeGenerator()->getHash();
        @unlink("$jsDir/$sHash.js");
        @unlink("$jsDir/$sHash.min.js");

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
        $sScript = jaxon()->getScript();
        $this->assertStringContainsString('SamplePackageClass = {}', $sScript);
    }

    /**
     * @throws UriException
     */
    public function testScriptExportErrorIncorrectFile()
    {
        // Change the js dir
        jaxon()->setOption('js.app.file', 'js/app'); // This dir must not exist.
        $sScript = jaxon()->getScript();
        $this->assertStringContainsString('SamplePackageClass = {}', $sScript);
    }

    /**
     * @throws SetupException
     */
    public function testSetupIncorrectFile()
    {
        // Change the js dir
        $this->expectException(SetupException::class);
        jaxon()->app()->setup(__DIR__ . '/../config/app/not-found.php');
    }

    /**
     * @throws SetupException
     */
    public function testSetupIncorrectConfig()
    {
        // Change the js dir
        $this->expectException(SetupException::class);
        jaxon()->app()->setup(__DIR__ . '/../config/app/app-error.php');
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
            ]);
        });

        $this->assertTrue(jaxon()->app()->canProcessRequest());
        jaxon()->app()->processRequest();
        $this->expectException(RequestException::class);
        jaxon()->app()->httpResponse();
    }
}
