<?php

namespace Jaxon\Tests\TestMisc;

require dirname(__DIR__, 1) . '/src/session.php';

use Jaxon\Jaxon;
use Jaxon\Exception\SetupException;
use Psr\Log\NullLogger;
use PHPUnit\Framework\TestCase;
use SessionManager;

use function get_class;

final class MiscTest extends TestCase
{
    /**
     * @throws SetupException
     */
    protected function setUp(): void
    {
        jaxon()->setOptions(['core' => ['language' => 'en']]);
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
        jaxon()->di()->setSessionManager(fn() => new SessionManager());
        $this->assertNull(jaxon()->session()->get('key'));
        jaxon()->session()->set('key', 'value');
        $this->assertEquals('value', jaxon()->session()->get('key'));
    }
}
