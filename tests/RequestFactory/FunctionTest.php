<?php

namespace Jaxon\Tests\RequestFactory;

use Jaxon\Jaxon;
use PHPUnit\Framework\TestCase;

use function jaxon;
use function rq;
use function pm;

final class FunctionTest extends TestCase
{
    public function setUp(): void
    {
        jaxon()->setOption('core.prefix.function', 'jxn_');
        // Register a function
        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'my_first_function',
            __DIR__ . '/../defs/first.php');
        // Register a function with an alias
        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'my_second_function', [
            'alias' => 'my_alias_function',
            'upload' => "'html_field_id'",
        ]);
    }

    public function tearDown(): void
    {
        jaxon()->reset();
        parent::tearDown();
    }

    public function testRequestToGlobalFunction()
    {
        $this->assertEquals(
            "jxn_testFunction()",
            rq()->testFunction()->getScript()
        );
    }

    public function testRequestToGlobalFunctionWithParameter()
    {
        $this->assertEquals(
            "jxn_testFunction('string', 2, true)",
            rq()->testFunction('string', 2, true)->getScript()
        );
    }

    public function testRequestToGlobalFunctionWithJaxonParameter()
    {
        $this->assertEquals(
            "jxn_testFunction('string', 2, true, jaxon.getFormValues('elt_id'), jaxon.$('elt_id').value)",
            rq()->testFunction('string', 2, true, pm()->form('elt_id'), pm()->input('elt_id'))->getScript()
        );
    }

    public function testRequestToJaxonFunction()
    {
        $this->assertEquals(
            "jxn_testFunction()",
            rq()->testFunction()->getScript()
        );
    }

    public function testRequestToJaxonFunctionWithParameter()
    {
        $this->assertEquals(
            "jxn_testFunction('string', 2, true)",
            rq()->testFunction('string', 2, true)->getScript()
        );
    }

    public function testRequestToJaxonFunctionWithJaxonParameter()
    {
        $this->assertEquals(
            "jxn_testFunction('string', 2, true, jaxon.getFormValues('elt_id'), jaxon.$('elt_id').value)",
            rq()->testFunction('string', 2, true, pm()->form('elt_id'), pm()->input('elt_id'))->getScript()
        );
    }
}
