<?php

namespace Jaxon\Tests\RegistrationApp;

require_once __DIR__ . '/../defs/classes.php';

use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Code\MinifierInterface;
use Jaxon\Utils\File\FileMinifier;
use Jaxon\Utils\Http\UriException;
use PHPUnit\Framework\TestCase;

use function jaxon;

class RegistrationTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->app()->setup(__DIR__ . '/../config/app/app.php');
    }

    /**
     * @throws SetupException
     */
    public function tearDown(): void
    {
        // Delete the generated js files
        $jsDir = realpath(__DIR__ . '/../script');
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
        $this->assertStringContainsString('http://example.test/script', $sScript);
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
        $this->assertStringContainsString('http://example.test/script', $sScript);
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
        $this->assertStringContainsString('http://example.test/script', $sScript);
        $this->assertStringNotContainsString('.min.js', $sScript);
        $this->assertStringContainsString('.js', $sScript);
    }

    /**
     * @throws UriException
     */
    public function testScriptExportErrorIncorrectDir()
    {
        // Change the script dir
        jaxon()->setOption('js.app.dir', __DIR__ . '/../js'); // This dir must not exist.
        $sScript = jaxon()->getScript();
        $this->assertStringContainsString('SamplePackageClass = {}', $sScript);
    }

    /**
     * @throws UriException
     */
    public function testScriptExportErrorIncorrectFile()
    {
        // Change the script dir
        jaxon()->setOption('js.app.file', 'js/app'); // This dir must not exist.
        $sScript = jaxon()->getScript();
        $this->assertStringContainsString('SamplePackageClass = {}', $sScript);
    }
}
