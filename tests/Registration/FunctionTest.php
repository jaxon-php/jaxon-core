<?php
namespace Jaxon\Tests\Registration;

use Jaxon\Jaxon;
use Jaxon\Request\Plugin\CallableFunction\CallableFunction;
use Jaxon\Request\Plugin\CallableFunction\CallableFunctionPlugin;
use PHPUnit\Framework\TestCase;

final class FunctionTest extends TestCase
{
    /**
     * @var CallableFunctionPlugin
     */
    protected static $xPlugin;

    public static function setUpBeforeClass(): void
    {
        include __DIR__ . '/defs/functions.php';
        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'my_first_function');
        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'my_second_function', ['alias' => 'my_alias_function']);
        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'my_third_function');
        self::$xPlugin = jaxon()->di()->getCallableFunctionPlugin();
    }

    public function testPHPFunction()
    {
        // No callable for standard PHP functions.
        $this->assertEquals(null, self::$xPlugin->getCallable('file_get_contents'));
    }

    public function testNonRegisteredFunction()
    {
        // No callable for aliased functions.
        $this->assertEquals(null, self::$xPlugin->getCallable('my_second_function'));
    }

    public function testRegisteredFunctionSupportClass()
    {
        $xFirstCallable = self::$xPlugin->getCallable('my_first_function');
        $xAliasCallable = self::$xPlugin->getCallable('my_alias_function');
        $xThirdCallable = self::$xPlugin->getCallable('my_third_function');
        // Test callables classes
        $this->assertEquals(CallableFunction::class, get_class($xFirstCallable));
        $this->assertEquals(CallableFunction::class, get_class($xAliasCallable));
        $this->assertEquals(CallableFunction::class, get_class($xThirdCallable));
    }

    public function testRegisteredFunctionExportedName()
    {
        $xFirstCallable = self::$xPlugin->getCallable('my_first_function');
        $xAliasCallable = self::$xPlugin->getCallable('my_alias_function');
        $xThirdCallable = self::$xPlugin->getCallable('my_third_function');
        // Test callables classes
        $this->assertEquals('my_first_function', $xFirstCallable->getName());
        $this->assertEquals('my_alias_function', $xAliasCallable->getName());
        $this->assertEquals('my_third_function', $xThirdCallable->getName());
    }
}
