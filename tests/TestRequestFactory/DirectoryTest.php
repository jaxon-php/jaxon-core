<?php

namespace Jaxon\Tests\TestRequestFactory;

use Jaxon\Jaxon;
use Jaxon\Exception\SetupException;
use PHPUnit\Framework\TestCase;

class DirectoryTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.prefix.class', '');
        jaxon()->register(Jaxon::CALLABLE_DIR, dirname(__DIR__) . '/src/dir');
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
            'jaxon.exec({"_type":"expr","calls":[{"_type":"func","_name":"ClassA.methodAa","args":[]}]})',
            rq('ClassA')->methodAa()->__toString()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToClassWithParameter()
    {
        $this->assertEquals(
            'jaxon.exec({"_type":"expr","calls":[{"_type":"func","_name":"ClassB.methodBb","args":["string",2,true]}]})',
            rq('ClassB')->methodBb('string', 2, true)->__toString()
        );
    }
}
