<?php

namespace Jaxon\Tests\TestMisc;

require __DIR__ . '/../src/session.php';

use Jaxon\Exception\RequestException;
use Jaxon\Jaxon;
use Jaxon\Exception\SetupException;
use Nyholm\Psr7\UploadedFile;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\NullLogger;
use PHPUnit\Framework\TestCase;
use SessionManager;

use function get_class;
use function Jaxon\jaxon;

final class MiscTest extends TestCase
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
     * @throws SetupException
     */
    protected function setUp(): void
    {
        $tmpDir = __DIR__ . '/../upload/tmp';
        @mkdir($tmpDir);

        $sSrcWhite = __DIR__ . '/../upload/src/white.png';
        $this->sNameWhite = 'white.png';
        $this->sPathWhite = "$tmpDir/{$this->sNameWhite}";
        $this->sSizeWhite = filesize($sSrcWhite);
        // Copy the file to the temp dir.
        @copy($sSrcWhite, $this->sPathWhite);

        jaxon()->config()->setOptions(['core' => ['language' => 'en']]);
    }

    /**
     * @throws SetupException
     */
    public function tearDown(): void
    {
        jaxon()->reset();
        parent::tearDown();
    }

    public function testLibraryVersion()
    {
        $this->assertEquals(Jaxon::VERSION, jaxon()->getVersion());
    }

    public function testTranslator()
    {
        $this->assertEquals('PHP Error Messages: Incorrect.',
            jaxon()->translator()->trans('errors.debug.message', ['message' => 'Incorrect.']));
        $this->assertEquals('PHP Error Messages: Incorrect.',
            jaxon()->translator()->trans('errors.debug.message', ['message' => 'Incorrect.'], 'en'));
        $this->assertEquals("Messages d'erreur PHP: Incorrect.",
            jaxon()->translator()->trans('errors.debug.message', ['message' => 'Incorrect.'], 'fr'));
        $this->assertEquals('Mensajes de error de PHP: Incorrect.',
            jaxon()->translator()->trans('errors.debug.message', ['message' => 'Incorrect.'], 'es'));

        jaxon()->setOption('core.language', 'fr');
        $this->assertEquals("Messages d'erreur PHP: Incorrect.",
            jaxon()->translator()->trans('errors.debug.message', ['message' => 'Incorrect.']));
        jaxon()->setOption('core.language', 'es');
        $this->assertEquals('Mensajes de error de PHP: Incorrect.',
            jaxon()->translator()->trans('errors.debug.message', ['message' => 'Incorrect.']));
    }

    public function testNullLogger()
    {
        $this->assertNotNull(jaxon()->logger());
        $this->assertEquals(NullLogger::class, get_class(jaxon()->logger()));
    }

    public function testSessions()
    {
        jaxon()->di()->setSessionManager(function() {
            return new SessionManager();
        });
        $this->assertNull(jaxon()->session()->get('key'));
        jaxon()->session()->set('key', 'value');
        $this->assertEquals('value', jaxon()->session()->get('key'));
    }

    /**
     * @throws RequestException
     */
    public function testHttpUploadDisabled()
    {
        jaxon()->setOption('core.upload.enabled', false);
        // Send a request to the registered class
        jaxon()->di()->set(ServerRequestInterface::class, function($c) {
            return $c->g(ServerRequestCreator::class)->fromGlobals()->withUploadedFiles([
                'image' => new UploadedFile($this->sPathWhite, $this->sSizeWhite,
                    UPLOAD_ERR_OK, $this->sNameWhite, 'png'),
            ])->withMethod('POST');
        });

        $this->assertFalse(jaxon()->di()->getRequestHandler()->canProcessRequest());
    }
}
