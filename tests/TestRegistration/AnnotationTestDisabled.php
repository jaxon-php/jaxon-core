<?php

namespace Jaxon\Tests\TestRegistration;

require __DIR__ . '/../src/annotated.php';
require __DIR__ . '/../src/excluded.php';

use Jaxon\Jaxon;
use Jaxon\Exception\SetupException;
use Jaxon\Utils\Http\UriException;
use PHPUnit\Framework\TestCase;

use Annotated;
use Excluded;

use function Jaxon\jaxon;
use function Jaxon\Annotations\register;

class AnnotationTestDisabled extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        register();

        jaxon()->config(__DIR__ . '/../config/annotations.php');

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
