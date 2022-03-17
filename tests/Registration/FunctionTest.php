<?php
namespace Jaxon\Tests\Registration;

use Jaxon\Jaxon;
use PHPUnit\Framework\TestCase;
use Exception;

/**
 * @covers Jaxon\RequestPlugin
 */
final class FunctionTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        include __DIR__ . '/defs/functions.php';
        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'my_first_function');
        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'my_second_function', ['alias' => 'my_alias_function']);
        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'my_third_function');
    }

    public function testPHPFunction()
    {
        $this->expectException(Exception::class);
        $xCallable = jaxon()->di()->get('file_get_contents');
    }

    public function testNonRegisteredFunction()
    {
        $this->expectException(Exception::class);
        $xCallable = jaxon()->di()->get('my_alias_function');
    }

    public function testRegisteredFunctionSupportClass()
    {
        $xFirstCallable = jaxon()->di()->getCallableFunction('my_first_function');
        $xAliasCallable = jaxon()->di()->getCallableFunction('my_second_function');
        $xThirdCallable = jaxon()->di()->getCallableFunction('my_third_function');
        // Test callables classes
        $this->assertEquals(get_class($xFirstCallable), \Jaxon\Request\Plugin\CallableFunction\CallableFunction::class);
        $this->assertEquals(get_class($xAliasCallable), \Jaxon\Request\Plugin\CallableFunction\CallableFunction::class);
        $this->assertEquals(get_class($xThirdCallable), \Jaxon\Request\Plugin\CallableFunction\CallableFunction::class);
    }

    public function testRegisteredFunctionExportedName()
    {
        $xFirstCallable = jaxon()->di()->getCallableFunction('my_first_function');
        $xAliasCallable = jaxon()->di()->getCallableFunction('my_second_function');
        $xThirdCallable = jaxon()->di()->getCallableFunction('my_third_function');
        // Test callables classes
        $this->assertEquals($xFirstCallable->getName(), 'my_first_function');
        $this->assertEquals($xAliasCallable->getName(), 'my_second_function');
        $this->assertEquals($xThirdCallable->getName(), 'my_third_function');
    }
}
