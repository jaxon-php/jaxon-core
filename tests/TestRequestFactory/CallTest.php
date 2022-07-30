<?php

namespace Jaxon\Tests\TestRequestFactory;

use Jaxon\Jaxon;
use Jaxon\Exception\SetupException;
use Jaxon\Request\Call\Parameter;
use PHPUnit\Framework\TestCase;

use function Jaxon\jaxon;
use function Jaxon\jq;
use function Jaxon\rq;
use function Jaxon\pm;

class CallTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.prefix.class', '');
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

    public function testRequestParameters()
    {
        $this->assertEquals("val1", pm()->js('val1')->getScript());
        $this->assertEquals("jaxon.$('val2').value", pm()->input('val2')->getScript());
        $this->assertEquals("jaxon.$('val3').innerHTML", pm()->html('val3')->getScript());
        $this->assertEquals("jaxon.$('val4').value", pm()->select('val4')->getScript());
        $this->assertEquals("jaxon.$('val5').checked", pm()->checked('val5')->getScript());
        $this->assertEquals("jaxon.getFormValues('val6')", pm()->form('val6')->getScript());
        // A parameter with unknown type will not be rendered, even if it has a value.
        $xUnknownTypeParam = new Parameter('Unknown', 'This is the value');
        $this->assertEquals('', $xUnknownTypeParam->getScript());
        $this->assertEquals('This is the value', $xUnknownTypeParam->getValue());
    }

    public function testRequestParameterConversion()
    {
        $this->assertEquals("parseInt(jaxon.$('val2').value)", pm()->input('val2')->toInt()->getScript());
        $this->assertEquals("parseInt(jaxon.$('val3').innerHTML)", pm()->html('val3')->toInt()->getScript());
        $this->assertEquals("parseInt(jaxon.$('val4').value)", pm()->select('val4')->toInt()->getScript());
        $this->assertEquals("parseInt(trim(' value '))", rq('.')->trim(' value ')->toInt()->getScript());
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClass()
    {
        $this->assertEquals(
            "Sample.method('string', 2, true)",
            rq('Sample')->method('string', 2, true)
        );
        // Clear parameters
        $this->assertEquals(
            "Sample.method()",
            rq('Sample')->method('string', 2, true)->clearParameters()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestWithArrayParam()
    {
        $this->assertEquals(
            "Sample.method([1,2,3])",
            rq('Sample')->method([1, 2, 3])->jsonSerialize()
        );
        $this->assertEquals(
            "Sample.method(['first','second','third'])",
            rq('Sample')->method(['first', 'second', 'third'])->jsonSerialize()
        );
        $this->assertEquals(
            "Sample.method({'first':'one','second':'two','third':'three'})",
            rq('Sample')->method(['first' => 'one', 'second' => 'two', 'third' => 'three'])->jsonSerialize()
        );
        $this->assertEquals(
            "Sample.method(val1, jaxon.$('val2').value, jaxon.$('val3').innerHTML, " .
                "jaxon.$('val4').value, jaxon.$('val5').checked, jaxon.getFormValues('val6'))",
            rq('Sample')->method(pm()->js('val1'), pm()->input('val2'),
                pm()->html('val3'), pm()->select('val4'),
                pm()->checked('val5'), pm()->form('val6'))->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestWithJQueryParam()
    {
        $this->assertEquals(
            "jxnVar1=$('#div').val;Sample.method(jxnVar1)",
            rq('Sample')->method(jq('#div')->val)->jsonSerialize()
        );
        $this->assertEquals(
            "jxnVar1=$('#div').val;Sample.method(jxnVar1, jxnVar1)",
            rq('Sample')->method(jq('#div')->val, jq('#div')->val)->jsonSerialize()
        );
        $this->assertEquals(
            "jxnVar1=$('#div').val;jxnVar2=$('#div2').val;Sample.method(jxnVar1, jxnVar2, jxnVar1)",
            rq('Sample')->method(jq('#div')->val, jq('#div2')->val, jq('#div')->val)->jsonSerialize()
        );
        $this->assertEquals(
            "jxnVar1=$('#div1').val;jxnVar2=$('#div2').val;Sample.method(jxnVar1, jxnVar2)",
            rq('Sample')->method(jq('#div1')->val, jq('#div2')->val)->jsonSerialize()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestWithJsEvent()
    {
        $this->assertEquals(
            "$('.div').click(function(){jxnVar1=$('#div').val;Sample.method(jxnVar1);})",
            jq('.div')->click(rq('Sample')->method(jq('#div')->val))->jsonSerialize()
        );
        $this->assertEquals(
            "$('.div').click(function(){jxnVar1=parseInt($(this).attr('param'));Sample.method(jxnVar1);})",
            jq('.div')->click(rq('Sample')->method(jq()->attr('param')->toInt()))->jsonSerialize()
        );
    }
}
