<?php

namespace Jaxon\Tests\TestRequestFactory;

use Jaxon\Jaxon;
use Jaxon\Exception\SetupException;
use PHPUnit\Framework\TestCase;
use Jaxon\NsTests\DirA\ClassA;
use Jaxon\NsTests\DirB\ClassB;
use function Jaxon\jaxon;
use function Jaxon\rq;

class NamespaceTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.prefix.class', '');
        jaxon()->register(Jaxon::CALLABLE_DIR, __DIR__ . '/../src/dir_ns', 'Jaxon\NsTests');
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
            "Jaxon.NsTests.DirA.ClassA.methodAa()",
            rq(ClassA::class)->methodAa()->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToClassWithParameter()
    {
        $this->assertEquals(
            "Jaxon.NsTests.DirB.ClassB.methodBb('string', 2, true)",
            rq(ClassB::class)->methodBb('string', 2, true)->getScript()
        );
    }
}
