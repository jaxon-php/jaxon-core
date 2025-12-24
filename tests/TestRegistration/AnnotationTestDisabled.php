<?php

namespace Jaxon\Tests\TestRegistration;

require dirname(__DIR__, 1) . '/src/annotated.php';
require dirname(__DIR__, 1) . '/src/excluded.php';

use Jaxon\Jaxon;
use Jaxon\Exception\SetupException;
use Jaxon\Utils\Http\UriException;
use PHPUnit\Framework\TestCase;

use Annotated;
use Excluded;

use function dirname;
use function Jaxon\Annotations\_register;

class AnnotationTestDisabled extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        _register();

        jaxon()->config()->load(dirname(__DIR__) . '/config/annotations.php');

        jaxon()->register(Jaxon::CALLABLE_CLASS, Annotated::class);
        jaxon()->register(Jaxon::CALLABLE_CLASS, Excluded::class);
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
     * @throws UriException
     */
    public function testJsCode()
    {
        $sJs = jaxon()->getScript();

        $this->assertStringContainsString("upload: 'user-files'", $sJs);
        $this->assertStringContainsString('bags: ["user.name","page.number"]', $sJs);
        $this->assertStringNotContainsString('Excluded', $sJs);
    }
}
