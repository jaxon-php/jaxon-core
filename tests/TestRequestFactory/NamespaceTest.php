<?php

namespace Jaxon\Tests\TestRequestFactory;

use Jaxon\Jaxon;
use Jaxon\Exception\SetupException;
use PHPUnit\Framework\TestCase;
use Jaxon\NsTests\DirA\ClassA;
use Jaxon\NsTests\DirB\ClassB;

class NamespaceTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.prefix.class', '');
        jaxon()->register(Jaxon::CALLABLE_DIR, dirname(__DIR__) . '/src/dir_ns', 'Jaxon\NsTests');
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
            'jaxon.exec({"_type":"expr","calls":[{"_type":"func","_name":"Jaxon.NsTests.DirA.ClassA.methodAa","args":[]}]})',
            rq(ClassA::class)->methodAa()->__toString()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToClassWithParameter()
    {
        $this->assertEquals(
            'jaxon.exec({"_type":"expr","calls":[{"_type":"func","_name":"Jaxon.NsTests.DirB.ClassB.methodBb","args":["string",2,true]}]})',
            rq(ClassB::class)->methodBb('string', 2, true)->__toString()
        );
    }
}
