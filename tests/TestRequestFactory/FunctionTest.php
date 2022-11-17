<?php

namespace Jaxon\Tests\TestRequestFactory;

use Jaxon\Jaxon;
use Jaxon\Exception\SetupException;
use PHPUnit\Framework\TestCase;

use function Jaxon\jaxon;
use function Jaxon\rq;
use function Jaxon\pm;

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
            __DIR__ . '/../src/first.php');
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
            "jxn_testFunction()",
            rq()->testFunction()->getScript()
        );
        $this->assertEquals(
            "jxn_testFunction()",
            jaxon()->request()->testFunction()->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToGlobalFunctionWithParameter()
    {
        $this->assertEquals(
            "jxn_testFunction('string', 2, true)",
            rq()->testFunction('string', 2, true)->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToGlobalFunctionWithJaxonParameter()
    {
        $this->assertEquals(
            "jxn_testFunction('string', 2, true, jaxon.getFormValues('elt_id'), jaxon.$('elt_id').value)",
            rq()->testFunction('string', 2, true, pm()->form('elt_id'), pm()->input('elt_id'))->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonFunction()
    {
        $this->assertEquals(
            "jxn_testFunction()",
            rq()->testFunction()->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonFunctionWithParameter()
    {
        $this->assertEquals(
            "jxn_testFunction('string', 2, true)",
            rq()->testFunction('string', 2, true)->getScript()
        );
    }

    /**
     * @throws SetupException
     */
    public function testRequestToJaxonFunctionWithJaxonParameter()
    {
        $this->assertEquals(
            "jxn_testFunction('string', 2, true, jaxon.getFormValues('elt_id'), jaxon.$('elt_id').value)",
            rq()->testFunction('string', 2, true, pm()->form('elt_id'), pm()->input('elt_id'))->getScript()
        );
    }
}
