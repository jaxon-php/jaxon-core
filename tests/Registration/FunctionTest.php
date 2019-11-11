<?php
namespace Jaxon\Tests\Registration;

use Jaxon\Jaxon;
use PHPUnit\Framework\TestCase;
use Exception;

/**
 * @covers Jaxon\Request
 */
final class FunctionTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        include __DIR__ . '/defs/callables/functions.php';
        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'my_first_function');
        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'my_second_function', ['alias' => 'my_alias_function']);
        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'my_third_function');
    }

    /**
     * @expectedException Exception
     */
    public function testPHPFunction()
    {
        $xCallable = jaxon()->di()->get('file_get_contents');
    }

    /**
     * @expectedException Exception
     */
    public function testNonRegisteredFunction()
    {
        $xCallable = jaxon()->di()->get('my_second_function');
    }

    public function testRegisteredFunctionSupportClass()
    {
        $xFirstCallable = jaxon()->di()->get('my_first_function');
        $xAliasCallable = jaxon()->di()->get('my_alias_function');
        $xThirdCallable = jaxon()->di()->get('my_third_function');
        // Test callables classes
        $this->assertEquals(
            get_class($xFirstCallable),
            \Jaxon\Request\Support\CallableFunction::class
        );
        $this->assertEquals(
            get_class($xAliasCallable),
            \Jaxon\Request\Support\CallableFunction::class
        );
        $this->assertEquals(
            get_class($xThirdCallable),
            \Jaxon\Request\Support\CallableFunction::class
        );
    }

    public function testRegisteredFunctionExportedName()
    {
        $xFirstCallable = jaxon()->di()->get('my_first_function');
        $xAliasCallable = jaxon()->di()->get('my_alias_function');
        $xThirdCallable = jaxon()->di()->get('my_third_function');
        // Test callables classes
        $this->assertEquals(
            $xFirstCallable->getName(),
            'my_first_function'
        );
        $this->assertEquals(
            $xAliasCallable->getName(),
            'my_alias_function'
        );
        $this->assertEquals(
            $xThirdCallable->getName(),
            'my_third_function'
        );
    }
}
