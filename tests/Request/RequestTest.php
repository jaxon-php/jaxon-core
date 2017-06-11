<?php
namespace Jaxon\Tests\Request;

use PHPUnit\Framework\TestCase;

/**
 * @covers Jaxon\Request
 */
final class RequestTest extends TestCase
{
    public function testRequestToGlobalFunction()
    {
        $this->assertEquals(
            "testFunction()",
            rq()->func('testFunction')
        );
    }

    public function testRequestToGlobalFunctionWithParameter()
    {
        $this->assertEquals(
            "testFunction('string', 2, true)",
            rq()->func('testFunction', 'string', 2, true)
        );
    }

    public function testRequestToGlobalFunctionWithJaxonParameter()
    {
        $this->assertEquals(
            "testFunction('string', 2, true, jaxon.getFormValues('elt_id'), jaxon.$('elt_id').value)",
            rq()->func('testFunction', 'string', 2, true, rq()->form('elt_id'), rq()->input('elt_id'))
        );
    }

    public function testRequestToJaxonFunction()
    {
        $this->assertEquals(
            "jaxon_testFunction()",
            rq()->call('testFunction')
        );
    }

    public function testRequestToJaxonFunctionWithParameter()
    {
        $this->assertEquals(
            "jaxon_testFunction('string', 2, true)",
            rq()->call('testFunction', 'string', 2, true)
        );
    }

    public function testRequestToJaxonFunctionWithJaxonParameter()
    {
        $this->assertEquals(
            "jaxon_testFunction('string', 2, true, jaxon.getFormValues('elt_id'), jaxon.$('elt_id').value)",
            rq()->call('testFunction', 'string', 2, true, rq()->form('elt_id'), rq()->input('elt_id'))
        );
    }

    public function testRequestToJaxonClass()
    {
        $this->assertEquals(
            "JaxonTest.method()",
            rq()->call('Test.method')
        );
    }

    public function testRequestToJaxonClassWithParameter()
    {
        $this->assertEquals(
            "JaxonTest.method('string', 2, true)",
            rq()->call('Test.method', 'string', 2, true)
        );
    }

    public function testRequestToJaxonClassWithFormParameter()
    {
        $this->assertEquals(
            "JaxonTest.method(jaxon.getFormValues('elt_id'))",
            rq()->call('Test.method', rq()->form('elt_id'))
        );
    }

    public function testRequestToJaxonClassWithInputParameter()
    {
        $this->assertEquals(
            "JaxonTest.method(jaxon.$('elt_id').value)",
            rq()->call('Test.method', rq()->input('elt_id'))
        );
    }

    public function testRequestToJaxonClassWithCheckedParameter()
    {
        $this->assertEquals(
            "JaxonTest.method(jaxon.$('elt_id').checked)",
            rq()->call('Test.method', rq()->checked('elt_id'))
        );
    }

    public function testRequestToJaxonClassWithSelectParameter()
    {
        $this->assertEquals(
            "JaxonTest.method(jaxon.$('elt_id').value)",
            rq()->call('Test.method', rq()->select('elt_id'))
        );
    }

    public function testRequestToJaxonClassWithInnerHTMLParameter()
    {
        $this->assertEquals(
            "JaxonTest.method(jaxon.$('elt_id').innerHTML)",
            rq()->call('Test.method', rq()->html('elt_id'))
        );
    }

    public function testRequestToJaxonClassWithMultipleParameter()
    {
        $this->assertEquals(
            "JaxonTest.method(jaxon.$('elt_id').checked, jaxon.$('elt_id').value, jaxon.$('elt_id').innerHTML)",
            rq()->call('Test.method', rq()->checked('elt_id'), rq()->select('elt_id'), rq()->html('elt_id'))
        );
    }
}