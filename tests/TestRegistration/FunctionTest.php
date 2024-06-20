<?php

namespace Jaxon\Tests\TestRegistration;

require_once __DIR__ . '/../src/functions.php';

use Jaxon\Exception\SetupException;
use Jaxon\Jaxon;
use Jaxon\Plugin\Request\CallableFunction\CallableFunction;
use Jaxon\Plugin\Request\CallableFunction\CallableFunctionPlugin;
use Jaxon\Utils\Http\UriException;
use PHPUnit\Framework\TestCase;
use Sample;
use function Jaxon\jaxon;
use function strlen;
use function trim;

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
        jaxon()->setOption('core.prefix.function', 'jxn_');
        // Register a function
        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'my_first_function',
            __DIR__ . '/../src/first.php');
        // Register a function with an alias
        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'my_second_function', [
            'alias' => 'my_alias_function',
            'upload' => "'html_field_id'",
        ]);
        // Register a class method as a function
        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'myMethod', [
            'alias' => 'my_third_function',
            'class' => Sample::class,
            'include' => __DIR__ . '/../src/classes.php',
        ]);
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

    /**
     * @throws UriException
     */
    public function testLibraryJsCode()
    {
        // This URI will be parsed by the URI detector
        $_SERVER['REQUEST_URI'] = 'http://example.test/path';

        $sJsCode = jaxon()->getScript(true, true);
        $this->assertEquals(file_get_contents(__DIR__ . '/../src/js/lib.js'), $sJsCode);
        $this->assertEquals(1656, strlen(trim($sJsCode)));
        $this->assertEquals(32, strlen(jaxon()->di()->getCodeGenerator()->getHash()));

        unset($_SERVER['REQUEST_URI']);
    }

    /**
     * @throws UriException
     * @throws SetupException
     */
    public function testLibraryJsCodeWithPlugins()
    {
        require_once __DIR__ . '/../src/plugins.php';
        require_once __DIR__ . '/../src/packages.php';

        jaxon()->registerPlugin('SamplePlugin', 'plugin');
        jaxon()->registerPackage('SamplePackage');

        // This URI will be parsed by the URI detector
        $_SERVER['REQUEST_URI'] = 'http://example.test/path';
        $sJsCode = jaxon()->getScript(true, true);
        $this->assertEquals(1838, strlen(trim($sJsCode)));
        $this->assertEquals(32, strlen(jaxon()->di()->getCodeGenerator()->getHash()));

        $sJsCode = trim(jaxon()->getCss() . "\n" . jaxon()->getJs()) . jaxon()->getScript();
        $this->assertEquals(1838, strlen(trim($sJsCode)));

        unset($_SERVER['REQUEST_URI']);
    }

    public function testCallableFunctionIncorrectName()
    {
        // Register a function with incorrect name
        $this->expectException(SetupException::class);
        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'my function');
    }

    public function testCallableFunctionIncorrectOption()
    {
        // Register a function with incorrect option
        $this->expectException(SetupException::class);
        jaxon()->register(Jaxon::CALLABLE_FUNCTION, 'my_function', true);
    }
}
