<?php

namespace Jaxon\Tests\TestRequestFactory;

use Jaxon\Jaxon;
use Jaxon\Exception\SetupException;
use PHPUnit\Framework\TestCase;


final class FunctionTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.prefix.function', 'jxn_');
        // Register a function
        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'my_first_function',
            dirname(__DIR__) . '/src/first.php');
        // Register a function with an alias
        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'my_second_function', [
            'alias' => 'my_alias_function',
            'upload' => "'html_field_id'",
        ]);
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
    public function testRequestToGlobalFunction()
    {
        $this->assertEquals(
            'jaxon.exec({"_type":"expr","calls":[{"_type":"attr","_name":"window"},{"_type":"func","_name":"testFunction","args":[]}]})',
            jo()->testFunction()->__toString()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToGlobalFunctionWithParameter()
    {
        $this->assertEquals(
            'jaxon.exec({"_type":"expr","calls":[{"_type":"attr","_name":"window"},{"_type":"func","_name":"testFunction","args":["string",2,true]}]})',
            jo()->testFunction('string', 2, true)->__toString()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonFunction()
    {
        $this->assertEquals(
            'jaxon.exec({"_type":"expr","calls":[{"_type":"func","_name":"jxn_testFunction","args":[]}]})',
            rq()->testFunction()->__toString()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonFunctionWithParameter()
    {
        $this->assertEquals(
            'jaxon.exec({"_type":"expr","calls":[{"_type":"func","_name":"jxn_testFunction","args":["string",2,true]}]})',
            rq()->testFunction('string', 2, true)->__toString()
        );
    }

    /**
     * @throws SetupException
     */
    public function testClassNameIsEmpty()
    {
        $this->assertEquals('', rq()->_class());
    }
}
