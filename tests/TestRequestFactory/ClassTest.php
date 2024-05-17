<?php

namespace Jaxon\Tests\TestRequestFactory;

use Jaxon\Jaxon;
use PHPUnit\Framework\TestCase;
use Jaxon\Exception\SetupException;

use function Jaxon\jaxon;
use function Jaxon\js;
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
            js('Sample')->method()->getScript()
        );
        $this->assertEquals(
            "JaxonSample.method()",
            js('Sample')->method()->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithParameter()
    {
        $this->assertEquals(
            "JaxonSample.method('string', 2, true)",
            js('Sample')->method('string', 2, true)->getScript()
        );
        $this->assertEquals(
            "JaxonSample.method('string', 2, true)",
            js('Sample')->method('string', 2, true)->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithFormParameter()
    {
        $this->assertEquals(
            "JaxonSample.method(jaxon.getFormValues('elt_id'))",
            js('Sample')->method(pm()->form('elt_id'))->getScript()
        );
        $this->assertEquals(
            "JaxonSample.method(jaxon.getFormValues('elt_id'))",
            js('Sample')->method(pm()->form('elt_id'))->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithInputParameter()
    {
        $this->assertEquals(
            "JaxonSample.method(jaxon.$('elt_id').value)",
            js('Sample')->method(pm()->input('elt_id'))->getScript()
        );
        $this->assertEquals(
            "JaxonSample.method(jaxon.$('elt_id').value)",
            js('Sample')->method(pm()->input('elt_id'))->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithCheckedParameter()
    {
        $this->assertEquals(
            "JaxonSample.method(jaxon.$('check_id').checked)",
            js('Sample')->method(pm()->checked('check_id'))->getScript()
        );
        $this->assertEquals(
            "JaxonSample.method(jaxon.$('check_id').checked)",
            js('Sample')->method(pm()->checked('check_id'))->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithSelectParameter()
    {
        $this->assertEquals(
            "JaxonSample.method(jaxon.$('select_id').value)",
            js('Sample')->method(pm()->select('select_id'))->getScript()
        );
        $this->assertEquals(
            "JaxonSample.method(jaxon.$('select_id').value)",
            js('Sample')->method(pm()->select('select_id'))->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithInnerHTMLParameter()
    {
        $this->assertEquals(
            "JaxonSample.method(jaxon.$('elt_id').innerHTML)",
            js('Sample')->method(pm()->html('elt_id'))->getScript()
        );
        $this->assertEquals(
            "JaxonSample.method(jaxon.$('elt_id').innerHTML)",
            js('Sample')->method(pm()->html('elt_id'))->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithMultipleParameter()
    {
        $this->assertEquals(
            "JaxonSample.method(jaxon.$('check_id').checked, jaxon.$('select_id').value, jaxon.$('elt_id').innerHTML)",
            js('Sample')->method(pm()->checked('check_id'), pm()->select('select_id'), pm()->html('elt_id'))->getScript()
        );
        $this->assertEquals(
            "JaxonSample.method(jaxon.$('check_id').checked, jaxon.$('select_id').value, jaxon.$('elt_id').innerHTML)",
            js('Sample')->method(pm()->checked('check_id'), pm()->select('select_id'), pm()->html('elt_id'))->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithConfirmation()
    {
        $this->assertEquals(
            "if(confirm('Really?')){JaxonSample.method(jaxon.$('elt_id').innerHTML);}",
            js('Sample')->method(pm()->html('elt_id'))->confirm("Really?")->getScript()
        );
        $this->assertEquals(
            "if(confirm('Really?')){JaxonSample.method(jaxon.$('elt_id').innerHTML);}",
            js('Sample')->method(pm()->html('elt_id'))->confirm("Really?")->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithConfirmationAndSubstitution()
    {
         $this->assertEquals(
            "if(confirm('Really M. {1}?'.supplant({'1':jaxon.$('name_id').innerHTML}))){JaxonSample.method(jaxon.$('elt_id').innerHTML);}",
            js('Sample')->method(pm()->html('elt_id'))->confirm("Really M. {1}?", pm()->html('name_id'))->getScript()
        );
        $this->assertEquals(
            "if(confirm('Really M. {1}?'.supplant({'1':jaxon.$('name_id').innerHTML}))){JaxonSample.method(jaxon.$('elt_id').innerHTML);}",
            js('Sample')->method(pm()->html('elt_id'))->confirm("Really M. {1}?", pm()->html('name_id'))->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClassWithConditionWhen()
    {
        $this->assertEquals(
            "if(jaxon.$('cond_id').checked){JaxonSample.method(jaxon.$('elt_id').innerHTML);}",
            js('Sample')->method(pm()->html('elt_id'))->when(pm()->checked('cond_id'))->getScript()
        );
        $this->assertEquals(
            "if(jaxon.$('cond_id').checked){JaxonSample.method(jaxon.$('elt_id').innerHTML);}",
            js('Sample')->method(pm()->html('elt_id'))->when(pm()->checked('cond_id'))->getScript()
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
            js('Sample')->method(pm()->html('elt_id'))
                ->when(pm()->checked('cond_id'))
                ->elseShow("Please check the option")->getScript()
        );
        $this->assertEquals(
            "if(jaxon.$('cond_id').checked){JaxonSample.method(jaxon.$('elt_id').innerHTML);}" .
                "else{alert('Please check the option');}",
            js('Sample')->method(pm()->html('elt_id'))
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
            js('Sample')->method(pm()->html('elt_id'))
                ->when(pm()->checked('cond_id'))
                ->elseShow("M. {1}, please check the option", pm()->html('name_id'))->getScript()
        );
        $this->assertEquals(
            "if(jaxon.$('cond_id').checked){JaxonSample.method(jaxon.$('elt_id').innerHTML);}else" .
                "{alert('M. {1}, please check the option'.supplant({'1':jaxon.$('name_id').innerHTML}));}",
            js('Sample')->method(pm()->html('elt_id'))
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
            js('Sample')->method(pm()->html('elt_id'))
                ->unless(pm()->checked('cond_id'))->getScript()
        );
        $this->assertEquals(
            "if(!(jaxon.$('cond_id').checked)){JaxonSample.method(jaxon.$('elt_id').innerHTML);}",
            js('Sample')->method(pm()->html('elt_id'))
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
            js('Sample')->method(pm()->html('elt_id'))
                ->unless(pm()->checked('cond_id'))
                ->elseShow("Please uncheck the option")->getScript()
        );
        $this->assertEquals(
            "if(!(jaxon.$('cond_id').checked)){JaxonSample.method(jaxon.$('elt_id').innerHTML);}" .
                "else{alert('Please uncheck the option');}",
            js('Sample')->method(pm()->html('elt_id'))
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
            js('Sample')->method(pm()->html('elt_id'))
                ->unless(pm()->checked('cond_id'))
                ->elseShow("M. {1}, please uncheck the option", pm()->html('name_id'))->getScript()
        );
        $this->assertEquals(
            "if(!(jaxon.$('cond_id').checked)){JaxonSample.method(jaxon.$('elt_id').innerHTML);}" .
                "else{alert('M. {1}, please uncheck the option'.supplant({'1':jaxon.$('name_id').innerHTML}));}",
            js('Sample')->method(pm()->html('elt_id'))
                ->unless(pm()->checked('cond_id'))
                ->elseShow("M. {1}, please uncheck the option", pm()->html('name_id'))->getScript()
        );
    }
}
