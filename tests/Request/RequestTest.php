<?php

namespace Jaxon\Tests\Request;

use Jaxon\Jaxon;
use PHPUnit\Framework\TestCase;

use function jaxon;
use function pr;
use function pm;

/**
 * @covers Jaxon\Request
 */
final class RequestTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        jaxon()->register(Jaxon::CALLABLE_CLASS, 'Test', __DIR__ . '/defs/classes.php');
    }

    public function testRequestToGlobalFunction()
    {
        $this->assertEquals(
            "jaxon_testFunction()",
            rq()->testFunction()->getScript()
        );
    }

    public function testRequestToGlobalFunctionWithParameter()
    {
        $this->assertEquals(
            "jaxon_testFunction('string', 2, true)",
            rq()->testFunction('string', 2, true)->getScript()
        );
    }

    public function testRequestToGlobalFunctionWithJaxonParameter()
    {
        $this->assertEquals(
            "jaxon_testFunction('string', 2, true, jaxon.getFormValues('elt_id'), jaxon.$('elt_id').value)",
            rq()->testFunction('string', 2, true, pm()->form('elt_id'), pm()->input('elt_id'))->getScript()
        );
    }

    public function testRequestToJaxonFunction()
    {
        $this->assertEquals(
            "jaxon_testFunction()",
            rq()->testFunction()->getScript()
        );
    }

    public function testRequestToJaxonFunctionWithParameter()
    {
        $this->assertEquals(
            "jaxon_testFunction('string', 2, true)",
            rq()->testFunction('string', 2, true)->getScript()
        );
    }

    public function testRequestToJaxonFunctionWithJaxonParameter()
    {
        $this->assertEquals(
            "jaxon_testFunction('string', 2, true, jaxon.getFormValues('elt_id'), jaxon.$('elt_id').value)",
            rq()->testFunction('string', 2, true, pm()->form('elt_id'), pm()->input('elt_id'))->getScript()
        );
    }

    public function testRequestToJaxonClass()
    {
        $this->assertEquals(
            "JaxonTest.method()",
            rq('Test')->method()->getScript()
        );
        $this->assertEquals(
            "JaxonTest.method()",
            rq('Test')->call('method')->getScript()
        );
    }

    public function testRequestToJaxonClassWithParameter()
    {
        $this->assertEquals(
            "JaxonTest.method('string', 2, true)",
            rq('Test')->method('string', 2, true)->getScript()
        );
        $this->assertEquals(
            "JaxonTest.method('string', 2, true)",
            rq('Test')->call('method', 'string', 2, true)->getScript()
        );
    }

    public function testRequestToJaxonClassWithFormParameter()
    {
        $this->assertEquals(
            "JaxonTest.method(jaxon.getFormValues('elt_id'))",
            rq('Test')->method(pm()->form('elt_id'))->getScript()
        );
        $this->assertEquals(
            "JaxonTest.method(jaxon.getFormValues('elt_id'))",
            rq('Test')->call('method', pm()->form('elt_id'))->getScript()
        );
    }

    public function testRequestToJaxonClassWithInputParameter()
    {
        $this->assertEquals(
            "JaxonTest.method(jaxon.$('elt_id').value)",
            rq('Test')->method(pm()->input('elt_id'))->getScript()
        );
        $this->assertEquals(
            "JaxonTest.method(jaxon.$('elt_id').value)",
            rq('Test')->call('method', pm()->input('elt_id'))->getScript()
        );
    }

    public function testRequestToJaxonClassWithCheckedParameter()
    {
        $this->assertEquals(
            "JaxonTest.method(jaxon.$('check_id').checked)",
            rq('Test')->method(pm()->checked('check_id'))->getScript()
        );
        $this->assertEquals(
            "JaxonTest.method(jaxon.$('check_id').checked)",
            rq('Test')->call('method', pm()->checked('check_id'))->getScript()
        );
    }

    public function testRequestToJaxonClassWithSelectParameter()
    {
        $this->assertEquals(
            "JaxonTest.method(jaxon.$('select_id').value)",
            rq('Test')->method(pm()->select('select_id'))->getScript()
        );
        $this->assertEquals(
            "JaxonTest.method(jaxon.$('select_id').value)",
            rq('Test')->call('method', pm()->select('select_id'))->getScript()
        );
    }

    public function testRequestToJaxonClassWithInnerHTMLParameter()
    {
        $this->assertEquals(
            "JaxonTest.method(jaxon.$('elt_id').innerHTML)",
            rq('Test')->method(pm()->html('elt_id'))->getScript()
        );
        $this->assertEquals(
            "JaxonTest.method(jaxon.$('elt_id').innerHTML)",
            rq('Test')->call('method', pm()->html('elt_id'))->getScript()
        );
    }

    public function testRequestToJaxonClassWithMultipleParameter()
    {
        $this->assertEquals(
            "JaxonTest.method(jaxon.$('check_id').checked, jaxon.$('select_id').value, jaxon.$('elt_id').innerHTML)",
            rq('Test')->method(pm()->checked('check_id'), pm()->select('select_id'), pm()->html('elt_id'))->getScript()
        );
        $this->assertEquals(
            "JaxonTest.method(jaxon.$('check_id').checked, jaxon.$('select_id').value, jaxon.$('elt_id').innerHTML)",
            rq('Test')->call('method', pm()->checked('check_id'), pm()->select('select_id'), pm()->html('elt_id'))->getScript()
        );
    }

    public function testRequestToJaxonClassWithConfirmation()
    {
        $this->assertEquals(
            "if(confirm('Really?')){JaxonTest.method(jaxon.$('elt_id').innerHTML);}",
            rq('Test')->method(pm()->html('elt_id'))->confirm("Really?")->getScript()
        );
        $this->assertEquals(
            "if(confirm('Really?')){JaxonTest.method(jaxon.$('elt_id').innerHTML);}",
            rq('Test')->call('method', pm()->html('elt_id'))->confirm("Really?")->getScript()
        );
    }

    public function testRequestToJaxonClassWithConfirmationAndSubstitution()
    {
         $this->assertEquals(
            "if(confirm('Really M. {1}?'.supplant({'1':jaxon.$('name_id').innerHTML}))){JaxonTest.method(jaxon.$('elt_id').innerHTML);}",
            rq('Test')->method(pm()->html('elt_id'))->confirm("Really M. {1}?", pm()->html('name_id'))->getScript()
        );
        $this->assertEquals(
            "if(confirm('Really M. {1}?'.supplant({'1':jaxon.$('name_id').innerHTML}))){JaxonTest.method(jaxon.$('elt_id').innerHTML);}",
            rq('Test')->call('method', pm()->html('elt_id'))->confirm("Really M. {1}?", pm()->html('name_id'))->getScript()
        );
    }

    public function testRequestToJaxonClassWithConditionWhen()
    {
        $this->assertEquals(
            "if(jaxon.$('cond_id').checked){JaxonTest.method(jaxon.$('elt_id').innerHTML);}",
            rq('Test')->method(pm()->html('elt_id'))->when(pm()->checked('cond_id'))->getScript()
        );
        $this->assertEquals(
            "if(jaxon.$('cond_id').checked){JaxonTest.method(jaxon.$('elt_id').innerHTML);}",
            rq('Test')->call('method', pm()->html('elt_id'))->when(pm()->checked('cond_id'))->getScript()
        );
    }

    public function testRequestToJaxonClassWithConditionWhenAndMessage()
    {
        $this->assertEquals(
            "if(jaxon.$('cond_id').checked){JaxonTest.method(jaxon.$('elt_id').innerHTML);}" .
                "else{alert('Please check the option');}",
            rq('Test')->method(pm()->html('elt_id'))
                ->when(pm()->checked('cond_id'))
                ->elseShow("Please check the option")->getScript()
        );
        $this->assertEquals(
            "if(jaxon.$('cond_id').checked){JaxonTest.method(jaxon.$('elt_id').innerHTML);}" .
                "else{alert('Please check the option');}",
            rq('Test')->call('method', pm()->html('elt_id'))
                ->when(pm()->checked('cond_id'))
                ->elseShow("Please check the option")->getScript()
        );
    }

    public function testRequestToJaxonClassWithConditionWhenAndMessageAndSubstitution()
    {
        $this->assertEquals(
            "if(jaxon.$('cond_id').checked){JaxonTest.method(jaxon.$('elt_id').innerHTML);}else" .
                "{alert('M. {1}, please check the option'.supplant({'1':jaxon.$('name_id').innerHTML}));}",
            rq('Test')->method(pm()->html('elt_id'))
                ->when(pm()->checked('cond_id'))
                ->elseShow("M. {1}, please check the option", pm()->html('name_id'))->getScript()
        );
        $this->assertEquals(
            "if(jaxon.$('cond_id').checked){JaxonTest.method(jaxon.$('elt_id').innerHTML);}else" .
                "{alert('M. {1}, please check the option'.supplant({'1':jaxon.$('name_id').innerHTML}));}",
            rq('Test')->call('method', pm()->html('elt_id'))
                ->when(pm()->checked('cond_id'))
                ->elseShow("M. {1}, please check the option", pm()->html('name_id'))->getScript()
        );
    }

    public function testRequestToJaxonClassWithConditionUnless()
    {
         $this->assertEquals(
            "if(!(jaxon.$('cond_id').checked)){JaxonTest.method(jaxon.$('elt_id').innerHTML);}",
            rq('Test')->method(pm()->html('elt_id'))
                ->unless(pm()->checked('cond_id'))->getScript()
        );
        $this->assertEquals(
            "if(!(jaxon.$('cond_id').checked)){JaxonTest.method(jaxon.$('elt_id').innerHTML);}",
            rq('Test')->call('method', pm()->html('elt_id'))
                ->unless(pm()->checked('cond_id'))->getScript()
        );
    }

    public function testRequestToJaxonClassWithConditionUnlessAndMessage()
    {
         $this->assertEquals(
            "if(!(jaxon.$('cond_id').checked)){JaxonTest.method(jaxon.$('elt_id').innerHTML);}" .
                "else{alert('Please uncheck the option');}",
            rq('Test')->method(pm()->html('elt_id'))
                ->unless(pm()->checked('cond_id'))
                ->elseShow("Please uncheck the option")->getScript()
        );
        $this->assertEquals(
            "if(!(jaxon.$('cond_id').checked)){JaxonTest.method(jaxon.$('elt_id').innerHTML);}" .
                "else{alert('Please uncheck the option');}",
            rq('Test')->call('method', pm()->html('elt_id'))
                ->unless(pm()->checked('cond_id'))
                ->elseShow("Please uncheck the option")->getScript()
        );
    }

    public function testRequestToJaxonClassWithConditionUnlessAndMessageAndSubstitution()
    {
         $this->assertEquals(
            "if(!(jaxon.$('cond_id').checked)){JaxonTest.method(jaxon.$('elt_id').innerHTML);}" .
                "else{alert('M. {1}, please uncheck the option'.supplant({'1':jaxon.$('name_id').innerHTML}));}",
            rq('Test')->method(pm()->html('elt_id'))
                ->unless(pm()->checked('cond_id'))
                ->elseShow("M. {1}, please uncheck the option", pm()->html('name_id'))->getScript()
        );
        $this->assertEquals(
            "if(!(jaxon.$('cond_id').checked)){JaxonTest.method(jaxon.$('elt_id').innerHTML);}" .
                "else{alert('M. {1}, please uncheck the option'.supplant({'1':jaxon.$('name_id').innerHTML}));}",
            rq('Test')->call('method', pm()->html('elt_id'))
                ->unless(pm()->checked('cond_id'))
                ->elseShow("M. {1}, please uncheck the option", pm()->html('name_id'))->getScript()
        );
    }
}
