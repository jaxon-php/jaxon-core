<?php

namespace Jaxon\Tests\TestRequestFactory;

use Jaxon\Jaxon;
use Jaxon\Exception\SetupException;
use PHPUnit\Framework\TestCase;
use function Jaxon\jaxon;
use function Jaxon\rq;

class DirectoryTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.prefix.class', '');
        jaxon()->register(Jaxon::CALLABLE_DIR, __DIR__ . '/../src/dir');
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
     * @throws SetupException
     */
    public function testRequestToClass()
    {
        $this->assertEquals(
            "ClassA.methodAa()",
            rq('ClassA')->methodAa()->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToClassWithParameter()
    {
        $this->assertEquals(
            "ClassB.methodBb('string', 2, true)",
            rq('ClassB')->methodBb('string', 2, true)->getScript()
        );
    }
}
