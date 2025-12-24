<?php

namespace Jaxon\Tests\TestRequestFactory;

use Jaxon\Jaxon;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Request\CallableClass\CallableClassPlugin;
use PHPUnit\Framework\TestCase;


final class RequestTest extends TestCase
{
    /**
     * @var CallableClassPlugin
     */
    protected $xPlugin;

    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        // jaxon()->setOption('core.prefix.class', 'Jxn');

        jaxon()->register(Jaxon::CALLABLE_CLASS, 'Sample', dirname(__DIR__) . '/src/sample.php');

        $this->xPlugin = jaxon()->di()->getCallableClassPlugin();
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
    public function testRequestToJaxonClass()
    {
        $this->assertEquals(
            'jaxon.exec({"_type":"expr","calls":[{"_type":"func","_name":"JaxonSample.method","args":[]}]})',
            rq('Sample')->method()->__toString()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithParameter()
    {
        $this->assertEquals(
            'jaxon.exec({"_type":"expr","calls":[{"_type":"func","_name":"JaxonSample.method","args":["string",2,true]}]})',
            rq('Sample')->method('string', 2, true)->__toString()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithPageParameter()
    {
        $this->assertEquals(
            'jaxon.exec({"_type":"expr","calls":[{"_type":"func","_name":"JaxonSample.method","args":[2,5,{"_type":"page","_name":""}]}]})',
            rq('Sample')->method(2, 5, je()->rd()->page())->__toString()
        );
    }

    /**
     * @throws SetupException
     */
    public function testCallsToRqFactory()
    {
        $this->assertEquals(
            'jaxon.exec({"_type":"expr","calls":[{"_type":"attr","_name":"JaxonSample"}]})',
            rq('Sample')->__toString()
        );
        $this->assertEquals(
            'jaxon.exec({"_type":"expr","calls":[{"_type":"attr","_name":""}]})',
            rq()->__toString()
        );
    }

    /**
     * @throws SetupException
     */
    public function testCallsToJoFactory()
    {
        $this->assertEquals(
            'jaxon.exec({"_type":"expr","calls":[{"_type":"attr","_name":"Sample"}]})',
            jo('Sample')->__toString()
        );
        $this->assertEquals(
            'jaxon.exec({"_type":"expr","calls":[{"_type":"attr","_name":"window"}]})',
            jo()->__toString()
        );
    }

    /**
     * @throws SetupException
     */
    public function testCallsToJqFactory()
    {
        $this->assertEquals(
            'jaxon.exec({"_type":"expr","calls":[{"_type":"select","_name":".selector","mode":"jq"}]})',
            jq('.selector')->__toString()
        );
        $this->assertEquals(
            'jaxon.exec({"_type":"expr","calls":[{"_type":"select","_name":"this","mode":"jq"}]})',
            jq()->__toString()
        );
    }

    /**
     * @throws SetupException
     */
    public function testCallsToJeFactory()
    {
        $this->assertEquals(
            'jaxon.exec({"_type":"expr","calls":[{"_type":"select","_name":"selector","mode":"js"}]})',
            je('selector')->__toString()
        );
        $this->assertEquals(
            'jaxon.exec({"_type":"expr","calls":[{"_type":"select","_name":"this","mode":"js"}]})',
            je()->__toString()
        );
    }
}
