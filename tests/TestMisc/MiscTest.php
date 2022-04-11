<?php

namespace Jaxon\Tests\TestMisc;

require __DIR__ . '/../src/session.php';

use Jaxon\Exception\SetupException;
use Psr\Log\NullLogger;
use PHPUnit\Framework\TestCase;
use SessionManager;

use function get_class;
use function jaxon;

final class MiscTest extends TestCase
{
    /**
     * @throws SetupException
     */
    protected function setUp(): void
    {
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
}
