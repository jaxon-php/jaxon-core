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
            rq()->func('testFunction')->getScript()
        );
    }

    public function testRequestToGlobalFunctionWithParameter()
    {
        $this->assertEquals(
            "testFunction('string', 2, true)",
            rq()->func('testFunction', 'string', 2, true)->getScript()
        );
    }

    public function testRequestToGlobalFunctionWithJaxonParameter()
    {
        $this->assertEquals(
            "testFunction('string', 2, true, jaxon.getFormValues('elt_id'), jaxon.$('elt_id').value)",
            rq()->func('testFunction', 'string', 2, true, rq()->form('elt_id'), rq()->input('elt_id'))->getScript()
        );
    }

    public function testRequestToJaxonFunction()
    {
        $this->assertEquals(
            "jaxon_testFunction()",
            rq()->call('testFunction')->getScript()
        );
    }

    public function testRequestToJaxonFunctionWithParameter()
    {
        $this->assertEquals(
            "jaxon_testFunction('string', 2, true)",
            rq()->call('testFunction', 'string', 2, true)->getScript()
        );
    }

    public function testRequestToJaxonFunctionWithJaxonParameter()
    {
        $this->assertEquals(
            "jaxon_testFunction('string', 2, true, jaxon.getFormValues('elt_id'), jaxon.$('elt_id').value)",
            rq()->call('testFunction', 'string', 2, true, rq()->form('elt_id'), rq()->input('elt_id'))->getScript()
        );
    }

    public function testRequestToJaxonClass()
    {
        $this->assertEquals(
            "JaxonTest.method()",
            rq()->call('Test.method')->getScript()
        );
    }

    public function testRequestToJaxonClassWithParameter()
    {
        $this->assertEquals(
            "JaxonTest.method('string', 2, true)",
            rq()->call('Test.method', 'string', 2, true)->getScript()
        );
    }

    public function testRequestToJaxonClassWithFormParameter()
    {
        $this->assertEquals(
            "JaxonTest.method(jaxon.getFormValues('elt_id'))",
            rq()->call('Test.method', rq()->form('elt_id'))->getScript()
        );
    }

    public function testRequestToJaxonClassWithInputParameter()
    {
        $this->assertEquals(
            "JaxonTest.method(jaxon.$('elt_id').value)",
            rq()->call('Test.method', rq()->input('elt_id'))->getScript()
        );
    }

    public function testRequestToJaxonClassWithCheckedParameter()
    {
        $this->assertEquals(
            "JaxonTest.method(jaxon.$('check_id').checked)",
            rq()->call('Test.method', rq()->checked('check_id'))->getScript()
        );
    }

    public function testRequestToJaxonClassWithSelectParameter()
    {
        $this->assertEquals(
            "JaxonTest.method(jaxon.$('select_id').value)",
            rq()->call('Test.method', rq()->select('select_id'))->getScript()
        );
    }

    public function testRequestToJaxonClassWithInnerHTMLParameter()
    {
        $this->assertEquals(
            "JaxonTest.method(jaxon.$('elt_id').innerHTML)",
            rq()->call('Test.method', rq()->html('elt_id'))->getScript()
        );
    }

    public function testRequestToJaxonClassWithMultipleParameter()
    {
        $this->assertEquals(
            "JaxonTest.method(jaxon.$('check_id').checked, jaxon.$('select_id').value, jaxon.$('elt_id').innerHTML)",
            rq()->call('Test.method', rq()->checked('check_id'), rq()->select('select_id'), rq()->html('elt_id'))->getScript()
        );
    }

    public function testRequestToJaxonClassWithConfirmation()
    {
        $this->assertEquals(
            "if(confirm('Really?')){JaxonTest.method(jaxon.$('elt_id').innerHTML);}",
            rq()->call('Test.method', rq()->html('elt_id'))->confirm("Really?")->getScript()
        );
    }

    public function testRequestToJaxonClassWithConfirmationAndSubstitution()
    {
         $this->assertEquals(
            "if(confirm('Really M. {1}?'.supplant({'1':jaxon.$('name_id').innerHTML}))){JaxonTest.method(jaxon.$('elt_id').innerHTML);}",
            rq()->call('Test.method', rq()->html('elt_id'))->confirm("Really M. {1}?", rq()->html('name_id'))->getScript()
        );
    }

    public function testRequestToJaxonClassWithConditionWhen()
    {
        $this->assertEquals(
            "if(jaxon.$('cond_id').checked){JaxonTest.method(jaxon.$('elt_id').innerHTML);}",
            rq()->call('Test.method', rq()->html('elt_id'))->when(rq()->checked('cond_id'))->getScript()
        );
    }

    public function testRequestToJaxonClassWithConditionWhenAndMessage()
    {
        $this->assertEquals(
            "if(jaxon.$('cond_id').checked){JaxonTest.method(jaxon.$('elt_id').innerHTML);}" .
                "else{alert('Please check the option');}",
            rq()->call('Test.method', rq()->html('elt_id'))
                ->when(rq()->checked('cond_id'))
                ->elseShow("Please check the option")->getScript()
        );
    }

    public function testRequestToJaxonClassWithConditionWhenAndMessageAndSubstitution()
    {
        $this->assertEquals(
            "if(jaxon.$('cond_id').checked){JaxonTest.method(jaxon.$('elt_id').innerHTML);}else" .
                "{alert('M. {1}, please check the option'.supplant({'1':jaxon.$('name_id').innerHTML}));}",
            rq()->call('Test.method', rq()->html('elt_id'))
                ->when(rq()->checked('cond_id'))
                ->elseShow("M. {1}, please check the option", rq()->html('name_id'))->getScript()
        );
    }

    public function testRequestToJaxonClassWithConditionUnless()
    {
         $this->assertEquals(
            "if(!(jaxon.$('cond_id').checked)){JaxonTest.method(jaxon.$('elt_id').innerHTML);}",
            rq()->call('Test.method', rq()->html('elt_id'))
                ->unless(rq()->checked('cond_id'))->getScript()
        );
    }

    public function testRequestToJaxonClassWithConditionUnlessAndMessage()
    {
         $this->assertEquals(
            "if(!(jaxon.$('cond_id').checked)){JaxonTest.method(jaxon.$('elt_id').innerHTML);}" .
                "else{alert('Please uncheck the option');}",
            rq()->call('Test.method', rq()->html('elt_id'))
                ->unless(rq()->checked('cond_id'))
                ->elseShow("Please uncheck the option")->getScript()
        );
    }

    public function testRequestToJaxonClassWithConditionUnlessAndMessageAndSubstitution()
    {
         $this->assertEquals(
            "if(!(jaxon.$('cond_id').checked)){JaxonTest.method(jaxon.$('elt_id').innerHTML);}" .
                "else{alert('M. {1}, please uncheck the option'.supplant({'1':jaxon.$('name_id').innerHTML}));}",
            rq()->call('Test.method', rq()->html('elt_id'))
                ->unless(rq()->checked('cond_id'))
                ->elseShow("M. {1}, please uncheck the option", rq()->html('name_id'))->getScript()
        );
    }
}
