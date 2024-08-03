<?php

namespace Jaxon\Tests\TestRegistrationApp;

require_once __DIR__ . '/../src/functions.php';

use Jaxon\Exception\SetupException;
use Jaxon\Jaxon;
use Jaxon\Plugin\Request\CallableFunction\CallableFunction;
use Jaxon\Plugin\Request\CallableFunction\CallableFunctionPlugin;
use PHPUnit\Framework\TestCase;
use function Jaxon\jaxon;
use function strlen;

final class FunctionTest extends TestCase
{
    /**
     * @var CallableFunctionPlugin
     */
    protected $xPlugin;

    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->app()->setup(__DIR__ . '/../config/app/functions.php');

        $this->xPlugin = jaxon()->di()->getCallableFunctionPlugin();
    }

    /**
     * @throws SetupException
     */
    public function tearDown(): void
    {
        jaxon()->reset();
        parent::tearDown();
    }

    public function testPluginName()
    {
        $this->assertEquals(Jaxon::CALLABLE_FUNCTION, $this->xPlugin->getName());
    }

    public function testPHPFunction()
    {
        // No callable for standard PHP functions.
        $this->assertEquals(null, $this->xPlugin->getCallable('file_get_contents'));
    }

    public function testNonCallableFunction()
    {
        // No callable for aliased functions.
        $this->assertEquals(null, $this->xPlugin->getCallable('my_second_function'));
    }

    public function testCallableFunctionClass()
    {
        $xFirstCallable = $this->xPlugin->getCallable('my_first_function');
        $xAliasCallable = $this->xPlugin->getCallable('my_alias_function');
        $xThirdCallable = $this->xPlugin->getCallable('my_third_function');
        // Test callables classes
        $this->assertEquals(CallableFunction::class, get_class($xFirstCallable));
        $this->assertEquals(CallableFunction::class, get_class($xAliasCallable));
        $this->assertEquals(CallableFunction::class, get_class($xThirdCallable));
    }

    public function testCallableFunctionName()
    {
        $xFirstCallable = $this->xPlugin->getCallable('my_first_function');
        $xAliasCallable = $this->xPlugin->getCallable('my_alias_function');
        $xThirdCallable = $this->xPlugin->getCallable('my_third_function');
        // Test callables classes
        $this->assertEquals('my_first_function', $xFirstCallable->getName());
        $this->assertEquals('my_alias_function', $xAliasCallable->getName());
        $this->assertEquals('my_third_function', $xThirdCallable->getName());
    }

    public function testCallableFunctionJsName()
    {
        $xFirstCallable = $this->xPlugin->getCallable('my_first_function');
        $xAliasCallable = $this->xPlugin->getCallable('my_alias_function');
        $xThirdCallable = $this->xPlugin->getCallable('my_third_function');
        // Test callables classes
        $this->assertEquals('jxn_my_first_function', $xFirstCallable->getJsName());
        $this->assertEquals('jxn_my_alias_function', $xAliasCallable->getJsName());
        $this->assertEquals('jxn_my_third_function', $xThirdCallable->getJsName());
    }

    public function testCallableFunctionOptions()
    {
        $xFirstCallable = $this->xPlugin->getCallable('my_first_function');
        $xAliasCallable = $this->xPlugin->getCallable('my_alias_function');
        $xThirdCallable = $this->xPlugin->getCallable('my_third_function');
        // Test callables classes
        $this->assertCount(0, $xFirstCallable->getOptions());
        $this->assertCount(1, $xAliasCallable->getOptions());
        $this->assertCount(0, $xThirdCallable->getOptions());
    }

    public function testCallableFunctionJsCode()
    {
        $this->assertEquals(32, strlen($this->xPlugin->getHash()));
        // $this->assertEquals('34608e208fda374f8761041969acf96e', $this->xPlugin->getHash());
        $this->assertEquals(403, strlen($this->xPlugin->getScript()));
        // $this->assertEquals(file_get_contents(__DIR__ . '/../src/js/function.js'), $this->xPlugin->getScript());
    }
}
