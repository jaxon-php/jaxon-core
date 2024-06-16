<?php

namespace Jaxon\Tests\TestRequestFactory;

use Jaxon\Jaxon;
use Jaxon\Exception\SetupException;
use Jaxon\Script\Call\Parameter;
use PHPUnit\Framework\TestCase;

use function Jaxon\jaxon;
use function Jaxon\jq;
use function Jaxon\js;
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
        $this->assertEquals("val1", pm()->js('val1')->__toString());
        $this->assertEquals("jaxon.$('val2').value", pm()->input('val2')->__toString());
        $this->assertEquals("jaxon.$('val3').innerHTML", pm()->html('val3')->__toString());
        $this->assertEquals("jaxon.$('val4').value", pm()->select('val4')->__toString());
        $this->assertEquals("jaxon.$('val5').checked", pm()->checked('val5')->__toString());
        $this->assertEquals("jaxon.getFormValues('val6')", pm()->form('val6')->__toString());
        // A parameter with unknown type will not be rendered, even if it has a value.
        $xUnknownTypeParam = new Parameter('Unknown', 'This is the value');
        $this->assertEquals('', $xUnknownTypeParam->__toString());
        $this->assertEquals('This is the value', $xUnknownTypeParam->getValue());
    }

    public function testRequestParameterConversion()
    {
        $this->assertEquals("parseInt(jaxon.$('val2').value)", pm()->input('val2')->toInt()->__toString());
        $this->assertEquals("parseInt(jaxon.$('val3').innerHTML)", pm()->html('val3')->toInt()->__toString());
        $this->assertEquals("parseInt(jaxon.$('val4').value)", pm()->select('val4')->toInt()->__toString());
        $this->assertEquals("parseInt(window.trim(' value '))", js('window')->trim(' value ')->toInt()->raw());
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonClass()
    {
        $this->assertEquals(
            "Sample.method('string', 2, true)",
            rq('Sample')->method('string', 2, true)->raw()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestWithArrayParam()
    {
        $this->assertEquals(
            "Sample.method([1,2,3])",
            rq('Sample')->method([1, 2, 3])->raw()
        );
        $this->assertEquals(
            "Sample.method(['first','second','third'])",
            rq('Sample')->method(['first', 'second', 'third'])->raw()
        );
        $this->assertEquals(
            "Sample.method({'first':'one','second':'two','third':'three'})",
            rq('Sample')->method(['first' => 'one', 'second' => 'two', 'third' => 'three'])->raw()
        );
        $this->assertEquals(
            "Sample.method(val1, jaxon.$('val2').value, jaxon.$('val3').innerHTML, " .
                "jaxon.$('val4').value, jaxon.$('val5').checked, jaxon.getFormValues('val6'))",
            rq('Sample')->method(pm()->js('val1'), pm()->input('val2'),
                pm()->html('val3'), pm()->select('val4'),
                pm()->checked('val5'), pm()->form('val6'))->raw()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestWithJQueryParam()
    {
        // $this->assertEquals(
        //     "Sample.method(jaxon.jq('#div').val)",
        //     rq('Sample')->method(jq('#div')->val)->raw()
        // );
        $this->assertEquals(
            'Sample.method(jaxon.exec({"_type":"expr","calls":[{"_type":"select","_name":"#div","mode":"jq"}' .
                ',{"_type":"attr","_name":"val"}]}))',
            rq('Sample')->method(jq('#div')->val)->raw()
        );
        // $this->assertEquals(
        //     "Sample.method(jaxon.jq('#div').val, jaxon.jq('#div').val)",
        //     rq('Sample')->method(jq('#div')->val, jq('#div')->val)->raw()
        // );
        // $this->assertEquals(
        //     "Sample.method(jaxon.jq('#div1').val, jaxon.jq('#div2').val, jaxon.jq('#div1').val)",
        //     rq('Sample')->method(jq('#div1')->val, jq('#div2')->val, jq('#div1')->val)->raw()
        // );
        // $this->assertEquals(
        //     "Sample.method(jaxon.jq('#div1').val, jaxon.jq('#div2').val)",
        //     rq('Sample')->method(jq('#div1')->val, jq('#div2')->val)->raw()
        // );
    }

    /**
     * @throws SetupException
     */
    public function testRequestWithJsEvent()
    {
        // $this->assertEquals(
        //     "jaxon.jq('.div').click((e) => {Sample.method(jaxon.jq('#div').val);})",
        //     jq('.div')->click(rq('Sample')->method(jq('#div')->val))->raw()
        // );
        $this->assertEquals(
            'jaxon.jq(\'.div\').on(\'click\', () => ' .
                '{ jaxon.exec({"_type":"expr","calls":[{"_type":"func","_name":"Sample.method",' .
                '"args":[{"_type":"expr","calls":[{"_type":"select","_name":"this","mode":"jq"},' .
                '{"_type":"method","_name":"attr","args":["param"]},' .
                '{"_type":"method","_name":"toInt","args":[]}]}]}]}); })',
            jq('.div')->click(rq('Sample')->method(jq()->attr('param')->toInt()))->raw()
        );
        // $this->assertEquals(
        //     "jaxon.jq('.div').click((e) => {Sample.method(parseInt(jaxon.jq(e.currentTarget).attr('param')));})",
        //     jq('.div')->click(rq('Sample')->method(jq()->attr('param')->toInt()))->raw()
        // );
    }
}
