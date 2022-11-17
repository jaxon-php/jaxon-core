<?php

namespace Jaxon\Tests\TestRequestFactory;

use Jaxon\Jaxon;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Request\CallableClass\CallableClassPlugin;
use PHPUnit\Framework\TestCase;

use function Jaxon\jaxon;
use function Jaxon\pm;
use function Jaxon\rq;

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

        jaxon()->register(Jaxon::CALLABLE_CLASS, 'Sample', __DIR__ . '/../src/sample.php');

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
            "JaxonSample.method()",
            rq('Sample')->call('method')->getScript()
        );
        $this->assertEquals(
            "JaxonSample.method()",
            jaxon()->request('Sample')->call('method')->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithParameter()
    {
        $this->assertEquals(
            "JaxonSample.method('string', 2, true)",
            rq('Sample')->method('string', 2, true)->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithStringParameter()
    {
        $this->assertEquals(
            "JaxonSample.method('string')",
            rq('Sample')->method(pm()->string('string'))->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithIntParameter()
    {
        $this->assertEquals(
            "JaxonSample.method(2, 5)",
            rq('Sample')->method(pm()->int(2), pm()->numeric(5))->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithJsParameter()
    {
        $this->assertEquals(
            "JaxonSample.method(jaxon.val(), jaxon.func())",
            rq('Sample')->method(pm()->js('jaxon.val()'), pm()->javascript('jaxon.func()'))->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithPageParameter()
    {
        $this->assertEquals(
            "JaxonSample.method(2, 5, 0)",
            rq('Sample')->method(2, 5, pm()->page())->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithFormParameter()
    {
        $this->assertEquals(
            "JaxonSample.method(jaxon.getFormValues('elt_id'))",
            rq('Sample')->method(pm()->form('elt_id'))->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithInputParameter()
    {
        $this->assertEquals(
            "JaxonSample.method(jaxon.$('elt_id').value)",
            rq('Sample')->method(pm()->input('elt_id'))->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithCheckedParameter()
    {
        $this->assertEquals(
            "JaxonSample.method(jaxon.$('check_id').checked)",
            rq('Sample')->method(pm()->checked('check_id'))->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithSelectParameter()
    {
        $this->assertEquals(
            "JaxonSample.method(jaxon.$('select_id').value)",
            rq('Sample')->method(pm()->select('select_id'))->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithInnerHTMLParameter()
    {
        $this->assertEquals(
            "JaxonSample.method(jaxon.$('elt_id').innerHTML)",
            rq('Sample')->method(pm()->html('elt_id'))->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithMultipleParameter()
    {
        $this->assertEquals(
            "JaxonSample.method(jaxon.$('check_id').checked, jaxon.$('select_id').value, jaxon.$('elt_id').innerHTML)",
            rq('Sample')->method(pm()->checked('check_id'),
                pm()->select('select_id'), pm()->html('elt_id'))->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithConfirmation()
    {
        $this->assertEquals(
            "if(confirm('Really?')){JaxonSample.method(jaxon.$('elt_id').innerHTML);}",
            rq('Sample')->method(pm()->html('elt_id'))->confirm("Really?")->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithConfirmationAndMessage()
    {
        $this->assertEquals(
            "if(confirm('Really?')){JaxonSample.method(jaxon.$('elt_id').innerHTML);}else{alert('No confirm');}",
            rq('Sample')->method(pm()->html('elt_id'))->confirm("Really?")
                ->elseShow("No confirm")->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithConfirmationAndSubstitution()
    {
         $this->assertEquals(
            "if(confirm('Really M. {1}?'.supplant({'1':jaxon.$('name_id').innerHTML})))" .
                "{JaxonSample.method(jaxon.$('elt_id').innerHTML);}",
            rq('Sample')->method(pm()->html('elt_id'))->confirm("Really M. {1}?",
                pm()->html('name_id'))->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithConfirmationMessageAndSubstitution()
    {
        $this->assertEquals(
            "if(confirm('Really M. {1}?'.supplant({'1':jaxon.$('name_id').innerHTML})))" .
                "{JaxonSample.method(jaxon.$('elt_id').innerHTML);}else{alert('Oh! Sorry M. {1}!'." .
                "supplant({'1':jaxon.$('name_id').innerHTML}));}",
            rq('Sample')->method(pm()->html('elt_id'))
                ->confirm("Really M. {1}?", pm()->html('name_id'))
                ->elseShow("Oh! Sorry M. {1}!", pm()->html('name_id'))->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithConditionWhen()
    {
        $this->assertEquals(
            "if(jaxon.$('cond_id').checked){JaxonSample.method(jaxon.$('elt_id').innerHTML);}",
            rq('Sample')->method(pm()->html('elt_id'))->when(pm()->checked('cond_id'))->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithConditionWhenAndMessage()
    {
        $this->assertEquals(
            "if(jaxon.$('cond_id').checked){JaxonSample.method(jaxon.$('elt_id').innerHTML);}" .
                "else{alert('Please check the option');}",
            rq('Sample')->method(pm()->html('elt_id'))
                ->when(pm()->checked('cond_id'))
                ->elseShow("Please check the option")->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithConditionWhenAndMessageAndSubstitution()
    {
        $this->assertEquals(
            "if(jaxon.$('cond_id').checked){JaxonSample.method(jaxon.$('elt_id').innerHTML);}else" .
                "{alert('M. {1}, please check the option'.supplant({'1':jaxon.$('name_id').innerHTML}));}",
            rq('Sample')->method(pm()->html('elt_id'))
                ->when(pm()->checked('cond_id'))
                ->elseShow("M. {1}, please check the option", pm()->html('name_id'))->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithConditionUnless()
    {
         $this->assertEquals(
            "if(!(jaxon.$('cond_id').checked)){JaxonSample.method(jaxon.$('elt_id').innerHTML);}",
            rq('Sample')->method(pm()->html('elt_id'))
                ->unless(pm()->checked('cond_id'))->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithConditionUnlessAndMessage()
    {
         $this->assertEquals(
            "if(!(jaxon.$('cond_id').checked)){JaxonSample.method(jaxon.$('elt_id').innerHTML);}" .
                "else{alert('Please uncheck the option');}",
            rq('Sample')->method(pm()->html('elt_id'))
                ->unless(pm()->checked('cond_id'))
                ->elseShow("Please uncheck the option")->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithConditionUnlessAndMessageAndSubstitution()
    {
         $this->assertEquals(
            "if(!(jaxon.$('cond_id').checked)){JaxonSample.method(jaxon.$('elt_id').innerHTML);}" .
                "else{alert('M. {1}, please uncheck the option'.supplant({'1':jaxon.$('name_id').innerHTML}));}",
            rq('Sample')->method(pm()->html('elt_id'))
                ->unless(pm()->checked('cond_id'))
                ->elseShow("M. {1}, please uncheck the option", pm()->html('name_id'))->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithIfeqCondition()
    {
        $this->assertEquals(
            "if(jaxon.$('elt_id').innerHTML=='yes'){JaxonSample.method();}",
            rq('Sample')->method()->ifeq(pm()->html('elt_id'), 'yes')->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithIfteqCondition()
    {
        $this->assertEquals(
            "if(jaxon.$('elt_id').innerHTML==='yes'){JaxonSample.method();}",
            rq('Sample')->method()->ifteq(pm()->html('elt_id'), 'yes')->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithIfneCondition()
    {
        $this->assertEquals(
            "if(jaxon.$('elt_id').innerHTML!='yes'){JaxonSample.method();}",
            rq('Sample')->method()->ifne(pm()->html('elt_id'), 'yes')->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithIfnteCondition()
    {
        $this->assertEquals(
            "if(jaxon.$('elt_id').innerHTML!=='yes'){JaxonSample.method();}",
            rq('Sample')->method()->ifnte(pm()->html('elt_id'), 'yes')->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithIfgtCondition()
    {
        $this->assertEquals(
            "if(jaxon.$('elt_id').innerHTML>10){JaxonSample.method();}",
            rq('Sample')->method()->ifgt(pm()->html('elt_id'), 10)->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithIfgeCondition()
    {
        $this->assertEquals(
            "if(jaxon.$('elt_id').innerHTML>=10){JaxonSample.method();}",
            rq('Sample')->method()->ifge(pm()->html('elt_id'), 10)->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithIfltCondition()
    {
        $this->assertEquals(
            "if(jaxon.$('elt_id').innerHTML<10){JaxonSample.method();}",
            rq('Sample')->method()->iflt(pm()->html('elt_id'), 10)->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithIfleCondition()
    {
        $this->assertEquals(
            "if(jaxon.$('elt_id').innerHTML<=10){JaxonSample.method();}",
            rq('Sample')->method()->ifle(pm()->html('elt_id'), 10)->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testConfirmMessageSuccess()
    {
        $this->assertEquals(
            "if(confirm('Really?')){JaxonSample.method(jaxon.$('elt_id').innerHTML);}" .
                "else{alert('No confirm');}",
            rq('Sample')->method(pm()->html('elt_id'))->confirm("Really?")
                ->elseSuccess("No confirm")->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testConfirmMessageInfo()
    {
        $this->assertEquals(
            "if(confirm('Really?')){JaxonSample.method(jaxon.$('elt_id').innerHTML);}" .
                "else{alert('No confirm');}",
            rq('Sample')->method(pm()->html('elt_id'))->confirm("Really?")
                ->elseInfo("No confirm")->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testConfirmMessageWarning()
    {
        $this->assertEquals(
            "if(confirm('Really?')){JaxonSample.method(jaxon.$('elt_id').innerHTML);}" .
                "else{alert('No confirm');}",
            rq('Sample')->method(pm()->html('elt_id'))->confirm("Really?")
                ->elseWarning("No confirm")->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testConfirmMessageError()
    {
        $this->assertEquals(
            "if(confirm('Really?')){JaxonSample.method(jaxon.$('elt_id').innerHTML);}" .
                "else{alert('No confirm');}",
            rq('Sample')->method(pm()->html('elt_id'))->confirm("Really?")
                ->elseError("No confirm")->getScript()
        );
    }
}
