<?php

namespace Jaxon\Tests\TestRequestFactory;

use Jaxon\Jaxon;
use PHPUnit\Framework\TestCase;
use Jaxon\Exception\SetupException;

use function Jaxon\jaxon;
use function Jaxon\rq;
use function Jaxon\pm;

class ClassTest extends TestCase
{
    public function setUp(): void
    {
        jaxon()->register(Jaxon::CALLABLE_CLASS, 'Sample', __DIR__ . '/../src/sample.php');
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
            rq('Sample')->method()->raw()
        );
        $this->assertEquals(
            "JaxonSample.method()",
            rq('Sample')->method()->raw()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithParameter()
    {
        $this->assertEquals(
            "JaxonSample.method('string', 2, true)",
            rq('Sample')->method('string', 2, true)->raw()
        );
        $this->assertEquals(
            "JaxonSample.method('string', 2, true)",
            rq('Sample')->method('string', 2, true)->raw()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithFormParameter()
    {
        $this->assertEquals(
            "JaxonSample.method(jaxon.getFormValues('elt_id'))",
            rq('Sample')->method(pm()->form('elt_id'))->raw()
        );
        $this->assertEquals(
            "JaxonSample.method(jaxon.getFormValues('elt_id'))",
            rq('Sample')->method(pm()->form('elt_id'))->raw()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithInputParameter()
    {
        $this->assertEquals(
            "JaxonSample.method(jaxon.$('elt_id').value)",
            rq('Sample')->method(pm()->input('elt_id'))->raw()
        );
        $this->assertEquals(
            "JaxonSample.method(jaxon.$('elt_id').value)",
            rq('Sample')->method(pm()->input('elt_id'))->raw()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithCheckedParameter()
    {
        $this->assertEquals(
            "JaxonSample.method(jaxon.$('check_id').checked)",
            rq('Sample')->method(pm()->checked('check_id'))->raw()
        );
        $this->assertEquals(
            "JaxonSample.method(jaxon.$('check_id').checked)",
            rq('Sample')->method(pm()->checked('check_id'))->raw()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithSelectParameter()
    {
        $this->assertEquals(
            "JaxonSample.method(jaxon.$('select_id').value)",
            rq('Sample')->method(pm()->select('select_id'))->raw()
        );
        $this->assertEquals(
            "JaxonSample.method(jaxon.$('select_id').value)",
            rq('Sample')->method(pm()->select('select_id'))->raw()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithInnerHTMLParameter()
    {
        $this->assertEquals(
            "JaxonSample.method(jaxon.$('elt_id').innerHTML)",
            rq('Sample')->method(pm()->html('elt_id'))->raw()
        );
        $this->assertEquals(
            "JaxonSample.method(jaxon.$('elt_id').innerHTML)",
            rq('Sample')->method(pm()->html('elt_id'))->raw()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithMultipleParameter()
    {
        $this->assertEquals(
            "JaxonSample.method(jaxon.$('check_id').checked, jaxon.$('select_id').value, jaxon.$('elt_id').innerHTML)",
            rq('Sample')->method(pm()->checked('check_id'), pm()->select('select_id'), pm()->html('elt_id'))->raw()
        );
        $this->assertEquals(
            "JaxonSample.method(jaxon.$('check_id').checked, jaxon.$('select_id').value, jaxon.$('elt_id').innerHTML)",
            rq('Sample')->method(pm()->checked('check_id'), pm()->select('select_id'), pm()->html('elt_id'))->raw()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithConfirmation()
    {
        $this->assertEquals(
            // "if(confirm('Really?')){JaxonSample.method(jaxon.$('elt_id').innerHTML);}",
            "JaxonSample.method(jaxon.$('elt_id').innerHTML)",
            rq('Sample')->method(pm()->html('elt_id'))->confirm("Really?")->raw()
        );
        $this->assertEquals(
            // "if(confirm('Really?')){JaxonSample.method(jaxon.$('elt_id').innerHTML);}",
            "JaxonSample.method(jaxon.$('elt_id').innerHTML)",
            rq('Sample')->method(pm()->html('elt_id'))->confirm("Really?")->raw()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithConfirmationAndSubstitution()
    {
         $this->assertEquals(
            // "if(confirm('Really M. {1}?'.supplant({'1':jaxon.$('name_id').innerHTML}))){JaxonSample.method(jaxon.$('elt_id').innerHTML);}",
            "JaxonSample.method(jaxon.$('elt_id').innerHTML)",
            rq('Sample')->method(pm()->html('elt_id'))->confirm("Really M. {1}?", pm()->html('name_id'))->raw()
        );
        $this->assertEquals(
            // "if(confirm('Really M. {1}?'.supplant({'1':jaxon.$('name_id').innerHTML}))){JaxonSample.method(jaxon.$('elt_id').innerHTML);}",
            "JaxonSample.method(jaxon.$('elt_id').innerHTML)",
            rq('Sample')->method(pm()->html('elt_id'))->confirm("Really M. {1}?", pm()->html('name_id'))->raw()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithConditionWhen()
    {
        $this->assertEquals(
            // "if(jaxon.$('cond_id').checked){JaxonSample.method(jaxon.$('elt_id').innerHTML);}",
            "JaxonSample.method(jaxon.$('elt_id').innerHTML)",
            rq('Sample')->method(pm()->html('elt_id'))->when(pm()->checked('cond_id'))->raw()
        );
        $this->assertEquals(
            // "if(jaxon.$('cond_id').checked){JaxonSample.method(jaxon.$('elt_id').innerHTML);}",
            "JaxonSample.method(jaxon.$('elt_id').innerHTML)",
            rq('Sample')->method(pm()->html('elt_id'))->when(pm()->checked('cond_id'))->raw()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithConditionWhenAndMessage()
    {
        $this->assertEquals(
            // "if(jaxon.$('cond_id').checked){JaxonSample.method(jaxon.$('elt_id').innerHTML);}" .
            //     "else{alert('Please check the option');}",
            "JaxonSample.method(jaxon.$('elt_id').innerHTML)",
            rq('Sample')->method(pm()->html('elt_id'))
                ->when(pm()->checked('cond_id'))
                ->elseShow("Please check the option")->raw()
        );
        $this->assertEquals(
            // "if(jaxon.$('cond_id').checked){JaxonSample.method(jaxon.$('elt_id').innerHTML);}" .
            //     "else{alert('Please check the option');}",
            "JaxonSample.method(jaxon.$('elt_id').innerHTML)",
            rq('Sample')->method(pm()->html('elt_id'))
                ->when(pm()->checked('cond_id'))
                ->elseShow("Please check the option")->raw()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithConditionWhenAndMessageAndSubstitution()
    {
        $this->assertEquals(
            // "if(jaxon.$('cond_id').checked){JaxonSample.method(jaxon.$('elt_id').innerHTML);}else" .
            //     "{alert('M. {1}, please check the option'.supplant({'1':jaxon.$('name_id').innerHTML}));}",
            "JaxonSample.method(jaxon.$('elt_id').innerHTML)",
            rq('Sample')->method(pm()->html('elt_id'))
                ->when(pm()->checked('cond_id'))
                ->elseShow("M. {1}, please check the option", pm()->html('name_id'))->raw()
        );
        $this->assertEquals(
            // "if(jaxon.$('cond_id').checked){JaxonSample.method(jaxon.$('elt_id').innerHTML);}else" .
            //     "{alert('M. {1}, please check the option'.supplant({'1':jaxon.$('name_id').innerHTML}));}",
            "JaxonSample.method(jaxon.$('elt_id').innerHTML)",
            rq('Sample')->method(pm()->html('elt_id'))
                ->when(pm()->checked('cond_id'))
                ->elseShow("M. {1}, please check the option", pm()->html('name_id'))->raw()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithConditionUnless()
    {
         $this->assertEquals(
            "if(!(jaxon.$('cond_id').checked)){JaxonSample.method(jaxon.$('elt_id').innerHTML);}",
            "if(!(jaxon.$('cond_id').checked)){JaxonSample.method(jaxon.$('elt_id').innerHTML);}",
            rq('Sample')->method(pm()->html('elt_id'))
                ->unless(pm()->checked('cond_id'))->raw()
        );
        $this->assertEquals(
            "if(!(jaxon.$('cond_id').checked)){JaxonSample.method(jaxon.$('elt_id').innerHTML);}",
            "if(!(jaxon.$('cond_id').checked)){JaxonSample.method(jaxon.$('elt_id').innerHTML);}",
            rq('Sample')->method(pm()->html('elt_id'))
                ->unless(pm()->checked('cond_id'))->raw()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithConditionUnlessAndMessage()
    {
         $this->assertEquals(
            // "if(!(jaxon.$('cond_id').checked)){JaxonSample.method(jaxon.$('elt_id').innerHTML);}" .
            //     "else{alert('Please uncheck the option');}",
            "JaxonSample.method(jaxon.$('elt_id').innerHTML)",
            rq('Sample')->method(pm()->html('elt_id'))
                ->unless(pm()->checked('cond_id'))
                ->elseShow("Please uncheck the option")->raw()
        );
        $this->assertEquals(
            // "if(!(jaxon.$('cond_id').checked)){JaxonSample.method(jaxon.$('elt_id').innerHTML);}" .
            //     "else{alert('Please uncheck the option');}",
            "JaxonSample.method(jaxon.$('elt_id').innerHTML)",
            rq('Sample')->method(pm()->html('elt_id'))
                ->unless(pm()->checked('cond_id'))
                ->elseShow("Please uncheck the option")->raw()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithConditionUnlessAndMessageAndSubstitution()
    {
         $this->assertEquals(
            // "if(!(jaxon.$('cond_id').checked)){JaxonSample.method(jaxon.$('elt_id').innerHTML);}" .
            //     "else{alert('M. {1}, please uncheck the option'.supplant({'1':jaxon.$('name_id').innerHTML}));}",
            "JaxonSample.method(jaxon.$('elt_id').innerHTML)",
            rq('Sample')->method(pm()->html('elt_id'))
                ->unless(pm()->checked('cond_id'))
                ->elseShow("M. {1}, please uncheck the option", pm()->html('name_id'))->raw()
        );
        $this->assertEquals(
            // "if(!(jaxon.$('cond_id').checked)){JaxonSample.method(jaxon.$('elt_id').innerHTML);}" .
            //     "else{alert('M. {1}, please uncheck the option'.supplant({'1':jaxon.$('name_id').innerHTML}));}",
            "JaxonSample.method(jaxon.$('elt_id').innerHTML)",
            rq('Sample')->method(pm()->html('elt_id'))
                ->unless(pm()->checked('cond_id'))
                ->elseShow("M. {1}, please uncheck the option", pm()->html('name_id'))->raw()
        );
    }
}
